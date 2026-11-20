<?php
@session_start();
require_once("../../../conexao.php");
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$q = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$q->execute([':id'=>$usuario, ':empresa'=>$empresa]);
if ($q->fetchColumn() !== 'Administrador') { http_response_code(403); exit('Somente administradores podem alterar permissões'); }
$q = $pdo->prepare("SELECT alterar_acessos FROM config WHERE empresa = :empresa LIMIT 1");
$q->execute([':empresa'=>$empresa]);
if ($q->fetchColumn() !== 'Sim') { http_response_code(403); exit('Alteração de acessos desabilitada para esta empresa'); }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }

$id = (int) ($_POST['id'] ?? 0);
$nome = trim((string) ($_POST['nome'] ?? ''));
if ($nome === '' || mb_strlen($nome, 'UTF-8') > 100 || $id < 0) { http_response_code(400); exit('Nome do grupo inválido'); }
$q = $pdo->prepare("SELECT id FROM grupo_acessos WHERE nome = :nome AND id <> :id LIMIT 1");
$q->execute([':nome'=>$nome, ':id'=>$id]);
if ($q->fetchColumn()) { http_response_code(409); exit('Nome já cadastrado!'); }
if ($id > 0) {
    $verificar = $pdo->prepare('SELECT 1 FROM grupo_acessos WHERE id = :id LIMIT 1');
    $verificar->execute([':id'=>$id]);
    if (!$verificar->fetchColumn()) { http_response_code(404); exit('Grupo não encontrado'); }
    $q = $pdo->prepare("UPDATE grupo_acessos SET nome = :nome WHERE id = :id");
    $q->execute([':nome'=>$nome, ':id'=>$id]);
} else {
    $q = $pdo->prepare("INSERT INTO grupo_acessos (nome) VALUES (:nome)");
    $q->execute([':nome'=>$nome]);
}
echo 'Salvo com Sucesso';