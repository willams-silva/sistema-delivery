<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once('../../../conexao.php');
$empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$usuario = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$setor = (string)($_GET['setor'] ?? '');
if (!$empresa || !$usuario || !$id || !in_array($setor, ['Cozinha','Bar','Outro'], true) ||
    ($_SESSION['aut_token_SaL1P'] ?? '') !== '25tNX1L1MSaL1P') {
    http_response_code(403); exit(json_encode(['erro'=>'Acesso negado']));
}
try {
    $consulta = $pdo->prepare("SELECT 1 FROM carrinho c INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa
        WHERE c.id=:id AND c.empresa=:empresa AND p.preparado=:setor LIMIT 1");
    $consulta->execute([':id'=>$id, ':empresa'=>$empresa, ':setor'=>$setor]);
    if (!$consulta->fetchColumn()) { http_response_code(404); exit(json_encode(['erro'=>'Item nao encontrado'])); }
    $stmt = $pdo->prepare("SELECT usuario_id, status_anterior, status_novo, criado_em
        FROM pedidos_locais_historico WHERE empresa=:empresa AND carrinho_id=:id AND setor=:setor
        ORDER BY id DESC LIMIT 50");
    $stmt->execute([':empresa'=>$empresa, ':id'=>$id, ':setor'=>$setor]);
    echo json_encode(['historico'=>$stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['erro'=>'Historico indisponivel. Verifique a migracao SQL.']);
}
