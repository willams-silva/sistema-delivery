<?php
@session_start();
require_once '../../../conexao.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=(int)($_SESSION['empresa']??0);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
$status=$_POST['status']??'';
if($empresa<=0||empty($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
if(($_SESSION['nivel']??'')!=='Administrador'){http_response_code(403);exit('Somente um Administrador pode mudar o status das mesas');}
if(!$id||$id<=0||!in_array($status,['Aberta','Fechada'],true)){http_response_code(400);exit('Dados inválidos');}
try{
 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT status,ativo FROM mesas WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $q->execute([':id'=>$id,':empresa'=>$empresa]);
 $mesa=$q->fetch(PDO::FETCH_ASSOC);
 if(!$mesa){$pdo->rollBack();http_response_code(404);exit('Mesa não encontrada');}
 if($status==='Aberta'&&$mesa['ativo']!=='Sim'){$pdo->rollBack();http_response_code(409);exit('Ative a mesa antes de abri-la');}
 if($status==='Fechada'&&$mesa['status']==='Aberta'){
  // Fechamento operacional exige fluxo de caixa/comanda; não encerrar atendimento por atalho.
  $pdo->rollBack();http_response_code(409);exit('Finalize o atendimento e o pagamento pelo fluxo de pedidos antes de fechar a mesa');
 }
 $q=$pdo->prepare('UPDATE mesas SET status=:status WHERE id=:id AND empresa=:empresa');
 $q->execute([':status'=>$status,':id'=>$id,':empresa'=>$empresa]);
 $pdo->commit();echo 'Editado';
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('mesas/editar_status: '.$e->getMessage());
 http_response_code(500);echo 'Erro ao alterar o status da mesa';
}
