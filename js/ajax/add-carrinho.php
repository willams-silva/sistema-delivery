<?php 
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, acesse novamente!';
	exit();
}
require_once("../../sistema/painel/buscar_config.php");

$produto = $_POST['produto'];
//$telefone = $_POST['telefone'];
//$nome = $_POST['nome'];
$quantidade = filter_var(@$_POST['quantidade'], @FILTER_SANITIZE_STRING);
$total_item = filter_var(@$_POST['total_item'], @FILTER_SANITIZE_STRING);
$obs = filter_var(@$_POST['obs'], @FILTER_SANITIZE_STRING);
$sessao = @$_SESSION['sessao_usuario'];
$valor_produto = @$_POST['valor_produto'];
$mesa = $_POST['mesa'];

$query = $pdo->query("SELECT * FROM produtos where id = '$produto'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_categoria = $res[0]['categoria'];
$estoque = $res[0]['estoque'];
$tem_estoque = $res[0]['tem_estoque'];
$valor_venda = $res[0]['valor_venda'];
$preparado = $res[0]['preparado'];

$status = '';
if($preparado != 'Não'){
    $status = 'Aguardando';
}

if($valor_produto == ""){
	$valor_produto = $valor_venda;
}

//ver se possui a quantidade de produtos comprados
if($quantidade > $estoque and $tem_estoque == 'Sim'){
	echo 'Quantidade em Estoque insuficiente, possui apenas '.$estoque.' Itens';
	exit();
}


$id_edicao = 0;
if (@$_SESSION['id_edicao'] != "") {
	$id_edicao = $_SESSION['id_edicao'];

	//adicionar o total item na tabela de vendas
	$query = $pdo->query("SELECT * FROM vendas where id = '$id_edicao'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$valor_venda = @$res[0]['valor'];
	$total_pago = @$res[0]['total_pago'];

	$total_venda = $valor_venda + ($total_item * $quantidade);

	if($total_pago > $valor_venda){
		$troco = $total_pago - $total_venda;		
	}else{
		$troco = 0;
		$total_pago = $total_venda;
	}

	
	$pdo->query("UPDATE vendas SET valor = '$total_venda', total_pago = '$total_pago', troco = '$troco' where id = '$id_edicao'");
}

if($mesa > 0){
	$total_item = $total_item * $quantidade;
}

$query = $pdo->prepare("INSERT INTO carrinho SET sessao = '$sessao', cliente = '0', produto = '$produto', quantidade = '$quantidade', total_item = '$total_item', obs = :obs, pedido = '$id_edicao', data = curDate(), mesa = '$mesa', nome_cliente = '', valor_unitario = '$valor_produto', status = '$status', hora = curTime(), empresa = '$id_empresa' ");
$query->bindValue(":obs", "$obs");
$query->execute();
$id_carrinho = $pdo->lastInsertId();
echo 'Inserido com Sucesso';


//limpar os ingredientes e adicionais
$pdo->query("UPDATE temp SET carrinho = '$id_carrinho' where sessao = '$sessao' and carrinho = '0'"); 

