<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../../sistema/conexao.php');

function resposta($ok, $mensagem) {
  echo json_encode(['ok'=>$ok, 'mensagem'=>$mensagem], JSON_UNESCAPED_UNICODE);
  exit;
}

$url_site = $_SESSION['url_site'] ?? '';
if ($url_site === '') resposta(false, 'Sessão do cardápio expirada. Atualize a página.');

$q = $pdo->prepare("SELECT empresa FROM config WHERE url_site = :url LIMIT 1");
$q->execute([':url'=>$url_site]);
$id_empresa = (int)$q->fetchColumn();
if ($id_empresa <= 0) resposta(false, 'Não foi possível identificar a loja.');

$acao = $_POST['acao'] ?? '';
if ($acao !== 'validar_telefone') resposta(false, 'Ação inválida.');

$telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
if (strlen($telefone) < 10 || strlen($telefone) > 13) resposta(false, 'Informe um telefone válido com DDD.');

// Limita tentativas nesta sessão para reduzir consultas automatizadas.
$inicio = (int)($_SESSION['meus_pedidos_limite_inicio'] ?? 0);
if (!$inicio || time() - $inicio > 900) {
  $_SESSION['meus_pedidos_limite_inicio'] = time();
  $_SESSION['meus_pedidos_tentativas_telefone'] = 0;
}
$_SESSION['meus_pedidos_tentativas_telefone'] = (int)($_SESSION['meus_pedidos_tentativas_telefone'] ?? 0) + 1;
if ($_SESSION['meus_pedidos_tentativas_telefone'] > 10) {
  resposta(false, 'Muitas tentativas. Aguarde 15 minutos e tente novamente.');
}

$q = $pdo->prepare("SELECT telefone FROM clientes WHERE empresa = :empresa");
$q->execute([':empresa'=>$id_empresa]);
$clientes = $q->fetchAll(PDO::FETCH_COLUMN);
$telefone_cadastrado = '';
foreach ($clientes as $telefone_cliente) {
  if (preg_replace('/\D+/', '', (string)$telefone_cliente) === $telefone) {
    $telefone_cadastrado = (string)$telefone_cliente;
    break;
  }
}

if ($telefone_cadastrado === '') resposta(false, 'Telefone não encontrado. Confira o número cadastrado no pedido.');

$_SESSION['meus_pedidos_telefone_autenticado'] = $telefone_cadastrado;
$_SESSION['meus_pedidos_empresa_autenticada'] = $id_empresa;
$_SESSION['meus_pedidos_autenticado_em'] = time();
resposta(true, 'Telefone localizado. Carregando seus pedidos.');
?>