<?php
@session_start();
require_once '../../../conexao.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }
$empresa=(int)($_SESSION['empresa']??0);
if ($empresa<=0 || empty($_SESSION['id'])) { http_response_code(403); exit('Sessão inválida'); }
$idRaw=$_POST['id']??'';
$id=$idRaw===''?0:filter_var($idRaw,FILTER_VALIDATE_INT);
$nome=filter_var($_POST['nome']??null,FILTER_VALIDATE_INT);
if ($id===false || $id<0 || $nome===false || $nome<=0 || $nome>99999) { http_response_code(400); exit('Informe um número de mesa válido'); }
try {
 $pdo->beginTransaction();
 if ($id>0) {
  $q=$pdo->prepare('SELECT id FROM mesas WHERE id=:id AND empresa=:empresa FOR UPDATE');
  $q->execute([':id'=>$id,':empresa'=>$empresa]);
  if (!$q->fetchColumn()) { $pdo->rollBack(); http_response_code(404); exit('Mesa não encontrada nesta empresa'); }
 }
 $q=$pdo->prepare('SELECT id FROM mesas WHERE nome=:nome AND empresa=:empresa AND id<>:id LIMIT 1');
 $q->execute([':nome'=>$nome,':empresa'=>$empresa,':id'=>$id]);
 if ($q->fetchColumn()) { $pdo->rollBack(); http_response_code(409); exit('Número de mesa já cadastrado'); }
 if ($id>0) {
  $q=$pdo->prepare('UPDATE mesas SET nome=:nome WHERE id=:id AND empresa=:empresa');
  $q->execute([':nome'=>$nome,':id'=>$id,':empresa'=>$empresa]);
 } else {
  $q=$pdo->prepare("INSERT INTO mesas (nome,ativo,status,empresa) VALUES (:nome,'Sim','Fechada',:empresa)");
  $q->execute([':nome'=>$nome,':empresa'=>$empresa]);
 }
 $pdo->commit(); echo 'Salvo com Sucesso';
} catch(Throwable $e) {
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('mesas/salvar: '.$e->getMessage());
 http_response_code(500); echo 'Erro ao salvar a mesa';
}
