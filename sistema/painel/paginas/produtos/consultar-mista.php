<?php
@session_start();
require_once('../../../conexao.php');
header('Content-Type: application/json; charset=utf-8');
$empresa = (int)($_SESSION['empresa'] ?? 0);
$id = (int)($_GET['id'] ?? 0);
if (!$empresa || !$id) { http_response_code(403); echo json_encode(['limite'=>0]); exit; }
$stmt = $pdo->prepare("SELECT limite_proteinas FROM tapiocas_mistas_config WHERE produto_id = ? AND empresa = ?");
$stmt->execute([$id, $empresa]);
echo json_encode(['limite'=>(int)$stmt->fetchColumn()]);
