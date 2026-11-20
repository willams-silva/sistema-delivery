<?php
@session_start();
require_once("../../../conexao.php");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}
$id = (int) ($_POST['id'] ?? 0);
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$operador_id = (int) ($_SESSION['id'] ?? 0);
if ($id <= 0 || $empresa <= 0 || $operador_id <= 0) {
    http_response_code(403);
    exit('Sessão ou registro inválido');
}
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $operador_id, ':empresa' => $empresa]);
$nivel = $operador->fetchColumn();
if ($nivel === false) {
    http_response_code(403);
    exit('Operador não autorizado');
}
if ($nivel !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'clientes' LIMIT 1");
    $permissao->execute([':usuario' => $operador_id, ':empresa' => $empresa]);
    if (!$permissao->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão para gerenciar clientes');
    }
}
$cliente = $pdo->prepare("SELECT id FROM clientes WHERE id = :id AND empresa = :empresa LIMIT 1");
$cliente->execute([':id' => $id, ':empresa' => $empresa]);
if (!$cliente->fetchColumn()) {
    http_response_code(404);
    exit('Cliente não encontrado');
}
$pendencias = $pdo->prepare("SELECT 1 FROM receber WHERE cliente = :id AND empresa = :empresa AND pago = 'Não' LIMIT 1");
$pendencias->execute([':id' => $id, ':empresa' => $empresa]);
if ($pendencias->fetchColumn()) {
    http_response_code(409);
    exit('Você não pode excluir este cliente, existem valores a receber associados a ele!');
}
$excluir = $pdo->prepare("DELETE FROM clientes WHERE id = :id AND empresa = :empresa");
$excluir->execute([':id' => $id, ':empresa' => $empresa]);
if ($excluir->rowCount() !== 1) {
    http_response_code(409);
    exit('Não foi possível excluir o cliente');
}
echo 'Excluído com Sucesso';
?>