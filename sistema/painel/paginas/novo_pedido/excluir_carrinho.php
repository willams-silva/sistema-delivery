<?php 
require_once("../../../conexao.php");
$tabela = 'carrinho';

$id = $_POST['id'];

$pdo->query("DELETE from $tabela where id = '$id'");
$pdo->query("DELETE from temp where carrinho = '$id'");
echo 'Excluído com Sucesso';

?>