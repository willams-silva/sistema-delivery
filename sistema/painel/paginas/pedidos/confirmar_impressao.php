<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function responderImpressao($codigo, $mensagem) {
    http_response_code($codigo);
    echo json_encode(['ok'=>$codigo===200,'mensagem'=>$mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') responderImpressao(405, 'Método inválido');
$empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$usuario = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario || !$id) responderImpressao(403, 'Sessão ou pedido inválido');
if (($_POST['confirmado'] ?? '') !== '1') responderImpressao(422, 'Confirme que o cupom foi realmente impresso');
require_once('../../../conexao.php');
try {
    $consulta = $pdo->prepare("UPDATE vendas SET impressao='Sim' WHERE id=:id AND empresa=:empresa AND (impressao IS NULL OR impressao<>'Sim')");
    $consulta->execute([':id'=>$id, ':empresa'=>$empresa]);
    if ($consulta->rowCount() !== 1) {
        $existe = $pdo->prepare("SELECT impressao FROM vendas WHERE id=:id AND empresa=:empresa");
        $existe->execute([':id'=>$id,':empresa'=>$empresa]);
        $estado = $existe->fetchColumn();
        if ($estado === false) responderImpressao(404, 'Pedido não encontrado nesta empresa');
        if ($estado !== 'Sim') responderImpressao(409, 'Não foi possível confirmar a impressão');
    }
    // O cron pode ter enfileirado o mesmo pedido: só limpa após confirmação humana.
    $estado = $pdo->prepare("UPDATE automacao_pedidos_estado SET imprimir_pendente=0 WHERE empresa=:empresa AND venda_id=:id");
    $estado->execute([':empresa'=>$empresa,':id'=>$id]);
    responderImpressao(200, 'Impressão confirmada');
} catch (Throwable $e) {
    error_log('Confirmacao impressao pedido: '.$e->getMessage());
    responderImpressao(500, 'Não foi possível registrar a confirmação');
}
