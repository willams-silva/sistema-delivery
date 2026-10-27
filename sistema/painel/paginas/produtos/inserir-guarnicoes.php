<?php 
require_once("../../../conexao.php");
$tabela = 'guarnicoes';

$nome = $_POST['nome'];
$produto = $_POST['id'];


$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, produto = '$produto', ativo = 'Sim'");


$query->bindValue(":nome", "$nome");
$query->execute();

echo 'Salvo com Sucesso';