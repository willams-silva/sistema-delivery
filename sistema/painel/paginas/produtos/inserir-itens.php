<?php 
require_once("../../../conexao.php");

$tabela = 'itens_grade';

$texto = $_POST['texto'];
$produto = $_POST['id_item_produto'];
$valor = $_POST['valor'];
$limite = $_POST['limite'];
$grade = $_POST['id'];

$valor = str_replace(',', '.', $valor);


$query = $pdo->prepare("INSERT INTO $tabela SET produto = '$produto', grade = '$grade', valor = :valor, texto = :texto, limite = :limite, ativo = 'Sim'");


$query->bindValue(":valor", "$valor");
$query->bindValue(":texto", "$texto");
$query->bindValue(":limite", "$limite");
$query->execute();

echo 'Salvo com Sucesso';