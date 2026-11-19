<?php
@session_start();require_once('../../conexao.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método inválido.');}
if(!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)){http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if($empresa<1||empty($_SESSION['csrf_conc'])||!hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))){http_response_code(403);exit('Sessão inválida.');}
$receber=filter_var($_POST['receber_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$credito=filter_var($_POST['liquidacao_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$receber||!$credito){http_response_code(400);exit('Informe os IDs do recebimento e do crédito.');}
try{
 $pdo->beginTransaction();
 $st=$pdo->prepare("SELECT id,forma_pgto,valor,pago FROM receber WHERE id=? AND empresa=? FOR UPDATE");$st->execute([$receber,$empresa]);$r=$st->fetch(PDO::FETCH_ASSOC);
 $st=$pdo->prepare("SELECT id,forma_pgto,valor_creditado,cancelado_em FROM conciliacao_liquidacoes WHERE id=? AND empresa=? FOR UPDATE");$st->execute([$credito,$empresa]);$l=$st->fetch(PDO::FETCH_ASSOC);
 if(!$r||!$l||$r['pago']!=='Sim'||$l['cancelado_em']!==null||(int)$r['forma_pgto']!==(int)$l['forma_pgto'])throw new DomainException('Recebimento e crédito devem pertencer à empresa, ter a mesma forma de pagamento e estar ativos.');
 $st=$pdo->prepare("SELECT id FROM conciliacao_vinculos_recebimentos WHERE empresa=? AND receber_id=?");$st->execute([$empresa,$receber]);if($st->fetchColumn())throw new DomainException('Recebimento já vinculado.');
 $st=$pdo->prepare("SELECT COALESCE(SUM(valor_vinculado),0) FROM conciliacao_vinculos_recebimentos WHERE empresa=? AND liquidacao_id=?");$st->execute([$empresa,$credito]);$usado=(float)$st->fetchColumn();
 // Valor vinculado é bruto do recebimento; taxas podem causar diferença para o crédito líquido.
 // Não forçar igualdade de valores, mas nunca declarar o crédito totalmente conciliado por este vínculo.
 $st=$pdo->prepare("INSERT INTO conciliacao_vinculos_recebimentos (empresa,receber_id,liquidacao_id,valor_vinculado,usuario_id) VALUES (?,?,?,?,?)");
 $st->execute([$empresa,$receber,$credito,$r['valor'],$usuario?:null]);$pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&vinculo_salvo=1',true,303);exit;
}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(422);exit($e->getMessage());}
catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Vinculo conciliacao: '.$e->getMessage());http_response_code(500);exit('Falha ao vincular. Confira a migração SQL.');}
