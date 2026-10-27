<?php 
@session_start();
require_once('../../sistema/conexao.php');

$id = $_POST['id'];
$acao = $_POST['acao'];
$sessao = @$_SESSION['sessao_usuario'];

$query =$pdo->query("SELECT * FROM guarnicoes where id = '$id' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_produto = $res[0]['produto'];

$query =$pdo->query("SELECT * FROM produtos where id = '$id_produto'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$guarnicoes = $res[0]['guarnicoes'];

$query =$pdo->query("SELECT * FROM temp where sessao = '$sessao' and tabela = 'guarnicoes' and carrinho = '0'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_guarnicoes = @count($res);


if($acao != 'Não'){
	  if($total_guarnicoes >=  $guarnicoes){
	   	echo 'Você não pode escolher mais de '.$guarnicoes.' Guarnições!';
	   	exit();
	}

  $pdo->query("INSERT INTO temp SET sessao = '$sessao', tabela = 'guarnicoes', id_item = '$id', carrinho = '0', data = curDate()"); 
}else{
    $pdo->query("DELETE FROM temp WHERE id_item = '$id' and sessao = '$sessao' and tabela = 'guarnicoes' and carrinho = '0'"); 
}

echo 'Alterado com Sucesso';

 ?>