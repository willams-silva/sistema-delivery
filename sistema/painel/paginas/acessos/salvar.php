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
$chave = trim((string) ($_POST['chave'] ?? ''));
$grupo = (int) ($_POST['grupo'] ?? 0);
$pagina = (string) ($_POST['pagina'] ?? '');
if ($nome === '' || mb_strlen($nome, 'UTF-8') > 100 || !preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $chave) || $grupo < 0 || !in_array($pagina, ['Sim','Não'], true)) { http_response_code(400); exit('Dados de acesso inválidos'); }
if ($grupo > 0) {
    $q = $pdo->prepare("SELECT 1 FROM grupo_acessos WHERE id = :id LIMIT 1");
    $q->execute([':id'=>$grupo]);
    if (!$q->fetchColumn()) { http_response_code(400); exit('Grupo inválido'); }
}
$q = $pdo->prepare("SELECT id FROM acessos WHERE (nome = :nome OR chave = :chave) AND id <> :id LIMIT 1");
$q->execute([':nome'=>$nome, ':chave'=>$chave, ':id'=>$id]);
if ($q->fetchColumn()) { http_response_code(409); exit('Nome ou chave já cadastrado!'); }
if ($id > 0) {
    $q = $pdo->prepare("SELECT chave FROM acessos WHERE id = :id LIMIT 1");
    $q->execute([':id'=>$id]);
    $chave_antiga = $q->fetchColumn();
    if ($chave_antiga === false) { http_response_code(404); exit('Acesso não encontrado'); }
    if ($chave_antiga !== $chave) { http_response_code(409); exit('A chave de um acesso existente não pode ser alterada; ela é usada nas permissões do sistema'); }
    $q = $pdo->prepare("UPDATE acessos SET nome = :nome, grupo = :grupo, pagina = :pagina WHERE id = :id");
    $q->execute([':nome'=>$nome, ':grupo'=>$grupo, ':pagina'=>$pagina, ':id'=>$id]);
} else {
    $q = $pdo->prepare("INSERT INTO acessos (nome, chave, grupo, pagina) VALUES (:nome, :chave, :grupo, :pagina)");
    $q->execute([':nome'=>$nome, ':chave'=>$chave, ':grupo'=>$grupo, ':pagina'=>$pagina]);
}
echo 'Salvo com Sucesso';