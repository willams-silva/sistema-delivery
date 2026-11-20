<?php
@session_start();
require_once '../../../conexao.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=(int)($_SESSION['empresa']??0);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if($empresa<=0||!isset($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
if(!$id||$id<=0){http_response_code(400);exit('Banner inválido');}
try{
 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT foto FROM banner_rotativo WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $q->execute([':id'=>$id,':empresa'=>$empresa]);
 $foto=$q->fetchColumn();
 if($foto===false){$pdo->rollBack();http_response_code(404);exit('Banner não encontrado nesta empresa');}
 $q=$pdo->prepare('DELETE FROM banner_rotativo WHERE id=:id AND empresa=:empresa');
 $q->execute([':id'=>$id,':empresa'=>$empresa]);
 $pdo->commit();
 // Remover somente arquivos próprios, não utilizados por outros banners.
 if($foto!=='sem-foto.jpg'&&preg_match('/^[a-zA-Z0-9_.-]+$/D',(string)$foto)){
  $q=$pdo->prepare('SELECT COUNT(*) FROM banner_rotativo WHERE foto=:foto');
  $q->execute([':foto'=>$foto]);
  if(!(int)$q->fetchColumn()){
   $arquivo=__DIR__.'/../../images/banner_rotativo/'.$foto;
   if(is_file($arquivo))@unlink($arquivo);
  }
 }
 echo 'Excluído com Sucesso';
}catch(PDOException $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('Excluir banner: '.$e->getMessage());
 http_response_code(503);echo 'Não foi possível excluir o banner';
}
