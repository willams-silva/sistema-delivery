<?php
@session_start();
require_once('../../../conexao.php');
$empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$usuario = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$setor = (string)($_POST['setor'] ?? '');
$acao = (string)($_POST['acao'] ?? '');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !$empresa || !$usuario || !$id ||
    !in_array($setor, ['Cozinha','Bar','Outro'], true) ||
    !in_array($acao, ['Aguardando','Preparando','Pronto','Finalizado'], true)) {
    http_response_code(400);
    exit('Requisição inválida');
}
$transicoes = [
    'Aguardando'=>['Preparando'],
    'Preparando'=>['Pronto'],
    'Pronto'=>['Finalizado']
];
$consulta = $pdo->prepare("SELECT c.status FROM carrinho c INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa WHERE c.id=:id AND c.empresa=:empresa AND p.preparado=:setor LIMIT 1");
$consulta->execute([':id'=>$id,':empresa'=>$empresa,':setor'=>$setor]);
$atual = $consulta->fetchColumn();
if ($atual === false) { http_response_code(404); exit('Item não encontrado neste setor'); }
if ($atual === $acao) exit('Alterado com Sucesso***');
if (!in_array($acao, $transicoes[$atual] ?? [], true)) {
    http_response_code(409);
    exit('Transição de status inválida');
}
$atualiza = $pdo->prepare("UPDATE carrinho SET status=:acao WHERE id=:id AND empresa=:empresa AND status=:atual");
$atualiza->execute([':acao'=>$acao,':id'=>$id,':empresa'=>$empresa,':atual'=>$atual]);
if ($atualiza->rowCount() !== 1) { http_response_code(409); exit('Status alterado por outro operador'); }
echo 'Alterado com Sucesso***';
