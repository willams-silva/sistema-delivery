<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
function bcPrevisaoResposta(int $codigo, array $dados): void {
 http_response_code($codigo);
 echo json_encode($dados, JSON_UNESCAPED_UNICODE);
 exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') bcPrevisaoResposta(405, ['erro'=>'Método não permitido']);
$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario = (int)($_SESSION['id'] ?? 0);
if (!$empresa || !$usuario) bcPrevisaoResposta(403, ['erro'=>'Sessão expirada']);
require_once '../../../conexao.php';
require_once '../../../horarios_especiais.php';
try {
 $q = $pdo->prepare('SELECT nivel FROM usuarios WHERE id = :usuario AND empresa = :empresa LIMIT 1');
 $q->execute([':usuario'=>$usuario, ':empresa'=>$empresa]);
 $nivel = $q->fetchColumn();
 if ($nivel === false) bcPrevisaoResposta(403, ['erro'=>'Acesso negado']);
 if ($nivel !== 'Administrador') {
  $q = $pdo->prepare('SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.chave = :chave LIMIT 1');
  $q->execute([':usuario'=>$usuario, ':chave'=>'dias']);
  if (!$q->fetchColumn()) bcPrevisaoResposta(403, ['erro'=>'Sem permissão']);
 }
 $data = (string)($_POST['data'] ?? '');
 $hora = (string)($_POST['hora'] ?? '');
 $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
 if (!$dt || $dt->format('Y-m-d') !== $data || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
  bcPrevisaoResposta(400, ['erro'=>'Informe data e horário válidos']);
 }
 $q = $pdo->prepare('SELECT horario_abertura, horario_fechamento, status_estabelecimento, pausar_delivery FROM config WHERE empresa = :empresa LIMIT 1');
 $q->execute([':empresa'=>$empresa]);
 $config = $q->fetch(PDO::FETCH_ASSOC);
 if (!$config) bcPrevisaoResposta(404, ['erro'=>'Configuração da empresa não encontrada']);
 $estado = bcEstadoDelivery(
  $pdo, $empresa, $data, $hora . ':00',
  (string)($config['pausar_delivery'] ?? 'Não'),
  (string)($config['status_estabelecimento'] ?? 'Aberto'),
  (string)($config['horario_abertura'] ?? '18:00'),
  (string)($config['horario_fechamento'] ?? '00:00')
 );
 $descricoes = [
  'pausado'=>'Delivery pausado manualmente',
  'manual'=>'Estabelecimento fechado manualmente',
  'fechamento_integral'=>'Fechamento integral nesta data',
  'continuidade_especial'=>'Continuação de horário especial do dia anterior',
  'horario_especial'=>'Dentro do horário especial cadastrado',
  'fora_horario_especial'=>'Fora do horário especial cadastrado',
  'continuidade_madrugada'=>'Continuação do expediente normal do dia anterior',
  'fechamento_semanal'=>'Dia semanal configurado como fechado',
  'horario_normal'=>'Dentro do horário normal',
  'fora_horario_normal'=>'Fora do horário normal'
 ];
 bcPrevisaoResposta(200, [
  'aberto'=>(bool)$estado['aberto'],
  'status'=>$estado['motivo'] === 'pausado' ? 'PAUSADO' : ($estado['aberto'] ? 'ABERTO' : 'FECHADO'),
  'motivo'=>$descricoes[$estado['motivo']] ?? 'Regra de funcionamento',
  'data'=>$data, 'hora'=>$hora,
  'observacao'=>'Prévia das regras cadastradas atualmente. Não altera a configuração nem garante o estado futuro de pausas ou fechamentos manuais.'
 ]);
} catch (PDOException $e) {
 error_log('Prévia de funcionamento: '.$e->getMessage());
 bcPrevisaoResposta(503, ['erro'=>'Não foi possível consultar as regras de funcionamento.']);
}
