<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$id_usuario = $_SESSION['id'];
$tabela = 'receber';


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

if($entrega != "" and ($cliente == "" || $cliente == "0")){
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

// Valores informados no navegador são revalidados contra o total calculado no servidor.
$valorPagoInformado = str_replace(',', '.', trim((string)$valor_pago));
$restanteInformado = str_replace(',', '.', trim((string)$valor_restante));
$parcialSolicitado = is_numeric($restanteInformado) && (float)$restanteInformado > 0.009;
if ($parcialSolicitado && (!is_numeric($valorPagoInformado) || (float)$valorPagoInformado <= 0)) {
    http_response_code(409); exit('Informe um valor válido para o primeiro pagamento.');
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

if ($cliente !== '' && $cliente !== '0') {
    $validarCliente = $pdo->prepare('SELECT id FROM clientes WHERE id = :id AND empresa = :empresa LIMIT 1');
    $validarCliente->execute([':id' => $cliente, ':empresa' => $id_empresa]);
    if (!$validarCliente->fetch(PDO::FETCH_ASSOC)) {
        echo 'Cliente não encontrado nesta empresa!';
        exit();
    }
}
// A fidelidade é atualizada somente depois da confirmação transacional da venda.
$nome_cliente = '';
$cliente_fidelidade = (int)$cliente;
$formaSt=$pdo->prepare('SELECT id FROM formas_pgto WHERE nome=:nome AND empresa=:empresa LIMIT 1');
$formaSt->execute([':nome'=>$saida,':empresa'=>(int)$id_empresa]);
$id_saida=(int)($formaSt->fetchColumn() ?: 0);

// Valida o caixa e a forma de pagamento antes de alterar estoque ou criar vendas.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método inválido.'); }
if (!ctype_digit((string)$id_empresa) || (int)$id_empresa < 1 || !ctype_digit((string)$id_usuario) || (int)$id_usuario < 1) {
    http_response_code(403); exit('Sessão inválida.');
}
$consultaCaixa=$pdo->prepare('SELECT id FROM caixas WHERE operador=:usuario AND empresa=:empresa AND data_fechamento IS NULL ORDER BY id DESC LIMIT 1');
$consultaCaixa->execute([':usuario'=>(int)$id_usuario,':empresa'=>(int)$id_empresa]);
$id_caixa=(int)($consultaCaixa->fetchColumn() ?: 0);
if (!$id_caixa) { http_response_code(409); exit('Abra um caixa antes de finalizar a venda no PDV.'); }
if (!$id_saida) { http_response_code(400); exit('Forma de pagamento não cadastrada para esta empresa.'); }

// Operação atômica: estoque, venda, financeiro e carrinho.
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
$pdo->beginTransaction();
$lockPedidoNome=null;
$lockCaixa=$pdo->prepare('SELECT id FROM caixas WHERE id=:id AND empresa=:empresa AND operador=:operador AND data_fechamento IS NULL FOR UPDATE');
$lockCaixa->execute([':id'=>$id_caixa,':empresa'=>(int)$id_empresa,':operador'=>(int)$id_usuario]);
if (!$lockCaixa->fetchColumn()) throw new RuntimeException('O caixa foi fechado. Abra um caixa para continuar.');
if ($cliente_fidelidade > 0) {
    $clienteSt=$pdo->prepare('SELECT nome,cartoes FROM clientes WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $clienteSt->execute([':id'=>$cliente_fidelidade,':empresa'=>(int)$id_empresa]);
    $clienteRegistro=$clienteSt->fetch(PDO::FETCH_ASSOC);
    if (!$clienteRegistro) throw new RuntimeException('Cliente não encontrado nesta empresa.');
    $nome_cliente=$clienteRegistro['nome'];
    $cartoesAtuais=(int)$clienteRegistro['cartoes'];
    $limiteCartoes=(int)$total_cartoes_config;
    $cartoesNovos=$limiteCartoes>0 ? min($limiteCartoes,$cartoesAtuais+1) : 0;
    $fidelidadeSt=$pdo->prepare('UPDATE clientes SET cartoes=:cartoes WHERE id=:id AND empresa=:empresa');
    $fidelidadeSt->execute([':cartoes'=>$cartoesNovos,':id'=>$cliente_fidelidade,':empresa'=>(int)$id_empresa]);
}

$total_carrinho = 0;
$query = $pdo->prepare("SELECT * FROM carrinho WHERE sessao=:sessao AND pedido=0 AND mesa='' AND empresa=:empresa FOR UPDATE");
$query->execute([':sessao'=>(int)$sessao,':empresa'=>(int)$id_empresa]);
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


    $query2 = $pdo->prepare('SELECT * FROM produtos WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $query2->execute([':id'=>$produto,':empresa'=>(int)$id_empresa]);
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if (!$res2) throw new RuntimeException('Produto não encontrado nesta empresa.');
    $id_categoria = $res2[0]['categoria'];
    $valor_produto = $res2[0]['valor_venda'];
    $estoque = $res2[0]['estoque'];
    $tem_estoque = $res2[0]['tem_estoque'];
    $valor_compra = $res2[0]['valor_compra'];
    $valor_compra = $quantidade * $valor_compra;
    $total_compra += $valor_compra;


    if ($tem_estoque == 'Sim') {
      if (!is_numeric($quantidade) || (float)$quantidade<=0) throw new RuntimeException('Quantidade inválida no carrinho.');
      if ((float)$estoque < (float)$quantidade) throw new RuntimeException('Estoque insuficiente para o produto selecionado.');
      $total_produtos = $estoque - $quantidade;
      $estoqueSt=$pdo->prepare('UPDATE produtos SET estoque=:estoque WHERE id=:id AND empresa=:empresa');
      $estoqueSt->execute([':estoque'=>$total_produtos,':id'=>$produto,':empresa'=>(int)$id_empresa]);
    }

  }
} else {
  throw new RuntimeException('Carrinho vazio.');
}




// Calcula o desconto efetivo no servidor, sem confiar no total enviado pelo navegador.
if (!is_numeric($desconto) || !is_numeric($frete) || (float)$desconto < 0 || (float)$frete < 0) {
    throw new RuntimeException('Desconto ou frete inválido.');
}
if ($tipo_desconto !== '%' && $tipo_desconto !== 'reais') {
    throw new RuntimeException('Tipo de desconto inválido.');
}
$descontoAplicado = $tipo_desconto === '%'
    ? round($total_carrinho * (float)$desconto / 100, 2)
    : (float)$desconto;
if ($descontoAplicado > $total_carrinho) throw new RuntimeException('Desconto maior que o valor dos produtos.');
$desconto = $descontoAplicado;
$total_final = round($total_carrinho + (float)$frete - $descontoAplicado, 2);

if($total_final <= 0){
	throw new RuntimeException('O valor da venda tem que ser maior que zero.');
}
$primeiraParcela = $total_final;
$segundaParcela = 0.0;
$idForma2 = 0;
if ($parcialSolicitado) {
    $primeiraParcela = round((float)$valorPagoInformado,2);
    $segundaParcela = round($total_final - $primeiraParcela,2);
    if ($primeiraParcela <= 0 || $segundaParcela <= 0) throw new RuntimeException('Divisão de pagamento inválida.');
    if (abs((float)$restanteInformado-$segundaParcela) > 0.02) throw new RuntimeException('Total da divisão mudou. Atualize o carrinho.');
    if (trim((string)$forma_pgto2) === '') throw new RuntimeException('Selecione a segunda forma de pagamento.');
    $segundaFormaSt=$pdo->prepare('SELECT id FROM formas_pgto WHERE nome=:nome AND empresa=:empresa LIMIT 1');
    $segundaFormaSt->execute([':nome'=>$forma_pgto2,':empresa'=>(int)$id_empresa]);
    $idForma2=(int)($segundaFormaSt->fetchColumn() ?: 0);
    if (!$idForma2) throw new RuntimeException('Segunda forma de pagamento inválida.');
    if ($data_restante < $data_atual || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',(string)$data_restante)) throw new RuntimeException('Data do segundo pagamento inválida.');
}



// Troco só é permitido quando o pagamento principal é em dinheiro.
$formaDinheiro = in_array(mb_strtolower(trim((string)$saida), 'UTF-8'), ['dinheiro','espécie','especie'], true);
if (!is_numeric($troco) || (float)$troco < 0) throw new RuntimeException('Valor recebido inválido.');
if (!$formaDinheiro && (float)$troco > 0.009) throw new RuntimeException('Troco só pode ser informado em pagamentos em dinheiro.');
if ($formaDinheiro && (float)$troco > 0.009 && (float)$troco + 0.009 < $primeiraParcela) {
    throw new RuntimeException('Valor recebido insuficiente. Para dividir o pagamento, utilize o fluxo específico.');
}
if (!$formaDinheiro) $troco = 0;



if(strtotime($data) > strtotime($data_atual)){
	$pago = 'Não';
	$data_pgto = '';
	$usuario_pgto = '';
}else{
	$pago = 'Sim';
	$data_pgto = $data;
	$usuario_pgto = $id_usuario;
}


// O status da venda não deve alterar a quitação individual da primeira parcela.
$primeiraPaga=$pago;
$primeiraDataPgto=$data_pgto;
$primeiroUsuarioPgto=$usuario_pgto;
if ($parcialSolicitado && $data_restante > $data_atual) $pago = 'Não';

// Serializa a numeração por empresa e dia, inclusive entre caixas diferentes.
$lockPedidoNome='pdv_pedido_'.(int)$id_empresa.'_'.date('Ymd');
$lockPedidoSt=$pdo->prepare('SELECT GET_LOCK(:nome,10)');
$lockPedidoSt->execute([':nome'=>$lockPedidoNome]);
if ((int)$lockPedidoSt->fetchColumn() !== 1) {
    throw new RuntimeException('Não foi possível reservar o número do pedido. Tente novamente.');
}
$ultimoPedidoSt=$pdo->prepare('SELECT COALESCE(MAX(pedido),0) FROM vendas WHERE empresa=:empresa AND data=CURDATE()');
$ultimoPedidoSt->execute([':empresa'=>(int)$id_empresa]);
$pedido=(int)$ultimoPedidoSt->fetchColumn()+1;

$troco_calculado = 0;
if ($formaDinheiro && $troco > 0) {
    $troco_calculado = round($troco - $primeiraParcela, 2);
} else {
    $troco = $total_final;
}

// Revalida o vínculo antes de persistir a venda.
$consultaCaixa->execute([':usuario'=>(int)$id_usuario,':empresa'=>(int)$id_empresa]);
if ((int)($consultaCaixa->fetchColumn() ?: 0) !== $id_caixa) {
    throw new RuntimeException('O caixa foi alterado durante a venda. Atualize o PDV.');
}


if($entrega == ""){
	$entrega = 'Consumir Local';
}


$query=$pdo->prepare("INSERT INTO vendas SET cliente=:cliente,valor=:valor,total_pago=:total_pago,troco=:troco,data=CURDATE(),hora=CURTIME(),status='Iniciado',pago=:pago,obs='',taxa_entrega=:frete,tipo_pgto=:forma,usuario_baixa=0,entrega=:entrega,mesa=:mesa,nome_cliente=:nome,cupom=:desconto,pago_entregador='Não',pedido=:pedido,ref_api='',tipo_pedido=:tipo,taxa_extra_pedido=0,caixa=:caixa,custo=:custo,empresa=:empresa");
$query->execute([':cliente'=>$cliente,':valor'=>$total_final,':total_pago'=>$troco,':troco'=>$troco_calculado,':pago'=>$pago,':frete'=>$frete,':forma'=>$saida,':entrega'=>$entrega,':mesa'=>$mesa,':nome'=>$nome_cliente,':desconto'=>$desconto,':pedido'=>$pedido,':tipo'=>$tipo_pedido,':caixa'=>$id_caixa,':custo'=>$total_compra,':empresa'=>(int)$id_empresa]);
$id_pedido = $pdo->lastInsertId();
$id_pedido_feito = $pdo->lastInsertId();


//lançar nas contas a receber
$receberSt=$pdo->prepare("INSERT INTO receber SET descricao='Nova Venda',cliente=:cliente,valor=:valor,vencimento=:vencimento,data_pgto=:data_pgto,data_lanc=CURDATE(),forma_pgto=:forma,frequencia=0,arquivo='sem-foto.jpg',foto='sem-foto.jpg',subtotal=:subtotal,usuario_lanc=:usuario,usuario_pgto=:usuario_pgto,pago=:pago,referencia='Venda',caixa=:caixa,hora=CURTIME(),tipo='Balcão',custo=:custo,empresa=:empresa");
$receberSt->execute([':cliente'=>$cliente,':valor'=>$primeiraParcela,':vencimento'=>$data,':data_pgto'=>$primeiraDataPgto ?: null,':forma'=>$id_saida,':subtotal'=>$primeiraParcela,':usuario'=>(int)$id_usuario,':usuario_pgto'=>$primeiroUsuarioPgto ?: null,':pago'=>$primeiraPaga,':caixa'=>$id_caixa,':custo'=>$total_compra,':empresa'=>(int)$id_empresa]);
if ($parcialSolicitado) {
    $segundaPaga=$data_restante===$data_atual ? 'Sim' : 'Não';
    $receberSt->execute([':cliente'=>$cliente,':valor'=>$segundaParcela,':vencimento'=>$data_restante,':data_pgto'=>$segundaPaga==='Sim' ? $data_restante : null,':forma'=>$idForma2,':subtotal'=>$segundaParcela,':usuario'=>(int)$id_usuario,':usuario_pgto'=>$segundaPaga==='Sim' ? (int)$id_usuario : null,':pago'=>$segundaPaga,':caixa'=>$id_caixa,':custo'=>0,':empresa'=>(int)$id_empresa]);
}


//relacionar itens do carrinho com o pedido
$itensSt=$pdo->prepare("UPDATE carrinho SET cliente=:cliente,pedido=:pedido WHERE sessao=:sessao AND pedido=0 AND mesa='' AND empresa=:empresa");
$itensSt->execute([':cliente'=>$cliente,':pedido'=>$id_pedido,':sessao'=>(int)$id_usuario,':empresa'=>(int)$id_empresa]);
if ($itensSt->rowCount() !== $total_reg) throw new RuntimeException('O carrinho mudou durante a venda.');
$tempSt=$pdo->prepare("UPDATE temp SET pedido=:pedido WHERE sessao=:sessao AND pedido=0");
$tempSt->execute([':pedido'=>$id_pedido,':sessao'=>(int)$id_usuario]);



$pdo->commit();
echo 'Salvo com Sucesso-'.$id_pedido;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('PDV finalizar venda: '.$e->getMessage());
    http_response_code($e instanceof RuntimeException ? 409 : 500);
    echo $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível finalizar a venda.';
} finally {
    if (!empty($lockPedidoNome)) {
        try {
            $liberarPedido=$pdo->prepare('SELECT RELEASE_LOCK(:nome)');
            $liberarPedido->execute([':nome'=>$lockPedidoNome]);
        } catch (Throwable $ignorado) {
            error_log('PDV: falha ao liberar bloqueio de numeração.');
        }
    }
}

 ?>