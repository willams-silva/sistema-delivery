<?php
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
$sessao = @$_SESSION['sessao_usuario'];
$id_temp = (int) (@$_POST['id'] ?? 0);
$id_carrinho = (int) (@$_POST['carrinho'] ?? 0);

if ($id_empresa == '' || $id_temp <= 0 || $id_carrinho <= 0) {
    echo 'Dados inválidos';
    exit();
}

$stmt = $pdo->prepare("SELECT t.valor_item, t.quantidade, c.quantidade AS quantidade_carrinho
                       FROM temp t
                       INNER JOIN carrinho c ON c.id = t.carrinho
                       WHERE t.id = ? AND t.carrinho = ? AND t.tabela = 'adicionais'
                       AND t.empresa = ? AND c.empresa = ? LIMIT 1");
$stmt->execute([$id_temp, $id_carrinho, $id_empresa, $id_empresa]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dados) {
    echo 'Adicional não encontrado';
    exit();
}

$valor_excluir = (float)$dados['valor_item'] * (int)$dados['quantidade'] * max(1, (int)$dados['quantidade_carrinho']);
$stmt = $pdo->prepare("DELETE FROM temp WHERE id = ? AND carrinho = ? AND empresa = ?");
$stmt->execute([$id_temp, $id_carrinho, $id_empresa]);

$stmt = $pdo->prepare("UPDATE carrinho SET total_item = GREATEST(0, total_item - ?) WHERE id = ? AND empresa = ?");
$stmt->execute([$valor_excluir, $id_carrinho, $id_empresa]);

echo 'Excluido com Sucesso';
