<?php
@session_start();
require_once("../../../conexao.php");
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id'=>$usuario, ':empresa'=>$empresa]);
$nivel = $operador->fetchColumn();
if ($nivel === false) { http_response_code(403); exit('Operador não autorizado'); }
if ($nivel !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'cargos' LIMIT 1");
    $permissao->execute([':usuario'=>$usuario, ':empresa'=>$empresa]);
    if (!$permissao->fetchColumn()) { http_response_code(403); exit('Sem permissão para gerenciar cargos'); }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }
$nome = trim((string) ($_POST['nome'] ?? ''));
$id = (int) ($_POST['id'] ?? 0);
if ($nome === '' || mb_strlen($nome, 'UTF-8') > 100 || $id < 0) { http_response_code(400); exit('Informe um cargo válido (até 100 caracteres)'); }
$duplicado = $pdo->prepare("SELECT id FROM cargos WHERE nome = :nome AND empresa = :empresa AND id <> :id LIMIT 1");
$duplicado->execute([':nome'=>$nome, ':empresa'=>$empresa, ':id'=>$id]);
if ($duplicado->fetchColumn()) { exit('Nome já Cadastrado!'); }
if ($id > 0) {
    $atual = $pdo->prepare("SELECT nome FROM cargos WHERE id = :id AND empresa = :empresa LIMIT 1");
    $atual->execute([':id'=>$id, ':empresa'=>$empresa]);
    $nome_anterior = $atual->fetchColumn();
    if ($nome_anterior === false) { http_response_code(404); exit('Cargo não encontrado'); }
    // Funcionários guardam o nome do cargo em usuarios.nivel.
    if ($nome_anterior !== $nome) {
        $uso = $pdo->prepare("SELECT 1 FROM usuarios WHERE empresa = :empresa AND nivel = :nivel LIMIT 1");
        $uso->execute([':empresa'=>$empresa, ':nivel'=>$nome_anterior]);
        if ($uso->fetchColumn()) { http_response_code(409); exit('Cargo em uso: altere primeiro os funcionários vinculados'); }
    }
    $query = $pdo->prepare("UPDATE cargos SET nome = :nome WHERE id = :id AND empresa = :empresa");
    $query->execute([':nome'=>$nome, ':id'=>$id, ':empresa'=>$empresa]);
} else {
    $query = $pdo->prepare("INSERT INTO cargos (nome, empresa) VALUES (:nome, :empresa)");
    $query->execute([':nome'=>$nome, ':empresa'=>$empresa]);
}
echo 'Salvo com Sucesso';
?>