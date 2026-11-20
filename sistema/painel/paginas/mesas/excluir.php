<?php
@session_start();
require_once '../../../conexao.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=(int)($_SESSION['empresa']??0);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if($empresa<=0||empty($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
if(!$id||$id<=0){http_response_code(400);exit('Mesa inválida');}
try{
 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT status FROM mesas WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $q->execute([':id'=>$id,':empresa'=>$empresa]);
 $status=$q->fetchColumn();
 if($status===false){$pdo->rollBack();http_response_code(404);exit('Mesa não encontrada');}
 if($status!=='Fechada'){$pdo->rollBack();http_response_code(409);exit('Feche a mesa antes de excluir');}
 // Não excluir mesas que já possuem atendimentos: preserva histórico e vínculos.
 $q=$pdo->prepare('SELECT 1 FROM abertura_mesa WHERE mesa=:id LIMIT 1');
 $q->execute([':id'=>$id]);
 if($q->fetchColumn()){$pdo->rollBack();http_response_code(409);exit('Mesa possui histórico de atendimento. Desative-a em vez de excluir');}
 $q=$pdo->prepare('DELETE FROM mesas WHERE id=:id AND empresa=:empresa');
 $q->execute([':id'=>$id,':empresa'=>$empresa]);
 $pdo->commit();echo 'Excluído com Sucesso';
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('mesas/excluir: '.$e->getMessage());
 http_response_code(500);echo 'Não foi possível excluir a mesa';
}
