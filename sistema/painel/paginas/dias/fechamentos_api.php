<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../conexao.php';
function responder($codigo, $dados) { http_response_code($codigo); echo json_encode($dados, JSON_UNESCAPED_UNICODE); exit; }
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if (!$empresa || !$usuario) responder(403, ['erro'=>'Sessão expirada']);
$q = $pdo->prepare('SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1');
$q->execute([':id'=>$usuario, ':empresa'=>$empresa]);
$nivel = $q->fetchColumn();
if ($nivel === false) responder(403, ['erro'=>'Acesso negado']);
if ($nivel !== 'Administrador') {
 $q = $pdo->prepare('SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.chave = :chave LIMIT 1');
 $q->execute([':usuario'=>$usuario, ':chave'=>'dias']);
 if (!$q->fetchColumn()) responder(403, ['erro'=>'Sem permissão para dias fechados']);
}
$acao = (string) ($_POST['acao'] ?? 'listar');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') responder(405, ['erro'=>'Método inválido']);
try {
 if ($acao === 'listar') {
  $q = $pdo->prepare('SELECT id, data_fechamento, motivo FROM fechamentos_excepcionais WHERE empresa = :empresa ORDER BY data_fechamento ASC');
  $q->execute([':empresa'=>$empresa]);
  responder(200, ['registros'=>$q->fetchAll(PDO::FETCH_ASSOC)]);
 }
 if ($acao === 'salvar') {
  $data = (string) ($_POST['data_fechamento'] ?? '');
  $motivo = trim((string) ($_POST['motivo'] ?? ''));
  $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
  if (!$dt || $dt->format('Y-m-d') !== $data || mb_strlen($motivo, 'UTF-8') > 150) responder(400, ['erro'=>'Informe uma data e motivo válidos']);
  $q = $pdo->prepare('INSERT INTO fechamentos_excepcionais (empresa, data_fechamento, motivo) VALUES (:empresa, :data, :motivo) ON DUPLICATE KEY UPDATE motivo = VALUES(motivo)');
  $q->execute([':empresa'=>$empresa, ':data'=>$data, ':motivo'=>$motivo]);
  responder(200, ['sucesso'=>true]);
 }
 if ($acao === 'excluir') {
  $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
  if (!$id || $id <= 0) responder(400, ['erro'=>'Registro inválido']);
  $q = $pdo->prepare('DELETE FROM fechamentos_excepcionais WHERE id = :id AND empresa = :empresa');
  $q->execute([':id'=>$id, ':empresa'=>$empresa]);
  responder($q->rowCount()?200:404, $q->rowCount()?['sucesso'=>true]:['erro'=>'Registro não encontrado']);
 }
 responder(400, ['erro'=>'Ação inválida']);
} catch (PDOException $e) {
 error_log('Fechamentos excepcionais: '.$e->getMessage());
 responder(503, ['erro'=>'Cadastro indisponível. Verifique se a tabela de fechamentos excepcionais foi criada.']);
}
