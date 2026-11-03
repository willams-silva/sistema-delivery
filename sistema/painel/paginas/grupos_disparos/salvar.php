<?php 
@session_start();
$id_empresa = @$_SESSION['empresa'];
require_once("../../../conexao.php");

$tabela = 'grupos_disparos';


$nome = $_POST['nome'];
$id = @$_POST['id'];

//validacao nome

$query = $pdo->query("SELECT * from $tabela where nome = '$nome' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_reg = @$res[0]['id'];
if(@count($res) > 0 and $id != $id_reg){
	echo 'Grupo já Cadastrada!';
	exit();
}



if($id == ""){
$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, ativo = 'Sim', empresa = '$id_empresa' ");
	
}else{
$query = $pdo->prepare("UPDATE $tabela SET nome = :nome where id = '$id'");
}
$query->bindValue(":nome", "$nome");
$query->execute();

echo 'Salvo com Sucesso';

 ?>