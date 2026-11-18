<?php
// Catálogo de personalizações para o PDV. Somente leitura; preços sempre vêm do banco.
@session_start();
header('Content-Type: application/json; charset=utf-8');
require_once('../../../conexao.php');
$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario = (int)($_SESSION['id'] ?? 0);
$produto = filter_input(INPUT_POST, 'produto', FILTER_VALIDATE_INT);
if (!$empresa || !$usuario) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada.']);
    exit;
}
if (!$produto || $produto < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'Produto inválido.']);
    exit;
}
$stmt = $pdo->prepare("SELECT p.id, p.nome, p.categoria, c.nome AS nome_categoria
    FROM produtos p JOIN categorias c ON c.id = p.categoria AND c.empresa = p.empresa
    WHERE p.id = ? AND p.empresa = ? AND p.ativo = 'Sim' AND c.ativo = 'Sim' LIMIT 1");
$stmt->execute([$produto, $empresa]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) {
    http_response_code(404);
    echo json_encode(['erro' => 'Produto indisponível.']);
    exit;
}
$nomeCategoria = mb_strtolower(trim((string)$p['nome_categoria']), 'UTF-8');
if (mb_strpos($nomeCategoria, 'tapioca') === false) {
    echo json_encode(['produto' => $p['id'], 'personalizavel' => false, 'itens' => []], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt = $pdo->prepare("SELECT id, nome, valor, tipo FROM adicionais_cat
    WHERE categoria = ? AND empresa = ? AND ativo = 'Sim' AND tipo IN ('adicional','proteina','opcional')
    ORDER BY tipo, nome");
$stmt->execute([(int)$p['categoria'], $empresa]);
$itens = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
    $itens[] = [
        'id' => (int)$item['id'],
        'nome' => (string)$item['nome'],
        'tipo' => (string)$item['tipo'],
        'valor' => $item['tipo'] === 'opcional' ? 0 : (float)$item['valor']
    ];
}
echo json_encode(['produto' => (int)$p['id'], 'personalizavel' => true, 'itens' => $itens], JSON_UNESCAPED_UNICODE);
