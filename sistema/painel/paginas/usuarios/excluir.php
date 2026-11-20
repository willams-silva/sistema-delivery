<?php
@session_start();
$tabela = 'usuarios';
require_once("../../../conexao.php");

if($modo_teste == 'Sim'){
	echo 'Esse recurso está desativado no modo teste!';
	exit();
}


$id = (int) ($_POST['id'] ?? 0);
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
if ($id <= 0 || $id_empresa <= 0) {
    http_response_code(400);
    exit('Dados inválidos');
}

$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$foto = @$res[0]['foto'];

if($foto != "sem-foto.jpg"){
	@unlink('../../images/perfil/'.$foto);
}

$pdo->query("DELETE FROM $tabela WHERE id = '$id' AND empresa = '$id_empresa' ");
echo 'Excluído com Sucesso';
?>