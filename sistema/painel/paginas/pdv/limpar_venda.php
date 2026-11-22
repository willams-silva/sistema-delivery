<?php
@session_start();
require_once("../../../conexao.php");

$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario = (int)($_SESSION['id'] ?? 0);
$mesa = isset($_SESSION['id_ab_mesa']) && (int)$_SESSION['id_ab_mesa'] > 0
    ? (string)(int)$_SESSION['id_ab_mesa'] : '';

if ($empresa <= 0 || $usuario <= 0) {
    http_response_code(403);
    exit('Sessão expirada. Faça login novamente.');
}
if ($mesa !== '') {
    http_response_code(400);
    exit('Esta ação só limpa vendas do balcão. Para mesas, utilize o fluxo da mesa.');
}
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT id FROM carrinho WHERE sessao = ? AND empresa = ? AND pedido = 0 AND (mesa = '' OR mesa = '0' OR mesa IS NULL) FOR UPDATE");
    $stmt->execute([(string)$usuario, $empresa]);
    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM temp WHERE empresa = ? AND carrinho IN ($placeholders) AND tabela = 'adicionais'");
        $stmt->execute(array_merge([$empresa], $ids));
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE sessao = ? AND empresa = ? AND pedido = 0 AND (mesa = '' OR mesa = '0' OR mesa IS NULL)");
        $stmt->execute([(string)$usuario, $empresa, '']);
    }
    $pdo->commit();
    echo 'Limpo com Sucesso';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[PDV limpar_venda] ' . $e->getMessage());
    http_response_code(500);
    echo 'Não foi possível limpar a comanda. Tente novamente.';
}
