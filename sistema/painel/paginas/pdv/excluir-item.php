<?php
@session_start();
require_once("../../../conexao.php");

$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario = (int)($_SESSION['id'] ?? 0);
$mesa = isset($_SESSION['id_ab_mesa']) && (int)$_SESSION['id_ab_mesa'] > 0
    ? (string)(int)$_SESSION['id_ab_mesa'] : '';
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($empresa <= 0 || $usuario <= 0) {
    http_response_code(403);
    exit('Sessão expirada');
}
if (!$id || $id <= 0) {
    http_response_code(400);
    exit('Item inválido');
}

try {
    $pdo->beginTransaction();
    // Excluir somente itens da comanda atual do operador.
    $stmt = $pdo->prepare("SELECT id FROM carrinho WHERE id = ? AND sessao = ? AND empresa = ? AND pedido = 0 AND mesa = ? FOR UPDATE");
    $stmt->execute([$id, (string)$usuario, $empresa, $mesa]);
    if (!$stmt->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Item não encontrado nesta comanda');
    }

    // Evitar adicionais órfãos depois de remover o produto.
    $stmt = $pdo->prepare("DELETE FROM temp WHERE carrinho = ? AND empresa = ? AND tabela = 'adicionais'");
    $stmt->execute([$id, $empresa]);

    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id = ? AND sessao = ? AND empresa = ? AND pedido = 0 AND mesa = ?");
    $stmt->execute([$id, (string)$usuario, $empresa, $mesa]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Falha ao remover item da comanda');
    }
    $pdo->commit();
    echo 'Excluído com Sucesso';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[PDV excluir-item] ' . $e->getMessage());
    http_response_code(500);
    echo 'Não foi possível remover o item.';
}
