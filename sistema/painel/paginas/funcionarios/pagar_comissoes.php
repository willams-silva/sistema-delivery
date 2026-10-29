<?php 
require_once("../../../conexao.php");
$tabela = 'pagar';

@session_start();
$id_usuario = $_SESSION['id'];

$valor = $_POST['total'];
$data = $_POST['data'];
$funcionario = $_POST['id_func'];

$data_atual = date('Y-m-d');

$pdo->query("DELETE FROM $tabela where funcionario = '$funcionario' and pago = 'Não' and referencia = 'Comissão'");

$pdo->query("INSERT INTO pagar SET descricao = 'Comissão', tipo = 'Comissão', valor = '$valor', data_lanc = '$data', data_venc = '$data', data_pgto = '$data', usuario_lanc = '$id_usuario', usuario_baixa = '$id_usuario', foto = 'sem-foto.jpg', pessoa = '0', pago = 'Sim', funcionario = '$funcionario', referencia = 'Comissão'");

echo 'Salvo com Sucesso';

?>