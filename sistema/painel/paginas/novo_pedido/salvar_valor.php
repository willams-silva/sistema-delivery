<?php 
@session_start();
$id_usuario = $_SESSION['id'];
require_once("../../../conexao.php");
$tabela = 'valor_adiantamento';


$id = $_POST['id'];
$nome = $_POST['nome'];
$valor = $_POST['valor'];
$valor = str_replace(',', '.', $valor);


$query = $pdo->prepare("INSERT INTO $tabela SET abertura = '$id', valor = :valor, nome = :nome");
$query->bindValue(":nome", "$nome");
$query->bindValue(":valor", "$valor");
$query->execute();
$ultimo_id = $pdo->lastInsertId();

//lançar nas contas receber
$query = $pdo->prepare("INSERT INTO receber SET descricao = 'Adiantamento Mesa', tipo = 'Abertura', valor = :valor, data_lanc = curDate(), data_venc = curDate(), data_pgto = curDate(), usuario_lanc = '$id_usuario', usuario_baixa = '$id_usuario', foto = 'sem-foto.jpg', pessoa = '0', pago = 'Sim', referencia = 'Abertura', id_ref = '$id', adiantamento = '$ultimo_id'");
$query->bindValue(":valor", "$valor");
$query->execute();


echo 'Salvo com Sucesso';

?>