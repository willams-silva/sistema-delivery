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

require_once('../../sistema/painel/buscar_config.php');
$acao = $_POST['acao'] ?? '';

if ($acao === 'enviar_codigo') {
  $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
  if (strlen($telefone) < 10 || strlen($telefone) > 13) resposta(false, 'Informe um telefone válido com DDD.');

  if (!empty($_SESSION['meus_pedidos_ultimo_envio']) && time() - $_SESSION['meus_pedidos_ultimo_envio'] < 60) {
    resposta(false, 'Aguarde 1 minuto antes de solicitar outro código.');
  }
  if (($_SESSION['meus_pedidos_tentativas_envio'] ?? 0) >= 5) {
    resposta(false, 'Limite de solicitações atingido. Tente novamente mais tarde.');
  }

  $clientes = $pdo->query("SELECT id, telefone FROM clientes WHERE empresa = " . $id_empresa)->fetchAll(PDO::FETCH_ASSOC);
  $telefone_cadastrado = '';
  foreach ($clientes as $cliente) {
    if (preg_replace('/\D+/', '', $cliente['telefone']) === $telefone) {
      $telefone_cadastrado = $cliente['telefone'];
      break;
    }
  }
  if ($telefone_cadastrado === '') resposta(false, 'Se o telefone estiver cadastrado, você receberá um código. Confira o número informado ou faça um pedido primeiro.');

  if (($api_whatsapp ?? 'Não') === 'Não' || empty($token_whatsapp) || empty($instancia_whatsapp)) {
    resposta(false, 'O envio de código pelo WhatsApp não está configurado nesta loja. Entre em contato com o estabelecimento.');
  }

  $codigo = (string)random_int(100000, 999999);
  $_SESSION['meus_pedidos_codigo_hash'] = password_hash($codigo, PASSWORD_DEFAULT);
  $_SESSION['meus_pedidos_codigo_expira'] = time() + 300;
  $_SESSION['meus_pedidos_telefone_pendente'] = $telefone_cadastrado;
  $_SESSION['meus_pedidos_ultimo_envio'] = time();
  $_SESSION['meus_pedidos_tentativas_envio'] = ($_SESSION['meus_pedidos_tentativas_envio'] ?? 0) + 1;
  $_SESSION['meus_pedidos_tentativas_codigo'] = 0;

  $telefone_envio = '55' . ltrim(preg_replace('/\D+/', '', $telefone_cadastrado), '0');
  $mensagem = "Seu código para consultar os pedidos é: " . $codigo . "\nEste código expira em 5 minutos. Não compartilhe com ninguém.";
  require(__DIR__ . '/api_texto.php');

  if (($api_whatsapp ?? '') === 'evolution' && empty($retorno['ok'])) {
    unset($_SESSION['meus_pedidos_codigo_hash'], $_SESSION['meus_pedidos_codigo_expira'], $_SESSION['meus_pedidos_telefone_pendente']);
    resposta(false, 'Não foi possível enviar o código pelo WhatsApp. Verifique a integração da loja.');
  }

  resposta(true, 'Se o telefone estiver cadastrado, o código foi enviado pelo WhatsApp.');
}

if ($acao === 'validar_codigo') {
  $codigo = preg_replace('/\D+/', '', $_POST['codigo'] ?? '');
  $hash = $_SESSION['meus_pedidos_codigo_hash'] ?? '';
  $expira = (int)($_SESSION['meus_pedidos_codigo_expira'] ?? 0);
  if ($hash === '' || time() > $expira) resposta(false, 'Código expirado. Solicite um novo código.');
  if (strlen($codigo) !== 6 || !password_verify($codigo, $hash)) {
    $_SESSION['meus_pedidos_tentativas_codigo'] = ($_SESSION['meus_pedidos_tentativas_codigo'] ?? 0) + 1;
    if ($_SESSION['meus_pedidos_tentativas_codigo'] >= 5) {
      unset($_SESSION['meus_pedidos_codigo_hash'], $_SESSION['meus_pedidos_codigo_expira'], $_SESSION['meus_pedidos_telefone_pendente']);
      resposta(false, 'Muitas tentativas. Solicite um novo código.');
    }
    resposta(false, 'Código inválido. Confira a mensagem recebida.');
  }

  $_SESSION['meus_pedidos_telefone_autenticado'] = $_SESSION['meus_pedidos_telefone_pendente'];
  $_SESSION['meus_pedidos_empresa_autenticada'] = $id_empresa;
  $_SESSION['meus_pedidos_autenticado_em'] = time();
  unset($_SESSION['meus_pedidos_codigo_hash'], $_SESSION['meus_pedidos_codigo_expira'], $_SESSION['meus_pedidos_telefone_pendente'], $_SESSION['meus_pedidos_tentativas_codigo']);
  resposta(true, 'Telefone confirmado. Carregando seus pedidos.');
}

resposta(false, 'Ação inválida.');
?>