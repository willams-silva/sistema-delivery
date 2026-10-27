<?php 
require_once('../../sistema/conexao.php');


$data_atual = date('Y-m-d');

$total_final = $_POST['total_final'];
$codigo_cupom = $_POST['codigo_cupom'];


$query =$pdo->query("SELECT * FROM cupons where codigo = '$codigo_cupom'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_cupons = @count($res);
if($total_cupons == 0 ){
	echo '0';
	exit();
}else{
	$valor_cupom = $res[0]['valor'];
	$data = $res[0]['data'];
	$quantidade = $res[0]['quantidade'];
	$valor_minimo = $res[0]['valor_minimo'];

	if($data != "" and $data != "0000-00-00"){
		if(strtotime($data) < strtotime($data_atual)){
			echo '1';
			exit();
		}
	}

	if($quantidade < 1){		
			echo '2';
			exit();		
	}

	if($valor_minimo > 0){
		if($total_final <= $valor_minimo){
			echo '3**'.$valor_minimo;
			exit();	
		}
	}

	$valor_total = $total_final - $valor_cupom;

	$valor_totalF = number_format($valor_total, 2, ',', '.');
	echo $valor_totalF.'**'.$valor_cupom;

	//deletar o cupom
	//$pdo->query("DELETE FROM cupons where codigo = '$codigo_cupom'");

	//abater 1 na quantidade de cupom
	$nova_quant = $quantidade - 1;
	$pdo->query("UPDATE cupons SET quantidade = '$nova_quant' where codigo = '$codigo_cupom'");
}

?>