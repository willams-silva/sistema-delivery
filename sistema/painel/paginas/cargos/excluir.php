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
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Cargo inválido'); }
$cargo = $pdo->prepare("SELECT nome FROM cargos WHERE id = :id AND empresa = :empresa LIMIT 1");
$cargo->execute([':id'=>$id, ':empresa'=>$empresa]);
$nome = $cargo->fetchColumn();
if ($nome === false) { http_response_code(404); exit('Cargo não encontrado'); }
$uso = $pdo->prepare("SELECT 1 FROM usuarios WHERE empresa = :empresa AND nivel = :nivel LIMIT 1");
$uso->execute([':empresa'=>$empresa, ':nivel'=>$nome]);
if ($uso->fetchColumn()) { http_response_code(409); exit('Não é possível excluir: cargo vinculado a funcionários'); }
$query = $pdo->prepare("DELETE FROM cargos WHERE id = :id AND empresa = :empresa");
$query->execute([':id'=>$id, ':empresa'=>$empresa]);
echo $query->rowCount() === 1 ? 'Excluído com Sucesso' : 'Cargo não encontrado';
?>