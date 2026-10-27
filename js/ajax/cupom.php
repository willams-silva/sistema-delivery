<?php 
require_once('../../sistema/conexao.php');

$total_final = $_POST['total_final'];
$codigo_cupom = $_POST['codigo_cupom'];

$query =$pdo->query("SELECT * FROM cupons where codigo = '$codigo_cupom'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_cupons = @count($res);
if($total_cupons == 0){
	echo '0';
	exit();
}else{
	$valor_cupom = $res[0]['valor'];
	$valor_total = $total_final - $valor_cupom;

	$valor_totalF = number_format($valor_total, 2, ',', '.');
	echo $valor_totalF.'**'.$valor_cupom;

	//deletar o cupom
	$pdo->query("DELETE FROM cupons where codigo = '$codigo_cupom'");
}

?>