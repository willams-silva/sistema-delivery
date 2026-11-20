<?php
@session_start();
require_once("../../../conexao.php");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}
$id = (int) ($_POST['id'] ?? 0);
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_operador = (int) ($_SESSION['id'] ?? 0);
if ($id <= 0 || $id_empresa <= 0 || $id_operador <= 0 || $id === $id_operador) {
    http_response_code(400);
    exit('Dados inválidos');
}
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $id_operador, ':empresa' => $id_empresa]);
$nivel_operador = $operador->fetchColumn();
if ($nivel_operador === false) {
    http_response_code(403);
    exit('Operador sem acesso a esta empresa');
}
if ($nivel_operador !== 'Administrador') {
    $acesso = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'funcionarios' LIMIT 1");
    $acesso->execute([':usuario' => $id_operador, ':empresa' => $id_empresa]);
    if (!$acesso->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão para gerenciar funcionários');
    }
}
$consulta = $pdo->prepare("SELECT foto, nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$consulta->execute([':id' => $id, ':empresa' => $id_empresa]);
$usuario = $consulta->fetch(PDO::FETCH_ASSOC);
if (!$usuario) {
    http_response_code(404);
    exit('Funcionário não encontrado');
}
if ($usuario['nivel'] === 'Administrador' && $nivel_operador !== 'Administrador') {
    http_response_code(403);
    exit('Somente administradores podem excluir administradores');
}
$excluir = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND empresa = :empresa");
$excluir->execute([':id' => $id, ':empresa' => $id_empresa]);
if ($excluir->rowCount() !== 1) {
    http_response_code(409);
    exit('Não foi possível excluir o funcionário');
}
$foto = (string) ($usuario['foto'] ?? '');
if ($foto !== '' && $foto !== 'sem-foto.jpg' && basename($foto) === $foto) {
    @unlink('../../images/perfil/' . $foto);
}
echo 'Excluído com Sucesso';
?>