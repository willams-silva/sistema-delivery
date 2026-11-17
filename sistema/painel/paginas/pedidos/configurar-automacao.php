<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['empresa']) || empty($_SESSION['id'])) { http_response_code(401); echo json_encode(['erro'=>'Sessão expirada']); exit; }
require_once '../../../conexao.php';
$empresa=(int)$_SESSION['empresa'];
$usuario=(int)$_SESSION['id'];
$auth=$pdo->prepare("SELECT nivel FROM usuarios WHERE id=? AND empresa=? LIMIT 1");
$auth->execute([$usuario,$empresa]);
$nivel=$auth->fetchColumn();
if ($nivel !== 'Administrador') { http_response_code(403); echo json_encode(['erro'=>'Apenas administradores podem alterar a automação']); exit; }
try {
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  $ativo=isset($_POST['ativo']) && $_POST['ativo']==='1'?1:0;
  $aceitar=filter_var($_POST['aceitar_segundos']??null,FILTER_VALIDATE_INT);
  $preparar=filter_var($_POST['preparar_segundos']??null,FILTER_VALIDATE_INT);
  $imprimir=isset($_POST['imprimir_ao_aceitar']) && $_POST['imprimir_ao_aceitar']==='1'?1:0;
  if ($aceitar===false || $preparar===false || $aceitar<60 || $preparar<60 || $aceitar>3600 || $preparar>3600) {
    http_response_code(422); echo json_encode(['erro'=>'Informe tempos entre 1 e 60 minutos']); exit;
  }
  $stmt=$pdo->prepare("INSERT INTO automacao_pedidos_config (empresa,ativo,aceitar_segundos,preparar_segundos,imprimir_ao_aceitar) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE ativo=VALUES(ativo),aceitar_segundos=VALUES(aceitar_segundos),preparar_segundos=VALUES(preparar_segundos),imprimir_ao_aceitar=VALUES(imprimir_ao_aceitar)");
  $stmt->execute([$empresa,$ativo,$aceitar,$preparar,$imprimir]);
 }
 $stmt=$pdo->prepare("SELECT ativo,aceitar_segundos,preparar_segundos,imprimir_ao_aceitar FROM automacao_pedidos_config WHERE empresa=?");
 $stmt->execute([$empresa]);
 echo json_encode(['ok'=>true,'config'=>$stmt->fetch(PDO::FETCH_ASSOC)?:['ativo'=>0,'aceitar_segundos'=>60,'preparar_segundos'=>120,'imprimir_ao_aceitar'=>0]]);
} catch(Throwable $e) { http_response_code(500); error_log($e->getMessage()); echo json_encode(['erro'=>'Não foi possível acessar as configurações. Confira a migração SQL.']); }
