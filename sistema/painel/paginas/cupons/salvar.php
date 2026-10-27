<?php 
require_once("../../../conexao.php");
$tabela = 'cupons';

$id = $_POST['id'];
$codigo = $_POST['codigo'];
$valor = $_POST['valor'];

//validar email
$query = $pdo->query("SELECT * from $tabela where codigo = '$codigo'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Código já Cadastrado, escolha outro!!';
	exit();
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET codigo = :codigo, valor = :valor");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET codigo = :codigo, valor = :valor WHERE id = '$id'");
}

$query->bindValue(":codigo", "$codigo");
$query->bindValue(":valor", "$valor");
$query->execute();

echo 'Salvo com Sucesso';

?>