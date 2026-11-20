<?php
@session_start();
require_once("../../../conexao.php");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
$id = (int) ($_POST['id-arquivo'] ?? 0);
$nome = trim((string) ($_POST['nome-arq'] ?? ''));
if ($empresa <= 0 || $usuario <= 0 || $id <= 0 || $nome === '') {
    http_response_code(400);
    exit('Sessão ou dados inválidos');
}
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $usuario, ':empresa' => $empresa]);
$nivel = $operador->fetchColumn();
if ($nivel === false) {
    http_response_code(403);
    exit('Operador não autorizado');
}
if ($nivel !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'clientes' LIMIT 1");
    $permissao->execute([':usuario' => $usuario, ':empresa' => $empresa]);
    if (!$permissao->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão');
    }
}
$registro = $pdo->prepare("SELECT 1 FROM clientes WHERE id = :id AND empresa = :empresa LIMIT 1");
$registro->execute([':id' => $id, ':empresa' => $empresa]);
if (!$registro->fetchColumn()) {
    http_response_code(404);
    exit('Cadastro não encontrado');
}
$arquivo = $_FILES['arquivo_conta'] ?? null;
if (!$arquivo || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
    !is_uploaded_file($arquivo['tmp_name'] ?? '') ||
    ($arquivo['size'] ?? 0) <= 0 || $arquivo['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    exit('Arquivo inválido ou maior que 5 MB');
}
$extensoes = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
$ext = strtolower(pathinfo((string) ($arquivo['name'] ?? ''), PATHINFO_EXTENSION));
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
if (!isset($extensoes[$ext]) || $extensoes[$ext] !== $mime) {
    http_response_code(400);
    exit('Formato não permitido. Use PDF, JPG ou PNG');
}
$nome_arquivo = bin2hex(random_bytes(16)) . '.' . $ext;
$destino = __DIR__ . '/../../images/arquivos/' . $nome_arquivo;
if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
    http_response_code(500);
    exit('Falha ao salvar arquivo');
}
try {
    $query = $pdo->prepare("INSERT INTO arquivos SET nome = :nome, data_cad = CURDATE(), usuario = :usuario, arquivo = :arquivo, registro = :registro, id_reg = :id");
    $query->execute([':nome' => $nome, ':usuario' => $usuario, ':arquivo' => $nome_arquivo, ':registro' => 'Cliente', ':id' => $id]);
} catch (Throwable $e) {
    @unlink($destino);
    http_response_code(500);
    exit('Falha ao registrar arquivo');
}
echo 'Inserido com Sucesso';
?>