<?php 
require_once("../../../conexao.php");
$tabela = 'vendas';
@session_start();
$id_usuario = $_SESSION['id'];


$id = $_POST['id'];
$valor = $_POST['valor'];

$query = $pdo->query("SELECT * FROM vendas where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
  $valor_total = $res[0]['valor'];  
  $total_pago = @$res[0]['total_pago'];

  $novo_valor = $valor_total - $valor;

  if($total_pago > $valor_total){
		$troco = $total_pago - $novo_valor;		
	}else{
		$troco = 0;
		$total_pago = $novo_valor;
	}

}



$pdo->query("UPDATE $tabela SET cupom = '$valor', valor = '$novo_valor', total_pago = '$total_pago', troco = '$troco' where id = '$id'");

echo 'Baixado com Sucesso';
