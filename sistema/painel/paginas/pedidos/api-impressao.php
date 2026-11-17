<?php
// API para agente Windows: HTTPS + token por empresa; sem sessao do navegador.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') { http_response_code(403); echo json_encode(['erro'=>'HTTPS obrigatorio']); exit; }
require_once __DIR__ . '/../../../conexao.php';
$empresa=filter_var($_POST['empresa']??null,FILTER_VALIDATE_INT);
$token=$_POST['token']??'';
if (!$empresa || !is_string($token) || strlen($token)<32 || strlen($token)>256) { http_response_code(401); exit(json_encode(['erro'=>'Credenciais invalidas'])); }
$st=$pdo->prepare("SELECT token_hash FROM automacao_impressoras WHERE empresa=?");
$st->execute([$empresa]);
$hash=$st->fetchColumn();
if (!$hash || !hash_equals($hash,hash('sha256',$token))) { http_response_code(401); exit(json_encode(['erro'=>'Nao autorizado'])); }
$acao=$_POST['acao']??'proximo';
try {
 if ($acao==='confirmar' || $acao==='falhou') {
  $id=filter_var($_POST['venda_id']??null,FILTER_VALIDATE_INT);
  $reserva=$_POST['reserva']??'';
  if (!$id || !preg_match('/^[a-f0-9]{64}$/D',$reserva)) { http_response_code(422); exit(json_encode(['erro'=>'Reserva invalida'])); }
  $estado=$acao==='confirmar'?'impresso':'pendente';
  $q=$pdo->prepare("UPDATE automacao_impressao_jobs SET estado=?, impresso_em=IF(?='impresso',NOW(),NULL),reserva_token=NULL,reservado_ate=NULL WHERE empresa=? AND venda_id=? AND estado='reservado' AND reserva_token=?");
  $q->execute([$estado,$estado,$empresa,$id,$reserva]);
  echo json_encode(['ok'=>$q->rowCount()===1]); exit;
 }
 if ($acao!=='proximo') { http_response_code(422); exit(json_encode(['erro'=>'Acao invalida'])); }
 // Adiciona apenas pedidos com aceite automatico e impressao habilitada.
 $q=$pdo->prepare("INSERT IGNORE INTO automacao_impressao_jobs (empresa,venda_id) SELECT e.empresa,e.venda_id FROM automacao_pedidos_estado e JOIN automacao_pedidos_config c ON c.empresa=e.empresa WHERE e.empresa=? AND e.imprimir_pendente=1 AND c.imprimir_ao_aceitar=1");
 $q->execute([$empresa]);
 $pdo->beginTransaction();
 $q=$pdo->prepare("SELECT j.venda_id FROM automacao_impressao_jobs j JOIN vendas v ON v.id=j.venda_id AND v.empresa=j.empresa WHERE j.empresa=? AND (j.estado='pendente' OR (j.estado='reservado' AND j.reservado_ate<NOW())) AND v.status NOT IN ('Cancelado') ORDER BY j.venda_id ASC LIMIT 1 FOR UPDATE");
 $q->execute([$empresa]);
 $id=$q->fetchColumn();
 if (!$id) { $pdo->commit(); echo json_encode(['ok'=>true,'pedido'=>null]); exit; }
 $reserva=bin2hex(random_bytes(32));
 $q=$pdo->prepare("UPDATE automacao_impressao_jobs SET estado='reservado',reserva_token=?,reservado_ate=DATE_ADD(NOW(), INTERVAL 3 MINUTE),tentativas=tentativas+1 WHERE empresa=? AND venda_id=?");
 $q->execute([$reserva,$empresa,$id]);
 $q=$pdo->prepare("SELECT id,pedido,nome_cliente,valor,data,hora,entrega,obs FROM vendas WHERE empresa=? AND id=?");
 $q->execute([$empresa,$id]);
 $v=$q->fetch(PDO::FETCH_ASSOC);
 $q=$pdo->prepare("SELECT nome_produto,quantidade,total_item,obs FROM carrinho WHERE pedido=?");
 $q->execute([$id]);
 $itens=$q->fetchAll(PDO::FETCH_ASSOC);
 $pdo->commit();
 echo json_encode(['ok'=>true,'pedido'=>['venda'=>$v,'itens'=>$itens,'reserva'=>$reserva]],JSON_UNESCAPED_UNICODE);
} catch(Throwable $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('API impressao: '.$e->getMessage());
 http_response_code(500); echo json_encode(['erro'=>'Falha temporaria']);
}
