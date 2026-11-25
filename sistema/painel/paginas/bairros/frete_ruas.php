<?php
$chave = 'bairros';
@session_start();
require_once("../../../conexao.php");
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$op = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$op->execute([':id'=>$usuario, ':empresa'=>$empresa]);
$nivel = $op->fetchColumn();
if ($nivel === false) { http_response_code(403); exit('Acesso negado'); }
if ($nivel !== 'Administrador') {
    $per = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = :chave LIMIT 1");
    $per->execute([':usuario'=>$usuario, ':empresa'=>$empresa, ':chave'=>$chave]);
    if (!$per->fetchColumn()) { http_response_code(403); exit('Sem permissão'); }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }


header('Content-Type: application/json; charset=utf-8');
if (!hash_equals((string)($_SESSION['bc_frete_csrf'] ?? ''), (string)($_POST['csrf'] ?? '')) || empty($_SESSION['bc_frete_csrf'])) { http_response_code(403); exit('Sessão inválida'); }
$acao = $_POST['acao'] ?? 'listar';
try {
if ($acao === 'listar') {
$q=$pdo->prepare("SELECT r.id,r.rua,r.valor,b.nome AS bairro FROM bc_frete_ruas r JOIN bairros b ON b.id=r.bairro_id AND b.empresa=r.empresa WHERE r.empresa=? ORDER BY b.nome,r.rua");
$q->execute([$empresa]); echo json_encode($q->fetchAll(PDO::FETCH_ASSOC)); exit;
}
if ($acao === 'excluir') {
$q=$pdo->prepare("DELETE FROM bc_frete_ruas WHERE id=? AND empresa=?"); $q->execute([(int)($_POST['id']??0),$empresa]); echo json_encode(['ok'=>true]); exit;
}
if ($acao !== 'salvar') { http_response_code(400); exit('Operação inválida'); }
$bairro=(int)($_POST['bairro_id']??0);
$rua=trim((string)($_POST['rua']??''));
$valor=str_replace(',','.',trim((string)($_POST['valor']??'')));
if ($rua==='' || mb_strlen($rua)>150 || !preg_match('/^\d+(?:\.\d{1,2})?$/',$valor) || (float)$valor>999999.99) { http_response_code(400); exit('Informe rua e valor válidos'); }
$q=$pdo->prepare("SELECT id FROM bairros WHERE id=? AND empresa=?"); $q->execute([$bairro,$empresa]); if (!$q->fetchColumn()) { http_response_code(400); exit('Bairro inválido'); }
$q=$pdo->prepare("INSERT INTO bc_frete_ruas (empresa,bairro_id,rua,valor) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)");
$q->execute([$empresa,$bairro,$rua,$valor]); echo json_encode(['ok'=>true]);
} catch (PDOException $e) { http_response_code(500); echo json_encode(['erro'=>'Não foi possível acessar as regras de frete.']); }
