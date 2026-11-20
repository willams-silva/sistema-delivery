<?php
@session_start();
require_once("../../../conexao.php");
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$origem=filter_var($_POST['id_mesa_troca']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$destino=filter_var($_POST['mesa_troca']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$abertura=filter_var($_POST['id_abertura_troca']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$empresa||empty($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
if(!$origem||!$destino||!$abertura||$origem===$destino){http_response_code(400);exit('Selecione uma mesa de destino diferente da origem');}
try{
 $pdo->beginTransaction();
 // Ordem determinística de locks evita deadlock em transferências concorrentes.
 $ids=[$origem,$destino];sort($ids,SORT_NUMERIC);
 $q=$pdo->prepare('SELECT id,ativo,status FROM mesas WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $mesas=[];
 foreach($ids as $id){
  $q->execute([':id'=>$id,':empresa'=>$empresa]);
  $linha=$q->fetch(PDO::FETCH_ASSOC);
  if(!$linha){$pdo->rollBack();http_response_code(404);exit('Mesa de origem ou destino não encontrada');}
  $mesas[(int)$linha['id']]=$linha;
 }
 if($mesas[$origem]['status']!=='Aberta'||$mesas[$origem]['ativo']!=='Sim'){
  $pdo->rollBack();http_response_code(409);exit('A mesa de origem não está aberta e ativa');
 }
 if($mesas[$destino]['status']!=='Fechada'||$mesas[$destino]['ativo']!=='Sim'){
  $pdo->rollBack();http_response_code(409);exit('A mesa de destino deve estar livre e ativa');
 }
 $q=$pdo->prepare("SELECT id,mesa FROM abertura_mesa WHERE id=:abertura AND empresa=:empresa AND status='Aberta' FOR UPDATE");
 $q->execute([':abertura'=>$abertura,':empresa'=>$empresa]);
 $at=$q->fetch(PDO::FETCH_ASSOC);
 if(!$at||(int)$at['mesa']!==$origem){
  $pdo->rollBack();http_response_code(409);exit('Atendimento de origem não corresponde à mesa informada');
 }
 $q=$pdo->prepare("SELECT id FROM abertura_mesa WHERE mesa=:mesa AND empresa=:empresa AND status='Aberta' LIMIT 1 FOR UPDATE");
 $q->execute([':mesa'=>$destino,':empresa'=>$empresa]);
 if($q->fetchColumn()){
  $pdo->rollBack();http_response_code(409);exit('A mesa de destino já possui atendimento aberto');
 }
 $q=$pdo->prepare("UPDATE abertura_mesa SET mesa=:destino WHERE id=:abertura AND empresa=:empresa AND mesa=:origem AND status='Aberta'");
 $q->execute([':destino'=>$destino,':abertura'=>$abertura,':empresa'=>$empresa,':origem'=>$origem]);
 if($q->rowCount()!==1){throw new RuntimeException('Atendimento alterado durante a transferência');}
 $q=$pdo->prepare("UPDATE mesas SET status='Fechada' WHERE id=:id AND empresa=:empresa");
 $q->execute([':id'=>$origem,':empresa'=>$empresa]);
 $q=$pdo->prepare("UPDATE mesas SET status='Aberta' WHERE id=:id AND empresa=:empresa");
 $q->execute([':id'=>$destino,':empresa'=>$empresa]);
 $pdo->commit();
 echo 'Salvo com Sucesso';
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('novo_pedido/troca_mesa: '.$e->getMessage());
 http_response_code(500);echo 'Não foi possível transferir a mesa. Tente novamente';
}
