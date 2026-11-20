<?php
@session_start();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit('Método não permitido');
}
$tabela = 'usuarios';
require_once("../../../conexao.php");

if($modo_teste == 'Sim'){
	echo 'Esse recurso está desativado no modo teste!';
	exit();
}


$id = (int) ($_POST['id'] ?? 0);
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_operador = (int) ($_SESSION['id'] ?? 0);
if ($id <= 0 || $id_empresa <= 0 || $id_operador <= 0 || $id === $id_operador) {
    http_response_code(400);
    exit('Dados inválidos');
}

$query = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id AND empresa = :empresa");
$query->execute([':id' => $id, ':empresa' => $id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if (!$res) {
    http_response_code(404);
    exit('Usuário não encontrado');
}
$foto = $res[0]['foto'] ?? '';

if ($foto !== '' && $foto !== 'sem-foto.jpg' && basename($foto) === $foto) {
	@unlink('../../images/perfil/'.$foto);
}

$delete = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND empresa = :empresa");
$delete->execute([':id' => $id, ':empresa' => $id_empresa]);
echo 'Excluído com Sucesso';
?>