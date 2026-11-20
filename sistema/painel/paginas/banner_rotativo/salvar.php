<?php
@session_start();
require_once '../../../conexao.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=(int)($_SESSION['empresa']??0);
if($empresa<=0||!isset($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
$idRaw=$_POST['id']??'';
$id=($idRaw===''?0:filter_var($idRaw,FILTER_VALIDATE_INT));
$categoria=filter_var($_POST['categoria']??null,FILTER_VALIDATE_INT);
if($id===false||$id<0||$categoria===false||$categoria<0){http_response_code(400);exit('Dados inválidos');}
try{
 if($categoria>0){
  $q=$pdo->prepare('SELECT 1 FROM categorias WHERE id=:id AND empresa=:empresa');
  $q->execute([':id'=>$categoria,':empresa'=>$empresa]);
  if(!$q->fetchColumn()){http_response_code(400);exit('Categoria não pertence a esta empresa');}
 }
 $fotoAnterior='sem-foto.jpg';
 if($id>0){
  $q=$pdo->prepare('SELECT foto FROM banner_rotativo WHERE id=:id AND empresa=:empresa');
  $q->execute([':id'=>$id,':empresa'=>$empresa]);
  $fotoAnterior=$q->fetchColumn();
  if($fotoAnterior===false){http_response_code(404);exit('Banner não encontrado nesta empresa');}
 }
 $foto=$fotoAnterior;
 $novoArquivo=null;
 $upload=$_FILES['foto']??null;
 if($upload&&($upload['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
  if(($upload['error']??null)!==UPLOAD_ERR_OK){http_response_code(400);exit('Falha ao enviar a imagem');}
  if(!is_uploaded_file($upload['tmp_name']??'')){http_response_code(400);exit('Arquivo enviado inválido');}
  if(($upload['size']??0)<=0||$upload['size']>5*1024*1024){http_response_code(400);exit('Imagem deve ter no máximo 5 MB');}
  $info=@getimagesize($upload['tmp_name']);
  $tipos=[IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_GIF=>'gif'];
  if(!$info||!isset($tipos[$info[2]])){http_response_code(400);exit('Envie uma imagem JPG, PNG ou GIF válida');}
  if($info[0]<1||$info[1]<1||$info[0]>6000||$info[1]>6000){http_response_code(400);exit('Dimensões da imagem não permitidas');}
  $ext=$tipos[$info[2]];
  $dir=__DIR__.'/../../images/banner_rotativo/';
  $novoArquivo=bin2hex(random_bytes(16)).'.'.$ext;
  if(!move_uploaded_file($upload['tmp_name'],$dir.$novoArquivo)){http_response_code(503);exit('Não foi possível salvar a imagem');}
  $foto=$novoArquivo;
 }
 try{
  $pdo->beginTransaction();
  if($id>0){
   $q=$pdo->prepare('UPDATE banner_rotativo SET categoria=:categoria,foto=:foto WHERE id=:id AND empresa=:empresa');
   $q->execute([':categoria'=>$categoria,':foto'=>$foto,':id'=>$id,':empresa'=>$empresa]);
  }else{
   $q=$pdo->prepare('INSERT INTO banner_rotativo (categoria,foto,empresa) VALUES (:categoria,:foto,:empresa)');
   $q->execute([':categoria'=>$categoria,':foto'=>$foto,':empresa'=>$empresa]);
  }
  $pdo->commit();
 }catch(Throwable $e){
  if($pdo->inTransaction())$pdo->rollBack();
  if($novoArquivo&&is_file($dir.$novoArquivo))@unlink($dir.$novoArquivo);
  throw $e;
 }
 if($novoArquivo&&$fotoAnterior!=='sem-foto.jpg'&&preg_match('/^[a-zA-Z0-9_.-]+$/D',(string)$fotoAnterior)){
  $q=$pdo->prepare('SELECT COUNT(*) FROM banner_rotativo WHERE foto=:foto');
  $q->execute([':foto'=>$fotoAnterior]);
  if(!(int)$q->fetchColumn()){
   $antigo=__DIR__.'/../../images/banner_rotativo/'.$fotoAnterior;
   if(is_file($antigo))@unlink($antigo);
  }
 }
 echo 'Salvo com Sucesso';
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('Salvar banner: '.$e->getMessage());
 http_response_code(503);echo 'Não foi possível salvar o banner';
}
