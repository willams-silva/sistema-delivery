<?php 
require_once("../../../conexao.php");
$tabela = 'vendas';

$id = $_POST['id'];
$entregador = $_POST['entregador'];

$pdo->query("UPDATE $tabela SET entregador = '$entregador' where id = '$id'");

echo 'Salvo com Sucesso';
 ?>