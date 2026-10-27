<?php 
@session_start();
require_once('../../sistema/conexao.php');

$pagamento = $_POST['pagamento'];
$entrega = $_POST['entrega'];
$rua = $_POST['rua'];
$numero = $_POST['numero'];
$bairro = $_POST['bairro'];
$complemento = $_POST['complemento'];
$total_pago = $_POST['troco'];
$obs = $_POST['obs'];
$sessao = @$_SESSION['sessao_usuario'];
$total_pago = str_replace(',', '.', $total_pago);
$nome_cliente_ped = @$_POST['nome_cliente'];
$tel_cliente = @$_POST['tel_cliente'];
$cliente = @$_POST['id_cliente'];
$mesa = @$_POST['mesa'];
$cupom = @$_POST['cupom'];
$codigo_pix = @$_POST['codigo_pix'];
$cep = @$_POST['cep'];
$cidade = @$_POST['cidade'];
$taxa_entrega = @$_POST['taxa_entrega'];

//verificar pgto pix
require("verificar_pgto.php");
if(@$status_api == 'approved'){
  $pago = 'Sim';
}else{
  $pago = 'Não';
}

if($cupom == ""){
  $cupom = 0;
}

if($tel_cliente != ""){
  $query = $pdo->query("SELECT * FROM clientes where telefone = '$tel_cliente' ");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  if(@count($res) > 0){ 
    $cliente = $res[0]['id'];


    //atualiza os dados do cliente
$query = $pdo->prepare("UPDATE clientes SET nome = :nome, rua = :rua, numero = :numero, complemento = :complemento, bairro = :bairro, cep = :cep, cidade = :cidade where id = '$cliente'");
$query->bindValue(":nome", "$nome_cliente_ped");
$query->bindValue(":rua", "$rua");
$query->bindValue(":numero", "$numero");
$query->bindValue(":complemento", "$complemento");
$query->bindValue(":bairro", "$bairro");
$query->bindValue(":cep", "$cep");
$query->bindValue(":cidade", "$cidade");
$query->execute();

  }else{
    $query = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, rua = :rua, numero = :numero, bairro = :bairro, complemento = :complemento, data = curDate(), cep = :cep, cidade = :cidade");
    $query->bindValue(":nome", "$nome_cliente_ped");
  $query->bindValue(":telefone", "$tel_cliente");
  $query->bindValue(":rua", "$rua");
  $query->bindValue(":numero", "$numero");
  $query->bindValue(":bairro", "$bairro");
  $query->bindValue(":complemento", "$complemento");
  $query->bindValue(":cep", "$cep");
  $query->bindValue(":cidade", "$cidade");
  $query->execute();
  $cliente = $pdo->lastInsertId();
  }
}





$total_carrinho = 0;





$total_carrinho = 0;
$query = $pdo->query("SELECT * FROM carrinho where sessao = '$sessao' and id_sabor = 0");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
  for($i=0; $i < $total_reg; $i++){
    foreach ($res[$i] as $key => $value){}  

      $id = $res[$i]['id'];
    $total_item = $res[$i]['total_item'];
    $produto = $res[$i]['produto']; 

    $total_carrinho += $total_item;    
    
  }
}else{
  echo '0';
  exit();
}
 



 $total_com_frete = $total_carrinho + $taxa_entrega - $cupom;
  if($total_pago == ""){
    $total_pago = $total_com_frete;
  }
 $troco = $total_pago - $total_com_frete;
 
//recuperar número do pedido
$query = $pdo->query("SELECT * FROM vendas where data = curDate() order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$num_pedido = $res[0]['pedido'];
if($num_pedido == ""){
  $num_pedido = 0;
}
$pedido = $num_pedido + 1;

$query = $pdo->prepare("INSERT INTO vendas SET cliente = '$cliente', valor = '$total_com_frete', total_pago = '$total_pago', troco = '$troco', data = curDate(), hora = curTime(), status = 'Iniciado', pago = '$pago', obs = :obs, taxa_entrega = '$taxa_entrega', tipo_pgto = '$pagamento', usuario_baixa = '0', entrega = '$entrega', mesa = '$mesa', nome_cliente = '$nome_cliente_ped', cupom = '$cupom', pago_entregador = 'Não', pedido = '$pedido', ref_api = '$codigo_pix'"); 
$query->bindValue(":obs", "$obs");
$query->execute();
$id_pedido = $pdo->lastInsertId();




//relacionar itens do carrinho com o pedido
$pdo->query("UPDATE carrinho SET cliente = '$cliente', pedido = '$id_pedido' where sessao = '$sessao' and pedido = '0'"); 

//limpar a sessao aberta
@$_SESSION['sessao_usuario'] = "";
//session_destroy();

$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes",strtotime(date('H:i'))));
echo $hora_pedido;

if($status_whatsapp == 'Api'){
$tel_cliente_whats = '55'.preg_replace('/[ ()-]+/' , '' , $tel_cliente);
$total_com_freteF = number_format($total_com_frete, 2, ',', '.');

$mensagem = '*Pedido:* '.$pedido.'%0A';
$mensagem .= '*Cliente:* '.$nome_cliente_ped.'%0A';
$mensagem .= '*Telefone:* '.$tel_cliente.'%0A';
$mensagem .= '*Valor:* R$ '.$total_com_freteF.'%0A';
$mensagem .= '*Pagamento:* '.$pagamento.'%0A';
$mensagem .= '*Pago:* '.$pago.'%0A';
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

  $query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_car' and tabela = 'adicionais'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  $total_reg2 = @count($res2);
  if($total_reg2 > 0){
    if($total_reg2 > 1){
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Adicionais ';
    }else{
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Adicional ';
    }
        $mensagem .= ' '.'```'.$texto_adicional.'```';
        for($i2=0; $i2 < $total_reg2; $i2++){
          foreach ($res2[$i2] as $key => $value){}
            $id_temp = $res2[$i2]['id'];        
          $id_item = $res2[$i2]['id_item'];   

          $query3 =$pdo->query("SELECT * FROM adicionais where id = '$id_item'");
          $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
          $total_reg3 = @count($res3);
          $nome_adc = $res3[0]['nome'];          
          if($i2 < ($total_reg2 - 1)){
           $nome_adc .= ', ';
          }
          $mensagem .= '```'.$nome_adc.'```'.'%0A';
  }

  }



//ingredientes pizza 2 sab
$query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_car' and tabela = 'ingredientes'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  $total_reg2 = @count($res2);
  if($total_reg2 > 0){
    if($total_reg2 > 1){
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Retirar Ingredientes ';
    }else{
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Retirar Ingrediente ';
    }

   $mensagem .= ' '.'```'.$texto_adicional.'```';

        for($i2=0; $i2 < $total_reg2; $i2++){
          foreach ($res2[$i2] as $key => $value){}
            $id_temp = $res2[$i2]['id'];        
          $id_item = $res2[$i2]['id_item'];   

          $query3 =$pdo->query("SELECT * FROM ingredientes where id = '$id_item'");
          $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
          $total_reg3 = @count($res3);
          $nome_adc = $res3[0]['nome'];         
          if($i2 < ($total_reg2 - 1)){
           $nome_adc .= ', ';
          }
           $mensagem .= '```'.$nome_adc.'```'.'%0A';
        }

     


}




//ond pizza 2 sab
if($obs_item_2sab != ""){
   $mensagem .= ' '.'```Observações: '.$obs_item_2sab.'```'.'%0A';
}





}


}


//COMEÇAR VER OS ADICIONAIS E OUTROS DOS DEMAIS ITENS QUE NAO SAO PIZZA 2 SAB
$query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'adicionais'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  $total_reg2 = @count($res2);
  if($total_reg2 > 0){
    if($total_reg2 > 1){
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Adicionais ';
    }else{
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Adicional ';
    }
        $mensagem .= ' '.'```'.$texto_adicional.'```';
        for($i2=0; $i2 < $total_reg2; $i2++){
          foreach ($res2[$i2] as $key => $value){}
            $id_temp = $res2[$i2]['id'];        
          $id_item = $res2[$i2]['id_item'];   

          $query3 =$pdo->query("SELECT * FROM adicionais where id = '$id_item'");
          $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
          $total_reg3 = @count($res3);
          $nome_adc = $res3[0]['nome'];          
          if($i2 < ($total_reg2 - 1)){
           $nome_adc .= ', ';
          }
          $mensagem .= '```'.$nome_adc.'```'.'%0A';
  }

  }



//ingredientes pizza 2 sab
$query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'ingredientes'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  $total_reg2 = @count($res2);
  if($total_reg2 > 0){
    if($total_reg2 > 1){
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Retirar Ingredientes ';
    }else{
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Retirar Ingrediente ';
    }

   $mensagem .= ' '.'```'.$texto_adicional.'```';

        for($i2=0; $i2 < $total_reg2; $i2++){
          foreach ($res2[$i2] as $key => $value){}
            $id_temp = $res2[$i2]['id'];        
          $id_item = $res2[$i2]['id_item'];   

          $query3 =$pdo->query("SELECT * FROM ingredientes where id = '$id_item'");
          $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
          $total_reg3 = @count($res3);
          $nome_adc = $res3[0]['nome'];         
          if($i2 < ($total_reg2 - 1)){
           $nome_adc .= ', ';
          }
           $mensagem .= '```'.$nome_adc.'```'.'%0A';
        }

     


}




//guarnicoes
$query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'guarnicoes'");
  $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
  $total_reg2 = @count($res2);
  if($total_reg2 > 0){
    if($total_reg2 > 1){
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Guarnições %0A';
    }else{
      $texto_adicional = $nome_prod .' ('.$total_reg2.') Guarnição %0A';
    }

   $mensagem .= ' '.'```'.$texto_adicional.'```';

        for($i2=0; $i2 < $total_reg2; $i2++){
          foreach ($res2[$i2] as $key => $value){}
            $id_temp = $res2[$i2]['id'];        
          $id_item = $res2[$i2]['id_item'];   

          $query3 =$pdo->query("SELECT * FROM guarnicoes where id = '$id_item'");
          $res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
          $total_reg3 = @count($res3);
          $nome_adc = $res3[0]['nome'];         
          if($i2 < ($total_reg2 - 1)){
           $nome_adc .= ', ';
          }
           $mensagem .= '```'.$nome_adc.'```'.'%0A';
        }

     


}



//ond pizza 2 sab
if($obs_item != ""){
   $mensagem .= ' '.'```Observações: '.$obs_item.'```'.'%0A';
}



}


//ond pizza 2 sab
if($obs != ""){
   $mensagem .= '%0A*Observações do Pedido*%0A';
   $mensagem .= '_'.$obs.'_'.'%0A%0A';
}


 if($entrega == "Delivery"){
      $mensagem .= '%0A*Endereço do Cliente*%0A';
      $endereco = $rua.' '.$numero.' '.$complemento.' '.$bairro.' '.$cidade;
       $mensagem .= '_'.$endereco.'_';
    
}


$mensagem .= '%0A%0A'.'```Obrigado pela preferência```'.'%0A';
$mensagem .= $url_sistema.'%0A';

$data_mensagem = date('Y-m-d H:i:s');
require("api2.php");
}

 ?>