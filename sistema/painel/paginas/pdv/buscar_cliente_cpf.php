<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['empresa'])) {
    http_response_code(401);
    echo json_encode(['encontrado' => false, 'mensagem' => 'Sessão expirada. Entre novamente.']);
    exit;
}
require_once('../../../conexao.php');
$cpf = preg_replace('/\D+/', '', (string)($_POST['cpf'] ?? ''));
if (strlen($cpf) !== 11) {
    echo json_encode(['encontrado' => false, 'mensagem' => 'CPF inválido. Digite 11 números.']);
    exit;
}
try {
    // O cadastro pode conter CPF com ou sem pontuação.
    $stmt = $pdo->prepare("SELECT id, nome FROM clientes WHERE empresa = :empresa AND REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = :cpf LIMIT 1");
    $stmt->execute([':empresa' => $_SESSION['empresa'], ':cpf' => $cpf]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cliente) {
        echo json_encode(['encontrado' => true, 'id' => (int)$cliente['id'], 'nome' => $cliente['nome']], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['encontrado' => false, 'mensagem' => 'CPF não cadastrado. Clique em + para cadastrar.'], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['encontrado' => false, 'mensagem' => 'Falha ao consultar cliente.']);
}
