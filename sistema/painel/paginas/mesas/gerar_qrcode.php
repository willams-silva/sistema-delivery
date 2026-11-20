<?php
@session_start();
require_once '../../../conexao.php';
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método não permitido');}
$empresa=(int)($_SESSION['empresa']??0);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if($empresa<=0||empty($_SESSION['id'])){http_response_code(403);exit('Sessão inválida');}
if(!$id||$id<=0){http_response_code(400);exit('Mesa inválida');}
$q=$pdo->prepare('SELECT nome FROM mesas WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$id,':empresa'=>$empresa]);
$nome=$q->fetchColumn();
if($nome===false){http_response_code(404);exit('Mesa não encontrada');}
$url=($url_sistema??'').'mesa/'.$id;
$qr='https://api.qrserver.com/v1/create-qr-code/?size=350x350&data='.rawurlencode($url);
echo '<div class="p-3"><h5>Mesa '.htmlspecialchars((string)$nome,ENT_QUOTES,'UTF-8').'</h5><img class="img-fluid" style="max-width:350px" src="'.htmlspecialchars($qr,ENT_QUOTES,'UTF-8').'" alt="QR Code da mesa"><p class="small text-muted mt-2">Aponte a câmera para abrir o cardápio da mesa.</p><a class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer" href="'.htmlspecialchars($qr,ENT_QUOTES,'UTF-8').'">Abrir QR Code para imprimir</a></div>';
