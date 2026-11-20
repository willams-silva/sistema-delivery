<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['empresa'])) { http_response_code(401); echo json_encode(['erro'=>'Sessão inválida']); exit; }
require_once("../../conexao.php");
require_once("../buscar_config.php");
$empresa = (int) $_SESSION['empresa'];
$incluiDelivery = isset($delivery_monitor) && $delivery_monitor === 'Sim';
$origem = $incluiDelivery ? '(c.cliente > 0 OR c.mesa > 0)' : 'c.mesa > 0';
try {
    $sql = "SELECT p.preparado AS setor, c.status, COUNT(*) AS total
            FROM carrinho c INNER JOIN produtos p ON p.id = c.produto AND p.empresa = c.empresa
            WHERE c.empresa = :empresa AND $origem
              AND p.preparado IN ('Cozinha','Bar','Outro')
              AND c.status IN ('Aguardando','Preparando','Pronto')
            GROUP BY p.preparado, c.status";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':empresa'=>$empresa]);
    $dados = [];
    foreach (['Cozinha','Bar','Outro'] as $setor) $dados[$setor] = ['Aguardando'=>0,'Preparando'=>0,'Pronto'=>0,'total'=>0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $setor = $linha['setor']; $status = $linha['status']; $n = (int)$linha['total'];
        $dados[$setor][$status] = $n; $dados[$setor]['total'] += $n;
    }
    echo json_encode(['setores'=>$dados], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['erro'=>'Falha ao consultar contadores']);
}
