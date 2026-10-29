<?php 
$tabela = 'receber';
require_once("../../../../conexao.php");

$data_atual = date('Y-m-d');
$data_hoje = date('Y-m-d');

$id = @$_POST['id'];

$total_pago = 0;
$total_pendentes = 0;
$total_vencidas = 0;
$total_pendentesF = 0;
$total_vencidasF = 0;
$query = $pdo->query("SELECT * from $tabela where cliente = '$id' and pago != 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
echo <<<HTML
<small><small>
	<table id="" style="width:100%; font-size:12px;">
<thead>
<tr align="center">
	<th >Descrição</th>
	<th class="esc">Valor</th>	
	<th class="esc">Vencimento</th>		
	<th class="esc">Baixar</th>				
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i<$linhas; $i++){
	$id = $res[$i]['id'];
	$descricao = $res[$i]['descricao'];
	$cliente = $res[$i]['cliente'];
	$valor = $res[$i]['valor'];
	$vencimento = $res[$i]['vencimento'];
	$data_pgto = $res[$i]['data_pgto'];
	$data_lanc = $res[$i]['data_lanc'];
	$forma_pgto = $res[$i]['forma_pgto'];
	$frequencia = $res[$i]['frequencia'];
	$obs = $res[$i]['obs'];
	$arquivo = $res[$i]['arquivo'];
	$referencia = $res[$i]['referencia'];
	$id_ref = $res[$i]['id_ref'];
	$multa = $res[$i]['multa'];
	$juros = $res[$i]['juros'];
	$desconto = $res[$i]['desconto'];
	$taxa = $res[$i]['taxa'];
	$subtotal = $res[$i]['subtotal'];
	$usuario_lanc = $res[$i]['usuario_lanc'];
	$usuario_pgto = $res[$i]['usuario_pgto'];
	$pago = $res[$i]['pago'];

	$vencimentoF = implode('/', array_reverse(@explode('-', $vencimento)));
	$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
	$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));



	$valorF = @number_format($valor, 2, ',', '.');
	$multaF = @number_format($multa, 2, ',', '.');
	$jurosF = @number_format($juros, 2, ',', '.');
	$descontoF = @number_format($desconto, 2, ',', '.');
	$taxaF = @number_format($taxa, 2, ',', '.');
	$subtotalF = @number_format($subtotal, 2, ',', '.');

	if($pago == "Sim"){
		$valor_finalF = @number_format($subtotal, 2, ',', '.');
	}else{
		$valor_finalF = @number_format($valor, 2, ',', '.');
	}



$query2 = $pdo->query("SELECT * FROM formas_pgto where id = '$forma_pgto'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_pgto = $res2[0]['nome'];
	$taxa_pgto = $res2[0]['taxa'];
}else{
	$nome_pgto = 'Sem Registro';
	$taxa_pgto = 0;
}


$query2 = $pdo->query("SELECT * FROM frequencias where dias = '$frequencia'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_frequencia = $res2[0]['frequencia'];
}else{
	$nome_frequencia = 'Sem Registro';
}




if($pago == 'Sim'){
	$classe_pago = 'green';
	$ocultar = 'ocultar';
	$total_pago += $subtotal;
}else{
	$classe_pago = 'red';
	$ocultar = '';
	$total_pendentes += $valor;
}	

$valor_multa = 0;
$valor_juros = 0;
$classe_venc = '';
if(strtotime($vencimento) < strtotime($data_hoje)){
	$classe_venc = 'red';
	$valor_multa = $multa_atraso;

	//pegar a quantidade de dias que o pagamento está atrasado
	$dif = strtotime($data_hoje) - strtotime($vencimento);
	$dias_vencidos = floor($dif / (60*60*24));

	$valor_juros = ($valor * $juros_atraso / 100) * $dias_vencidos;
	$total_vencidas += $valor;
}

$total_pendentesF = @number_format($total_pendentes, 2, ',', '.');
$total_vencidasF = @number_format($total_vencidas, 2, ',', '.');

$taxa_conta = $taxa_pgto * $valor / 100;




//PEGAR RESIDUOS DA CONTA
	$total_resid = 0;
	$valor_com_residuos = 0;
	$query2 = $pdo->query("SELECT * FROM receber WHERE id_ref = '$id' and residuo = 'Sim'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res2) > 0){

		$descricao = '(Resíduo) - ' .$descricao;

		for($i2=0; $i2 < @count($res2); $i2++){
			foreach ($res2[$i2] as $key => $value){} 
				$id_res = $res2[$i2]['id'];
			$valor_resid = $res2[$i2]['valor'];
			$total_resid += $valor_resid - $res2[$i2]['desconto'];
		}


		$valor_com_residuos = $valor + $total_resid;
	}
	if($valor_com_residuos > 0){
		$vlr_antigo_conta = '('.$valor_com_residuos.')';
		$descricao_link = '';
		$descricao_texto = 'd-none';
	}else{
		$vlr_antigo_conta = '';
		$descricao_link = 'd-none';
		$descricao_texto = '';
	}

echo <<<HTML
<tr align="center" style="border-bottom: 1px solid #000; padding-bottom: 3px; color:{$classe_venc}">
<td ><i class="fa fa-square mr-1" style="color:{$classe_pago}"></i> {$descricao}</td>
<td class="esc">R$ {$valor_finalF}</td>
<td class="esc">{$vencimentoF}</td>
<td class="esc ">
<big><a class="" href="#" onclick="baixar('{$id}', '{$valor}', '{$descricao}', '{$forma_pgto}', '{$taxa_conta}', '{$valor_multa}', '{$valor_juros}')" title="Baixar Conta"><img src="images/icons/black/square-check.png" alt="" title="" width="22px" style="margin-top: 4px"/></a></big>
</td>
</tr>
HTML;

}

}else{
	echo '<span style="font-size:13px">Não possui nenhuma conta!</span>';
}


echo <<<HTML
</tbody>
</table>
</small></small>
<br>
	<div align="right" style="font-size:13px"><span>Total Pendentes: <span style="color:green">R$ {$total_pendentesF}</span></span> <br>
	<span style="margin-left: 25px">Total Vencidas: <span style="color:red">R$ {$total_vencidasF}</span></span>
	</div>
HTML;
?>


<script type="text/javascript">

function baixar(id, valor, descricao, pgto, taxa, multa, juros){
	$('#id-baixar').val(id);
	$('#titulo_baixar').text(descricao);
	$('#valor-baixar').val(valor);
	$('#saida-baixar').val(pgto).change();
	$('#subtotal').val(valor);

	
	$('#valor-juros').val(juros);
	$('#valor-desconto').val('');
	$('#valor-multa').val(multa);
	$('#valor-taxa').val(taxa);

	totalizar()

	$('#btn_baixar').click();
	
}

</script>