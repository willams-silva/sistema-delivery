<?php 
include('../../conexao.php');

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
$data_hoje = utf8_encode(strftime('%A, %d de %B de %Y', strtotime('today')));

$id = $_GET['id'];

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

$valorF = number_format($valor, 2, ',', '.');
$total_pagoF = number_format($total_pago, 2, ',', '.');
$trocoF = number_format($troco, 2, ',', '.');
$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
$dataF = implode('/', array_reverse(explode('-', $data)));
	//$horaF = date("H:i", strtotime($hora));	

$valor_dos_itens = $valor - $taxa_entrega;
$valor_dos_itensF = number_format($valor_dos_itens, 2, ',', '.');

$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes",strtotime($hora)));

$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$nome_cliente = @$res2[0]['nome'];
$telefone_cliente = @$res2[0]['telefone'];
$rua_cliente = @$res2[0]['rua'];
$numero_cliente = @$res2[0]['numero'];
$complemento_cliente = @$res2[0]['complemento'];
$bairro_cliente = @$res2[0]['bairro'];

if($tipo_pgto == 'Cartão de Crédito'){
	$tipo_pgto = 'Crédito';
}

if($tipo_pgto == 'Cartão de Débito'){
	$tipo_pgto = 'Débito';
}


?>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {    		
		window.print()   
	} );
</script>

<style type="text/css">
	*{
		margin:0px;

		/*Espaçamento da margem da esquerda e da Direita*/
		padding:0px;
		background-color:#ffffff;


	}
	.text {
		&-center { text-align: center; }
	}
	.ttu { text-transform: uppercase;
		font-weight: bold;
		font-size: 1.2em;
	}

	.printer-ticket {
		display: table !important;
		width: 100%;

		/*largura do Campos que vai os textos*/
		max-width: 400px;
		font-weight: light;
		line-height: 1.3em;

		/*Espaçamento da margem da esquerda e da Direita*/
		padding: 0px;
		font-family: TimesNewRoman, Geneva, sans-serif; 

		/*tamanho da Fonte do Texto*/
		font-size: 10px; 



	}
	
	th { 
		font-weight: inherit;

		/*Espaçamento entre as uma linha para outra*/
		padding:5px;
		text-align: center;

		/*largura dos tracinhos entre as linhas*/
		border-bottom: 1px dashed #000000;
	}


	

	
	

	.cor{
		color:#000000;
	}
	
	
	.title { font-size: 1.5em;  }

	/*margem Superior entre as Linhas*/
	.margem-superior{
		padding-top:5px;
	}
	
	
}
</style>



<table class="printer-ticket">

	<tr>
		<th class="ttu" class="title" colspan="3"><?php echo $nome_sistema ?></th>

	</tr>
	<tr>
		<th colspan="3">
			<?php echo $endereco_sistema ?> <br />
			Contato: <?php echo $telefone_sistema ?> 
			<?php if($cnpj_sistema != ""){echo ' / CNPJ '. @$cnpj_sistema; } ?>  
		</th>
	</tr>

	<tr>
		<th colspan="3">Cliente <?php echo $nome_cliente ?> Tel: <?php echo $telefone_cliente ?>			
		<br>
		Venda: <b><?php echo $id ?></b> - Data: <?php echo $dataF ?> Hora: <?php echo $hora ?>


	</th>
</tr>

<tr>
	<th class="ttu margem-superior" colspan="3">
		Comprovante de Venda

	</th>
</tr>
<tr>
	<th colspan="3">
		CUMPOM NÃO FISCAL

	</th>
</tr>

<tbody>

	<?php 

	$res = $pdo->query("SELECT * from carrinho where pedido = '$id' order by id asc");
	$dados = $res->fetchAll(PDO::FETCH_ASSOC);
	$linhas = count($dados);

	$sub_tot;
	for ($i=0; $i < count($dados); $i++) { 
		foreach ($dados[$i] as $key => $value) {
		}
		$id_carrinho = $dados[$i]['id']; 
		$id_produto = $dados[$i]['produto']; 
		$quantidade = $dados[$i]['quantidade'];
		$total_item = $dados[$i]['total_item'];
		$obs_item = $dados[$i]['obs'];


		$res_p = $pdo->query("SELECT * from produtos where id = '$id_produto' ");
		$dados_p = $res_p->fetchAll(PDO::FETCH_ASSOC);
		$nome_produto = $dados_p[0]['nome'];  
		

		?>

		<tr>
			<td colspan="1" width="90%"> <?php echo $quantidade ?> - <?php echo $nome_produto ?></td>

		<td colspan="2" align="right">R$ <?php
		
		$total_itemF = number_format($total_item , 2, ',', '.');
				// $total = number_format( $cp1 , 2, ',', '.');
		echo $total_itemF ;
		?></td>
		</tr>


		<?php 
			
$query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'adicionais'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_reg2 = @count($res2);
if($total_reg2 > 0){
		if($total_reg2 > 1){
			$texto_adicional = '('.$total_reg2.') Adicionais';
		}else{
			$texto_adicional = '('.$total_reg2.') Adicional';
		}
		?>
		<tr>
			<td colspan="2" width="80%" >
				<small><b>&nbsp;&nbsp;&nbsp;<?php echo $texto_adicional ?> : </b>
				<?php 
					for($i2=0; $i2 < $total_reg2; $i2++){
		foreach ($res2[$i2] as $key => $value){}
			$id_temp = $res2[$i2]['id'];				
		$id_item = $res2[$i2]['id_item'];		

		$query3 =$pdo->query("SELECT * FROM adicionais where id = '$id_item'");
		$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
		$total_reg3 = @count($res3);
		$nome_adc = $res3[0]['nome'];
		echo $nome_adc;
		if($i2 < ($total_reg2 - 1)){
			echo ', ';
		}
	}
				 ?>
				</small>
			</td>
		</tr>
	<?php } ?>



	<?php 
			
$query2 =$pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'ingredientes'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$total_reg2 = @count($res2);
if($total_reg2 > 0){
		if($total_reg2 > 1){
			$texto_adicional = '('.$total_reg2.') Retirar Ingredientes';
		}else{
			$texto_adicional = '('.$total_reg2.') Retirar Ingrediente';
		}
		?>
		<tr>
			<td  colspan="3" width="100%" >
				<small><b>&nbsp;&nbsp;&nbsp;<?php echo $texto_adicional ?> : </b>
				<?php 
					for($i2=0; $i2 < $total_reg2; $i2++){
		foreach ($res2[$i2] as $key => $value){}
			$id_temp = $res2[$i2]['id'];				
		$id_item = $res2[$i2]['id_item'];		

		$query3 =$pdo->query("SELECT * FROM ingredientes where id = '$id_item'");
		$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);
		$total_reg3 = @count($res3);
		$nome_adc = $res3[0]['nome'];
		echo $nome_adc;
		if($i2 < ($total_reg2 - 1)){
			echo ', ';
		}
	}
				 ?>
				</small>
			</td>
		</tr>
	<?php } ?>




<?php 
			
if($obs_item != ""){
		?>
		<tr>
			<td  colspan="2" width="100%" >
				<small><b>&nbsp;&nbsp;&nbsp;Observação : </b>
				<?php echo $obs_item ?>
				</small>
			</td>
		</tr>
	<?php } ?>



<tr ><td></td></tr>


<?php } ?>


</tbody>
<tfoot>

	<tfoot>

		<tr>
			<th class="ttu"  colspan="3" class="cor">
				<!-- _ _	_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ -->
			</th>
		</tr>	
		
		<?php if($entrega == "Delivery"){ ?>
		<tr>
			<td colspan="1" width="60%">Total</td>
			<td colspan="2" align="right">R$ <?php echo @$valor_dos_itensF ?></td>
		</tr>
		<?php } ?>	

		<?php if($entrega == "Delivery"){ ?>
			<tr>
				<td colspan="1" width="60%">Frete</td>
				<td colspan="2" align="right">R$ <?php echo @$taxa_entregaF ?></td>
			</tr>
		<?php } ?>		

		

	</tr>

	<tr>
		<td colspan="1" width="60%">SubTotal</td>
		<td colspan="2" align="right"><b>R$ <?php echo @$valorF ?></b></td>
	</tr>	

	<?php if($total_pago != $valor){ ?>
		<tr>
			<td colspan="1" width="60%">Valor Recebido</td>
			<td colspan="2" align="right">R$ <?php echo @$total_pagoF ?></td>
		</tr>
	<?php } ?>

	<?php if($troco > 0){ ?>
		<tr>
			<td colspan="1" width="60%">Troco</td>
			<td colspan="2" align="right">R$ <?php echo @$trocoF ?></td>
		</tr>
	<?php } ?>	

	<tr>
		<th class="ttu" colspan="3" class="cor">
			<!-- _ _	_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ -->
		</th>
	</tr>	

	<tr>
		<td colspan="1" width="50%">Forma de Pagamento</td>
		<td colspan="2" align="right"><?php echo @$tipo_pgto ?></td>
	</tr>

	
	<tr>
		<td colspan="1" width="50%">Forma de Entrega</td>
		<td colspan="2" align="right"><?php echo @$entrega ?></td>
	</tr>	

	<tr>
		<td colspan="2">Está Pago?</td>
		<td align="right"><?php echo @$pago ?></td>
	</tr>




<tr>
			<th class="ttu"  colspan="3" class="cor">
				<!-- _ _	_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ -->
			</th>
		</tr>


		<?php if($entrega == "Delivery"){ ?>
			<tr>
		<th colspan="3"><b>Endereço	para Entrega</b>		
		<br>
		<?php echo $rua_cliente ?> <?php echo $numero_cliente ?> <?php echo $complemento_cliente ?>
		<?php echo $bairro_cliente ?>


	</th>
</tr>
		<?php } ?>	
	

</tfoot>




</table>


