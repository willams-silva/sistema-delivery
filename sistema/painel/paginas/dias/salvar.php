<?php
$chave = 'dias';
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
$dia = trim((string) ($_POST['dia'] ?? ''));
$permitidos = ['Segunda-Feira','Terça-Feira','Quarta-Feira','Quinta-Feira','Sexta-Feira','Sábado','Domingo'];
if (!in_array($dia, $permitidos, true) || $id < 0) { http_response_code(400); exit('Dia inválido'); }
$dup = $pdo->prepare("SELECT id FROM dias WHERE empresa = :empresa AND dia = :dia AND id <> :id LIMIT 1");
$dup->execute([':empresa'=>$empresa, ':dia'=>$dia, ':id'=>$id]);
if ($dup->fetchColumn()) { exit('Dia já Cadastrado, escolha outro!!'); }
if ($id > 0) {
    $q = $pdo->prepare("UPDATE dias SET dia = :dia WHERE id = :id AND empresa = :empresa");
    $q->execute([':dia'=>$dia, ':id'=>$id, ':empresa'=>$empresa]);
    if (!$q->rowCount()) {
        $check = $pdo->prepare("SELECT 1 FROM dias WHERE id = :id AND empresa = :empresa");
        $check->execute([':id'=>$id, ':empresa'=>$empresa]);
        if (!$check->fetchColumn()) { http_response_code(404); exit('Dia não encontrado'); }
    }
} else {
    $q = $pdo->prepare("INSERT INTO dias (dia, empresa) VALUES (:dia, :empresa)");
    $q->execute([':dia'=>$dia, ':empresa'=>$empresa]);
}
echo 'Salvo com Sucesso';