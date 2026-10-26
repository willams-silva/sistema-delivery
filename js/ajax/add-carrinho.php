<?php 
@session_start();
require_once('../../sistema/conexao.php');

$produto = $_POST['produto'];
$telefone = $_POST['telefone'];
$nome = $_POST['nome'];
$quantidade = $_POST['quantidade'];
$total_item = $_POST['total_item'];
$obs = $_POST['obs'];
$sessao = @$_SESSION['sessao_usuario'];



$query = $pdo->query("SELECT * FROM clientes where telefone = '$telefone' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){	
	$id_cliente = $res[0]['id'];	
}else{
	$query = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, data = curDate()");
	$query->bindValue(":nome", "$nome");
$query->bindValue(":telefone", "$telefone");
$query->execute();
$id_cliente = $pdo->lastInsertId();
}


$query = $pdo->prepare("INSERT INTO carrinho SET sessao = '$sessao', cliente = '$id_cliente', produto = '$produto', quantidade = '$quantidade', total_item = '$total_item', obs = :obs, pedido = '0'"); 
$query->bindValue(":obs", "$obs");
$query->execute();
$id_carrinho = $pdo->lastInsertId();
echo 'Inserido com Sucesso';


//limpar os ingredientes e adicionais
$pdo->query("UPDATE temp SET carrinho = '$id_carrinho' where sessao = '$sessao' and carrinho = '0'"); 

 ?>