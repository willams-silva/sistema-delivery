<?php
@session_start();
$id_empresa=(int)($_SESSION['empresa']??0);
$id_usuario=(int)($_SESSION['id']??0);
if($id_empresa<1||$id_usuario<1){http_response_code(403);exit('Sessão inválida.');}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método inválido.');}
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$id){http_response_code(400);exit('Conta inválida.');}
require_once('../../../conexao.php');
require_once('../../buscar_config.php');
require_once __DIR__ . '/../../includes/auditoria_financeira.php';
try{
 $pdo->beginTransaction();
 $st=$pdo->prepare('SELECT id,arquivo,hash,descricao,valor,vencimento,data_pgto,forma_pgto,pago,subtotal,frequencia FROM pagar WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $st->execute([':id'=>$id,':empresa'=>$id_empresa]);$conta=$st->fetch(PDO::FETCH_ASSOC);
 if(!$conta){$pdo->rollBack();http_response_code(404);exit('Conta não encontrada neste estabelecimento.');}
 $st=$pdo->prepare('DELETE FROM pagar WHERE id=:id AND empresa=:empresa');
 $st->execute([':id'=>$id,':empresa'=>$id_empresa]);
 if($st->rowCount()!==1)throw new RuntimeException('Exclusão não confirmada.');
 registrarAuditoriaFinanceira($pdo,$id_empresa,'pagar',(int)$id,'EXCLUIR',$id_usuario,$conta,null);
 $pdo->commit();
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('Excluir conta a pagar: '.$e->getMessage());
 http_response_code(500);exit('Não foi possível excluir a conta.');
}
// A limpeza de anexos e notificações acontece somente após exclusão confirmada.
$foto=(string)($conta['arquivo']??'');
if($foto!==''&&$foto!=='sem-foto.png'&&basename($foto)===$foto){
 $diretorio=realpath('../../images/contas');
 if($diretorio!==false){
  $arquivo=$diretorio.DIRECTORY_SEPARATOR.$foto;
  if(is_file($arquivo)&&!is_link($arquivo))@unlink($arquivo);
 }
}
$hash=(string)($conta['hash']??'');
$referencia_agendamento='pagar';
if($hash!==''){
 try{require('../../apis/cancelar_agendamento.php');}
 catch(Throwable $e){error_log('Cancelar agendamento de conta excluída: '.$e->getMessage());}
}
echo 'Excluído com Sucesso';
?>