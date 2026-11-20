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

$id = (int) ($_POST['id'] ?? 0);
$nome = trim((string) ($_POST['nome'] ?? ''));
$valor = trim((string) ($_POST['valor'] ?? ''));
if ($nome === '' || mb_strlen($nome, 'UTF-8') > 150 || $id < 0) { http_response_code(400); exit('Informe uma localidade válida'); }
$valor = str_replace(' ', '', $valor);
if (strpos($valor, ',') !== false) { $valor = str_replace('.', '', $valor); $valor = str_replace(',', '.', $valor); }
if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valor) || (float)$valor > 999999.99) { http_response_code(400); exit('Informe uma taxa de entrega válida'); }
$dup = $pdo->prepare("SELECT id FROM bairros WHERE empresa = :empresa AND nome = :nome AND id <> :id LIMIT 1");
$dup->execute([':empresa'=>$empresa, ':nome'=>$nome, ':id'=>$id]);
if ($dup->fetchColumn()) { exit('Localidade já Cadastrada, escolha outra!!'); }
if ($id > 0) {
    $q = $pdo->prepare("UPDATE bairros SET nome = :nome, valor = :valor WHERE id = :id AND empresa = :empresa");
    $q->execute([':nome'=>$nome, ':valor'=>$valor, ':id'=>$id, ':empresa'=>$empresa]);
    if (!$q->rowCount()) {
        $check = $pdo->prepare("SELECT 1 FROM bairros WHERE id = :id AND empresa = :empresa");
        $check->execute([':id'=>$id, ':empresa'=>$empresa]);
        if (!$check->fetchColumn()) { http_response_code(404); exit('Localidade não encontrada'); }
    }
} else {
    $q = $pdo->prepare("INSERT INTO bairros (nome, valor, empresa) VALUES (:nome, :valor, :empresa)");
    $q->execute([':nome'=>$nome, ':valor'=>$valor, ':empresa'=>$empresa]);
}
echo 'Salvo com Sucesso';