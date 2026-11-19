<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') { http_response_code(405); echo json_encode(['erro'=>'Método inválido']); exit; }
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario) { http_response_code(403); echo json_encode(['erro'=>'Sessão inválida']); exit; }
require_once '../../../conexao.php';
$acao=(string)($_GET['acao']??'');
$permitidas=['','CRIAR','ALTERAR','EXCLUIR','BAIXAR','ESTORNAR'];
if (!in_array($acao,$permitidas,true)) { http_response_code(400); echo json_encode(['erro'=>'Ação inválida']); exit; }
$inicio=(string)($_GET['inicio']??'');
$fim=(string)($_GET['fim']??'');
foreach ([$inicio,$fim] as $data) {
 if ($data!=='' && (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$data) || !($d=DateTimeImmutable::createFromFormat('!Y-m-d',$data)) || $d->format('Y-m-d')!==$data)) {
  http_response_code(400); echo json_encode(['erro'=>'Período inválido']); exit;
 }
}
if ($inicio!=='' && $fim!=='' && $inicio>$fim) { http_response_code(400); echo json_encode(['erro'=>'Período invertido']); exit; }
$sql="SELECT a.id,a.registro_id,a.acao,a.usuario_id,a.ocorrido_em,a.dados_anteriores,a.dados_novos,u.nome AS usuario_nome FROM auditoria_financeira a LEFT JOIN usuarios u ON u.id=a.usuario_id AND u.empresa=a.empresa WHERE a.empresa=:empresa AND a.tabela_origem='receber'";
$args=[':empresa'=>$empresa];
if ($acao!=='') {$sql.=' AND a.acao=:acao';$args[':acao']=$acao;}
if ($inicio!=='') {$sql.=' AND a.ocorrido_em>=:inicio';$args[':inicio']=$inicio.' 00:00:00';}
if ($fim!=='') {$sql.=' AND a.ocorrido_em<=:fim';$args[':fim']=$fim.' 23:59:59';}
$sql.=' ORDER BY a.id DESC LIMIT 100';
try {
 $st=$pdo->prepare($sql);$st->execute($args);$linhas=$st->fetchAll(PDO::FETCH_ASSOC);
 foreach($linhas as &$linha) {
  $antes=json_decode($linha['dados_anteriores']??'null',true);
  $depois=json_decode($linha['dados_novos']??'null',true);
  $linha['descricao']=(string)($depois['descricao']??$antes['descricao']??'');
  $linha['valor_antes']=$antes['valor']??null;
  $linha['valor_depois']=$depois['valor']??null;
  unset($linha['dados_anteriores'],$linha['dados_novos']);
 }
 echo json_encode(['registros'=>$linhas],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
} catch(Throwable $e) {
 error_log('Auditoria receber: '.$e->getMessage());http_response_code(500);echo json_encode(['erro'=>'Não foi possível consultar o histórico']);
}
