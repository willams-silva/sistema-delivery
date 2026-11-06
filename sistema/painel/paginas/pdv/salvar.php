<?php 
@session_start();
$id_usuario = $_SESSION['id'];
$tabela = 'receber';
require_once("../../../conexao.php");

$sessao = $id_usuario;

$data_atual = date('Y-m-d');
$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual."-".$mes_atual."-01";
$data_inicio_ano = $ano_atual."-01-01";

$data_ontem = date('Y-m-d', strtotime("-1 days",strtotime($data_atual)));
$data_amanha = date('Y-m-d', strtotime("+1 days",strtotime($data_atual)));

$total_compra = 0;


if($mes_atual == '04' || $mes_atual == '06' || $mes_atual == '09' || $mes_atual == '11'){
	$data_final_mes = $ano_atual.'-'.$mes_atual.'-30';
}else if($mes_atual == '02'){
	$bissexto = date('L', @mktime(0, 0, 0, 1, 1, $ano_atual));
	if($bissexto == 1){
		$data_final_mes = $ano_atual.'-'.$mes_atual.'-29';
	}else{
		$data_final_mes = $ano_atual.'-'.$mes_atual.'-28';
	}

}else{
	$data_final_mes = $ano_atual.'-'.$mes_atual.'-31';
}



$troco = $_POST['troco'];
$troco = @str_replace(',', '.', $troco);
$desconto = $_POST['desconto'];
$desconto = @str_replace(',', '.', $desconto);
$cliente = $_POST['cliente'];
$saida = $_POST['saida'];
$data = $_POST['data2'];
$frete = $_POST['frete'];
$mesa = @$_POST['mesa'];
$entrega = @$_POST['entrega'];

if($entrega != "" and $cliente == ""){
	echo 'Para qualquer opcao do delivery precisa selecionar um cliente!';
	exit();
}

$tipo_pedido = '';
if($mesa == "" and $entrega == ""){
	$tipo_pedido = 'Balcão';
}


$tipo_desconto = $_POST['tipo_desconto'];
$subtotal_venda = $_POST['subtotal_venda'];
$valor_restante = $_POST['valor_restante'];
$valor_pago = $_POST['valor_pago'];

$data_restante = $_POST['data_restante'];
$forma_pgto2 = $_POST['forma_pgto2'];

if($valor_restante > 0 and $forma_pgto2 == "" and $data_restante == $data_atual){
	echo 'Você precisa selecionar uma forma de pagamento para o valor restante!';
	exit();
}

$dataF = implode('/', array_reverse(explode('-', $data)));

if($desconto == ""){
	$desconto = 0;
}

if($frete == ""){
	$frete = 0;
}

if($cliente == ""){
	$cliente = 0;
}

$total_cartoes_cliente = 0;
$query = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = @$res[0]['nome'];
$total_cartoes_cliente = @$res[0]['cartoes'];
if(@count($res) > 0){
    if($total_cartoes_cliente == ""){
      $total_cartoes_cliente = 0;
    }
    if($total_cartoes_cliente < $total_cartoes_config){
      $total_cartoes_cliente = $total_cartoes_cliente + 1;
    } 

    if($total_cartoes_config == 0){
       $total_cartoes_cliente = 0;
    }

$pdo->query("UPDATE clientes SET  cartoes = '$total_cartoes_cliente' where id = '$cliente'");

}


$query = $pdo->query("SELECT * FROM formas_pgto where nome = '$saida'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_saida = @$res[0]['id'];


$total_carrinho = 0;
$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao' and pedido = 0 and mesa = ''");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
  for ($i = 0; $i < $total_reg; $i++) {
    foreach ($res[$i] as $key => $value) {
    }

    $id = $res[$i]['id'];
    $total_item = $res[$i]['total_item'];
    $produto = $res[$i]['produto'];
    $quantidade = $res[$i]['quantidade'];    

    $total_carrinho += $total_item;


    $query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    $id_categoria = $res2[0]['categoria'];
    $valor_produto = $res2[0]['valor_venda'];
    $estoque = $res2[0]['estoque'];
    $tem_estoque = $res2[0]['tem_estoque'];
    $valor_compra = $res2[0]['valor_compra'];
    $valor_compra = $quantidade * $valor_compra;
    $total_compra += $valor_compra;


    if ($tem_estoque == 'Sim') {
      $total_produtos = $estoque - $quantidade;
      $pdo->query("UPDATE produtos SET estoque = '$total_produtos' where id = '$produto'");
    }

  }
} else {
  echo '0';
  exit();
}




if($tipo_desconto == '%'){
	if($desconto > 0 and $total_carrinho > 0){
		$total_final = -($total_carrinho * $desconto / 100);
	}else{
		$total_final = 0;
	}
	
}else{
	$total_final = -$desconto;
}


$total_final = $total_carrinho + $frete - $desconto;

if($total_final <= 0){
	echo 'O valor da Venda tem que ser maior que zero';
	exit();
}


if($troco < $total_final and $troco != ""){
	echo 'O total a pagar não pode ser menor que o total da venda!';
	exit();
}

if($troco == ""){
	$troco = 0;
}



if(strtotime($data) > strtotime($data_atual)){
	$pago = 'Não';
	$data_pgto = '';
	$usuario_pgto = '';
}else{
	$pago = 'Sim';
	$data_pgto = $data;
	$usuario_pgto = $id_usuario;
}


//recuperar número do pedido
$query = $pdo->query("SELECT * FROM vendas where data = curDate() order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$num_pedido = @$res[0]['pedido'];
if ($num_pedido == "") {
  $num_pedido = 0;
}
$pedido = $num_pedido + 1;

$troco_calculado = 0;
if($troco > 0){
	$troco_calculado = $troco - $total_final;
}else{
	$troco = $total_final;
}

//verificar caixa aberto
$query1 = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null order by id desc limit 1");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if(@count($res1) > 0){
	$id_caixa = @$res1[0]['id'];
}else{
	$id_caixa = 0;
}


if($entrega == ""){
	$entrega = 'Consumir Local';
}


$query = $pdo->prepare("INSERT INTO vendas SET cliente = '$cliente', valor = '$total_final', total_pago = '$troco', troco = '$troco_calculado', data = curDate(), hora = curTime(), status = 'Iniciado', pago = '$pago', obs = '', taxa_entrega = '$frete', tipo_pgto = '$saida', usuario_baixa = '0', entrega = '$entrega', mesa = '$mesa', nome_cliente = '$nome_cliente', cupom = '$desconto', pago_entregador = 'Não', pedido = '$pedido', ref_api = '', tipo_pedido = '$tipo_pedido', taxa_extra_pedido = '0', caixa = '$id_caixa', custo = '$total_compra'");
$query->execute();
$id_pedido = $pdo->lastInsertId();
$id_pedido_feito = $pdo->lastInsertId();


//lançar nas contas a receber
$pdo->query("INSERT INTO receber SET descricao = 'Nova Venda', cliente = '$cliente', valor = '$total_final', vencimento = '$data', data_pgto = '$data', data_lanc = curDate(), forma_pgto = '$id_saida', frequencia = '0',  arquivo = 'sem-foto.jpg', foto = 'sem-foto.jpg', subtotal = '$total_final', usuario_lanc = '$id_usuario', usuario_pgto = '$usuario_pgto', pago = '$pago', referencia = 'Venda', caixa = '$id_caixa', hora = curTime(), tipo = 'Balcão', custo = '$total_compra' ");


//relacionar itens do carrinho com o pedido
$pdo->query("UPDATE carrinho SET cliente = '$cliente', pedido = '$id_pedido' where sessao = '$id_usuario' and pedido = '0' and mesa = ''");
$pdo->query("UPDATE temp SET pedido = '$id_pedido' where sessao = '$id_usuario' and pedido = '0'");



echo 'Salvo com Sucesso-'.$id_pedido;

 ?>