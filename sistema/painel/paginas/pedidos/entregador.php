<?php 
require_once("../../../conexao.php");
$tabela = 'vendas';

$id = $_POST['id'];
$id_pedido = $_POST['id'];
$entregador = $_POST['entregador'];

$pdo->query("UPDATE $tabela SET entregador = '$entregador' where id = '$id'");

echo 'Salvo com Sucesso';


//BUSCAR AS INFORMAÇÕES DO PEDIDO
$query = $pdo->query("SELECT * from vendas where id = '$id' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

$id = $res[0]['id'];	
$cliente = $res[0]['cliente'];
$valor = $res[0]['valor'];
$total_pago = $res[0]['total_pago'];
$troco = $res[0]['troco'];
$data = $res[0]['data'];
$hora = $res[0]['hora'];
$status = $res[0]['status'];
$pago = $res[0]['pago'];
$obs = $res[0]['obs'];
$taxa_entrega = $res[0]['taxa_entrega'];
$tipo_pgto = $res[0]['tipo_pgto'];
$usuario_baixa = $res[0]['usuario_baixa'];
$entrega = $res[0]['entrega'];
$mesa = $res[0]['mesa'];
$nome_cliente_ped = $res[0]['nome_cliente'];
$cupom = $res[0]['cupom'];
$n_pedido = $res[0]['pedido'];

$cupomF = number_format($cupom, 2, ',', '.');
$valorF = number_format($valor, 2, ',', '.');
$total_pagoF = number_format($total_pago, 2, ',', '.');
$trocoF = number_format($troco, 2, ',', '.');
$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
$dataF = implode('/', array_reverse(explode('-', $data)));
	//$horaF = date("H:i", strtotime($hora));	

$valor_dos_itens = $valor - $taxa_entrega + $cupom;
$valor_dos_itensF = number_format($valor_dos_itens, 2, ',', '.');

$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes",strtotime($hora)));

$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_reg2 = @count($res2);
if($total_reg2 > 0){
$nome_cliente = @$res2[0]['nome'];
$telefone_cliente = @$res2[0]['telefone'];
$rua_cliente = @$res2[0]['rua'];
$numero_cliente = @$res2[0]['numero'];
$complemento_cliente = @$res2[0]['complemento'];
$bairro_cliente = @$res2[0]['bairro'];
$cidade_cliente = @$res2[0]['cidade'];
}

if($status_whatsapp == 'Api'){
$query2 = $pdo->query("SELECT * FROM usuarios where id = '$entregador'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$tel_entregador = $res2[0]['telefone'];

$tel_cliente_whats = '55'.preg_replace('/[ ()-]+/' , '' , $tel_entregador);


$mensagem = '*Pedido:* '.$n_pedido.'%0A';
$mensagem .= '*Cliente:* '.$nome_cliente.'%0A';
$mensagem .= '*Telefone:* '.$telefone_cliente.'%0A';
$mensagem .= '*Valor:* R$ '.$valorF.'%0A';
$mensagem .= '*Pagamento:* '.$tipo_pgto.'%0A';
$mensagem .= '*Previsão Entrega:* '.$hora_pedido.'%0A';
$mensagem .= '%0A________________________________%0A%0A';
$mensagem .= '*_Detalhes do Pedido_* %0A %0A';

//ABAIXO É PARA PEGAR OS PRODUTOS COMPRADOS
$nome_produto2 = '';
$res = $pdo->query("SELECT * from carrinho where pedido = '$id_pedido' and id_sabor = 0 order by id asc");
$dados = $res->fetchAll(PDO::FETCH_ASSOC);
$linhas = count($dados);


$sub_tot;
for ($i=0; $i < count($dados); $i++) { 
  foreach ($dados[$i] as $key => $value) {
  }
  $texto_produtos = '';
  $id_carrinho = $dados[$i]['id']; 
  $id_produto = $dados[$i]['produto']; 
  $quantidade = $dados[$i]['quantidade'];
  $total_item = $dados[$i]['total_item'];
  $obs_item = $dados[$i]['obs'];
  $item = $dados[$i]['item'];
    $variacao = $dados[$i]['variacao'];


    $query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if(@count(@$res2) > 0){
      $sigla_variacao = '('.$res2[0]['sigla'].')';      
    }else{
      $sigla_variacao = '';
    }

    $query2 = $pdo->query("SELECT * FROM produtos where id = '$id_produto'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if(@count(@$res2) > 0){
      $nome_produto = $res2[0]['nome'];
      $foto_produto = $res2[0]['foto'];
    }else{
      $nome_produto2 = '';
      $query33 = $pdo->query("SELECT * FROM carrinho where id_sabor = '$item' and pedido = '$id_pedido' ");
$res33 = $query33->fetchAll(PDO::FETCH_ASSOC);
$total_reg33 = @count($res33);
if($total_reg33 > 0){
  
  for($i33=0; $i33 < $total_reg33; $i33++){
    foreach ($res33[$i33] as $key => $value){}
    $prod = $res33[$i33]['produto'];
    $id_car = $res33[$i33]['id'];

    $query2 = $pdo->query("SELECT * FROM produtos where id = '$prod'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if(@count(@$res2) > 0){
      
      $foto_produto = $res2[0]['foto'];
      $cat_produto = $res2[0]['categoria'];
      if($i33 < $total_reg33 - 1){
        $nome_prod = $res2[0]['nome']. ' / ';
      }else{
        $nome_prod = $res2[0]['nome'];
      }
      
    }   

    $nome_produto2 .= $nome_prod;
  }
  
  $nome_produto = $nome_produto2;


}
    } 

$texto_produtos .= '✅'.$quantidade.' - '.$nome_produto.' '.$sigla_variacao.'%0A';



$mensagem .= '%0A'.$texto_produtos;

//INICIAR OS ADICIONAIS PARA PIZZA DOIS SABORES
$query33 = $pdo->query("SELECT * FROM carrinho where id_sabor = '$item' and pedido = '$id_pedido' and id_sabor > 0");
$res33 = $query33->fetchAll(PDO::FETCH_ASSOC);
$total_reg33 = @count($res33);
if($total_reg33 > 0){
  
  for($i33=0; $i33 < $total_reg33; $i33++){
    foreach ($res33[$i33] as $key => $value){}
    $prod = $res33[$i33]['produto'];
    $id_car = $res33[$i33]['id'];
    $obs_item_2sab = $res33[$i33]['obs'];

    $query2 = $pdo->query("SELECT * FROM produtos where id = '$prod'");
    $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
    if(@count(@$res2) > 0){
      
      $nome_prod = $res2[0]['nome'];
    }



}


}



}


//ond pizza 2 sab
if($obs != ""){
   $mensagem .= '%0A*Observações do Pedido*%0A';
   $mensagem .= '_'.$obs.'_'.'%0A%0A';
}


 if($entrega == "Delivery"){
      $mensagem .= '%0A*Endereço do Cliente*%0A';
      $endereco = $rua_cliente.' '.$numero_cliente.' '.$complemento_cliente.' '.$bairro_cliente.' '.$cidade_cliente;
       $mensagem .= '_'.$endereco.'_';
    
}



$data_mensagem = date('Y-m-d H:i:s');
require("../../../../js/ajax/api_texto.php");
}
 ?>