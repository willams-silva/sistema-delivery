<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once('../../sistema/conexao.php');
$empresa = (int)($_SESSION['empresa'] ?? 0);
$sessao = (string)($_SESSION['sessao_usuario'] ?? '');
if (!$empresa || !$sessao) { echo json_encode(['itens'=>0,'total'=>0]); exit; }
$stmt = $pdo->prepare("SELECT COUNT(*) AS itens, COALESCE(SUM(CAST(total_item AS DECIMAL(12,2)) * CAST(quantidade AS DECIMAL(12,3))),0) AS total FROM carrinho WHERE sessao = ? AND empresa = ?");
$stmt->execute([$sessao,$empresa]);
$r = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
echo json_encode(['itens'=>(int)($r['itens'] ?? 0),'total'=>(float)($r['total'] ?? 0)]);
