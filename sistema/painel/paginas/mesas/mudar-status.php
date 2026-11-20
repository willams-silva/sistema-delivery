<?php
@session_start();
require_once '../../../conexao.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=(int)($_SESSION['empresa']??0);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
$acao=$_POST['acao']??'';
if($empresa<=0||empty($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
if(!$id||$id<=0||!in_array($acao,['Sim','Não'],true)){http_response_code(400);exit('Dados inválidos');}
try{
 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT status FROM mesas WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $q->execute([':id'=>$id,':empresa'=>$empresa]);
 $status=$q->fetchColumn();
 if($status===false){$pdo->rollBack();http_response_code(404);exit('Mesa não encontrada');}
 if($acao==='Não'&&$status==='Aberta'){$pdo->rollBack();http_response_code(409);exit('Não é possível desativar uma mesa aberta');}
 $q=$pdo->prepare('UPDATE mesas SET ativo=:acao WHERE id=:id AND empresa=:empresa');
 $q->execute([':acao'=>$acao,':id'=>$id,':empresa'=>$empresa]);
 $pdo->commit();echo 'Alterado com Sucesso';
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('mesas/mudar-status: '.$e->getMessage());
 http_response_code(500);echo 'Erro ao atualizar a mesa';
}
