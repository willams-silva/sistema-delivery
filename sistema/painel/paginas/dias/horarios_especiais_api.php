<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../../conexao.php';
function horario_responder($status, $dados) { http_response_code($status); echo json_encode($dados, JSON_UNESCAPED_UNICODE); exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') horario_responder(405, ['erro'=>'Método não permitido']);
$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario = (int)($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) horario_responder(403, ['erro'=>'Sessão inválida']);
try {
 $q = $pdo->prepare('SELECT nivel FROM usuarios WHERE id = :usuario AND empresa = :empresa LIMIT 1');
 $q->execute([':usuario'=>$usuario, ':empresa'=>$empresa]);
 $nivel = $q->fetchColumn();
 if ($nivel === false) horario_responder(403, ['erro'=>'Acesso negado']);
 if ($nivel !== 'Administrador') {
  $q = $pdo->prepare('SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.chave = :chave LIMIT 1');
  $q->execute([':usuario'=>$usuario, ':chave'=>'dias']);
  if (!$q->fetchColumn()) horario_responder(403, ['erro'=>'Sem permissão']);
 }
 $acao = (string)($_POST['acao'] ?? 'listar');
 if ($acao === 'listar') {
  $q = $pdo->prepare('SELECT id, data_especial, TIME_FORMAT(hora_inicio, "%H:%i") AS hora_inicio, TIME_FORMAT(hora_fim, "%H:%i") AS hora_fim, descricao FROM horarios_especiais WHERE empresa = :empresa ORDER BY data_especial');
  $q->execute([':empresa'=>$empresa]);
  horario_responder(200, ['registros'=>$q->fetchAll(PDO::FETCH_ASSOC)]);
 }
 if ($acao === 'salvar') {
  $data = (string)($_POST['data_especial'] ?? '');
  $inicio = (string)($_POST['hora_inicio'] ?? '');
  $fim = (string)($_POST['hora_fim'] ?? '');
  $descricao = trim((string)($_POST['descricao'] ?? ''));
  $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
  if (!$dt || $dt->format('Y-m-d') !== $data || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $inicio) || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $fim) || $fim === $inicio || mb_strlen($descricao, 'UTF-8') > 150) {
   horario_responder(400, ['erro'=>'Informe uma data e horário válidos. A abertura e o fechamento devem ser diferentes; horários noturnos são permitidos.']);
  }
  $q = $pdo->prepare('SELECT 1 FROM fechamentos_excepcionais WHERE empresa = :empresa AND data_fechamento = :data LIMIT 1');
  $q->execute([':empresa'=>$empresa, ':data'=>$data]);
  if ($q->fetchColumn()) horario_responder(409, ['erro'=>'Esta data está marcada como fechamento integral. Exclua o fechamento antes de cadastrar o horário especial.']);
  $q = $pdo->prepare('INSERT INTO horarios_especiais (empresa, data_especial, hora_inicio, hora_fim, descricao) VALUES (:empresa, :data, :inicio, :fim, :descricao) ON DUPLICATE KEY UPDATE hora_inicio = VALUES(hora_inicio), hora_fim = VALUES(hora_fim), descricao = VALUES(descricao)');
  $q->execute([':empresa'=>$empresa, ':data'=>$data, ':inicio'=>$inicio, ':fim'=>$fim, ':descricao'=>$descricao]);
  horario_responder(200, ['sucesso'=>true]);
 }
 if ($acao === 'excluir') {
  $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
  if (!$id || $id <= 0) horario_responder(400, ['erro'=>'Registro inválido']);
  $q = $pdo->prepare('DELETE FROM horarios_especiais WHERE id = :id AND empresa = :empresa');
  $q->execute([':id'=>$id, ':empresa'=>$empresa]);
  horario_responder($q->rowCount()?200:404, $q->rowCount()?['sucesso'=>true]:['erro'=>'Registro não encontrado']);
 }
 horario_responder(400, ['erro'=>'Ação inválida']);
} catch (PDOException $e) {
 error_log('Horários especiais: '.$e->getMessage());
 horario_responder(503, ['erro'=>'Horários especiais indisponíveis. Confirme a criação da tabela no banco.']);
}
