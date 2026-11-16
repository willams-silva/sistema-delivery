<?php 
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, acesse novamente!';
	exit();
}
require_once("../../sistema/painel/buscar_config.php");

$ids = $_POST['ids'];
$variacao = $_POST['variacao'];
$obs = $_POST['obs'];
$borda = $_POST['borda'];
$mesa = $_POST['mesa'];

if(@$_SESSION['sessao_usuario'] == ""){
	$sessao = date('Y-m-d-H:i:s-').rand(0, 1500);
	$_SESSION['sessao_usuario'] = $sessao;
}else{
	$sessao = $_SESSION['sessao_usuario'];
}

$status = '';
if($mesa != ""){
    $status = 'Aguardando';
}

$ids = substr($ids, 0, -1);
$separar = explode("-", $ids);
$total_ids = @count($separar);

$quantidade_sabores = $total_ids;

$nome_produto = '';
$valor_produto = array();
$valor_produto_media = 0;
$primeiro_id_produto = $separar[0];
for($i=0; $i<$total_ids; $i++){
		$id = $separar[$i];

		$query = $pdo->query("SELECT * FROM produtos where id = '$id'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);		
		$nome_p = $res[0]['nome'];
		$categoria = $res[0]['categoria'];
		$preparado = $res[0]['preparado'];
		$valor_base_produto = (float) $res[0]['valor_venda'];
		$valor_promocional_produto = (float) $res[0]['val_promocional'];
		$promocao_produto = $res[0]['promocao'];


		if($preparado != 'Não'){
		    $status = 'Aguardando';
		}


		if($i>0){
			$nome_p = str_replace('Pizza', '', $nome_p);
			$nome_p = str_replace('pizza', '', $nome_p);
			$nome_p = str_replace('de', '', $nome_p);
		}
		
		$nome_produto .= $nome_p.' / ';

		$query = $pdo->query("SELECT * FROM variacoes_cat where id = '$variacao'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);			
		$sigla = $res[0]['sigla'];		
		


		$query = $pdo->query("SELECT * FROM variacoes where produto = '$id' and sigla = '$sigla'");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		if(@count($res) > 0){	
			$valor_p = (float) $res[0]['valor'];
			if ($promocao_produto == 'Sim' && $valor_promocional_produto > 0 && $valor_base_produto > 0) {
				$valor_p = $valor_p * ($valor_promocional_produto / $valor_base_produto);
			}
			$id_variacao = $res[0]['id'];			
		}else{
			$valor_p = 0;
			$id_variacao = "";		
		}

		$valor_produto_media += $valor_p;

		array_push($valor_produto, $valor_p);



}

$nome_produto = substr($nome_produto, 0, -2);	

$valor_produto_media = $valor_produto_media / $total_ids;

if($mais_sabores == 'Média'){
	$valor_produto_final = $valor_produto_media;
}else{
	$valor_produto_final = max($valor_produto);
}


// Identificar se a categoria usa adicionais individuais por sabor.
$query_categoria = $pdo->prepare("SELECT nome FROM categorias WHERE id = ? AND empresa = ? LIMIT 1");
$query_categoria->execute([(int)$categoria, $id_empresa]);
$dados_categoria = $query_categoria->fetch(PDO::FETCH_ASSOC);
$eh_tapiocas_doces = $dados_categoria && strtolower(trim($dados_categoria['nome'])) === 'tapiocas doces';

// Somar somente os adicionais vinculados aos sabores selecionados.
// Nas demais categorias, manter o comportamento antigo (id_sabor = 0).
$total_adicionais = 0;
if ($eh_tapiocas_doces) {
    $placeholders = implode(',', array_fill(0, count($separar), '?'));
    $sql_adc = "SELECT id_item, quantidade, valor_item FROM temp
                WHERE carrinho = '0' AND sessao = ? AND tabela = 'adicionais'
                AND empresa = ? AND id_sabor IN ($placeholders)";
    $params_adc = array_merge([$sessao, $id_empresa], array_map('intval', $separar));
} else {
    $sql_adc = "SELECT id_item, quantidade, valor_item FROM temp
                WHERE carrinho = '0' AND sessao = ? AND tabela = 'adicionais'
                AND empresa = ? AND id_sabor = 0";
    $params_adc = [$sessao, $id_empresa];
}
$stmt_adc = $pdo->prepare($sql_adc);
$stmt_adc->execute($params_adc);
$adicionais_selecionados = $stmt_adc->fetchAll(PDO::FETCH_ASSOC);
foreach ($adicionais_selecionados as $adc) {
    $valor_unitario_adc = (float)$adc['valor_item'];
    if ($valor_unitario_adc <= 0) {
        $stmt_valor = $pdo->prepare("SELECT valor FROM adicionais_cat WHERE id = ? LIMIT 1");
        $stmt_valor->execute([(int)$adc['id_item']]);
        $valor_unitario_adc = (float)$stmt_valor->fetchColumn();
    }
    $total_adicionais += $valor_unitario_adc * (int)$adc['quantidade'];
}
$valor_produto_final += $total_adicionais;

//pegar o valor da borda
$query =$pdo->query("SELECT * FROM bordas where id = '$borda'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
$valor_borda = $res[0]['valor'];
}else{
	$valor_borda = 0;
}
$valor_produto_final += $valor_borda;


$id_edicao = 0;
if (@$_SESSION['id_edicao'] != "") {
	$id_edicao = $_SESSION['id_edicao'];

	//adicionar o total item na tabela de vendas
	$query = $pdo->query("SELECT * FROM vendas where id = '$id_edicao'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$valor_venda = @$res[0]['valor'];

	$total_pago = @$res[0]['total_pago'];

	$total_venda = $valor_venda + ($valor_produto_final);

	if($total_pago > $valor_venda){
		$troco = $total_pago - $total_venda;		
	}else{
		$troco = 0;
		$total_pago = $total_venda;
	}

	
	$pdo->query("UPDATE vendas SET valor = '$total_venda', total_pago = '$total_pago', troco = '$troco' where id = '$id_edicao'");
}

$query = $pdo->prepare("INSERT INTO carrinho SET sessao = '$sessao', cliente = '0', produto = '$primeiro_id_produto', quantidade = '1', total_item = '$valor_produto_final', obs = :obs, pedido = '$id_edicao', id_sabor = '0', data = curDate(), variacao = '$id_variacao', mesa = '$mesa', nome_cliente = '', sabores = '$quantidade_sabores', nome_produto = '$nome_produto', borda = '$borda', categoria = '$categoria', status = '$status', hora = curTime(), empresa = '$id_empresa' ");
$query->bindValue(":obs", "$obs");
$query->execute();
$id_carrinho = $pdo->lastInsertId();
echo 'Inserido com Sucesso';


//limpar os ingredientes e adicionais
if ($eh_tapiocas_doces) {
    $placeholders_temp = implode(',', array_fill(0, count($separar), '?'));
    $stmt_temp = $pdo->prepare("UPDATE temp SET carrinho = ? WHERE sessao = ? AND carrinho = '0' AND empresa = ? AND (tabela != 'adicionais' OR id_sabor IN ($placeholders_temp))");
    $stmt_temp->execute(array_merge([$id_carrinho, $sessao, $id_empresa], array_map('intval', $separar)));
} else {
    $stmt_temp = $pdo->prepare("UPDATE temp SET carrinho = ? WHERE sessao = ? AND carrinho = '0' AND empresa = ?");
    $stmt_temp->execute([$id_carrinho, $sessao, $id_empresa]);
} 

