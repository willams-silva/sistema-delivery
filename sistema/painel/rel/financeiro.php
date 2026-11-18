<?php 
include('data_formatada.php');

$dataInicialF = implode('/', array_reverse(@explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(@explode('-', $dataFinal)));	

$filtro_tipoF = "";
if($filtro_tipo == "receber"){
	$filtro_tipoF = 'ENTRADAS / GANHOS';
	$classe_entradas = 'green'; 
}else{
	$filtro_tipoF = 'SAÍDAS / DESPESAS';
	$classe_entradas = 'red'; 
}


$filtro_dataF = "";
if($filtro_data == "data_lanc"){
	$filtro_dataF = 'DATA DE LANÇAMENTO'; 
}else if($filtro_data == "data_venc"){
	$filtro_dataF = 'DATA DE VENCIMENTO';
}else{
	$filtro_dataF = "DATA DE PAGAMENTO";
}

$filtro_delivery = '';

$filtro_lancamentoF = "";
if($filtro_lancamento != ""){
	if($filtro_lancamento == 'Conta'){
		if($filtro_tipo == 'receber'){
			$filtro_lancamentoF = 'Recebimentos';
		}else{
			$filtro_lancamentoF = 'Contas / Despesas';
		}
	}else if($filtro_lancamento == 'Venda'){
		$filtro_tipoF = 'VENDAS';
		$classe_entradas = '';

	}else if($filtro_lancamento == 'Cancelamento'){
		$filtro_tipoF = 'VENDAS CANCELADAS';
		$classe_entradas = '';

	}else if($filtro_lancamento == 'Compra'){
		$filtro_tipoF = 'COMPRAS';
		$classe_entradas = '';
	
	}else if($filtro_lancamento == 'Comissão'){
		$filtro_tipoF = 'COMISSÕES';
		$classe_entradas = '';
	
	}else if($filtro_lancamento == 'Serviço'){
		$filtro_tipoF = 'SERVIÇOS';
		$classe_entradas = '';

	}else if($filtro_lancamento == 'Delivery' || $filtro_lancamento == 'Retirar' || $filtro_lancamento == 'Consumir Local'){
		$filtro_tipoF = 'VENDAS DELIVERY';
		$classe_entradas = '';
		$filtro_delivery = 'Sim';
	}
	
	
}

$filtro_pendentesF = "";
if($filtro_pendentes == "Não"){
	$filtro_pendentesF = 'PENDENTES'; 
}else if($filtro_pendentes == "Sim"){
	$filtro_pendentesF = 'PAGAS';
}else{
	$filtro_pendentesF = "";
}


$datas = "";
if($dataInicial == $dataFinal){
	$datas = $dataInicialF;
}else{
	$datas = $dataInicialF.' à '.$dataFinalF;
}

$texto_filtro = $filtro_dataF.' : '.$datas.' '.$filtro_pendentesF;

$sql_balcao = " ";
if($filtro_lancamento == "Balcão"){
	$sql_balcao = " and tipo = 'Balcão' ";
	$filtro_lancamento = 'Venda';
}

$sql_mesa = " ";
if($filtro_lancamento == "Mesas"){
	$sql_mesa = " and tipo = 'Venda' ";
	$filtro_lancamento = 'Venda';
}

?>
<!DOCTYPE html>
<html>
<head>

<style>

@import url('https://fonts.cdnfonts.com/css/tw-cen-mt-condensed');
@page { margin: 145px 20px 25px 20px; }
#header { position: fixed; left: 0px; top: -110px; bottom: 100px; right: 0px; height: 35px; text-align: center; padding-bottom: 100px; }
#content {margin-top: 0px;}
#footer { position: fixed; left: 0px; bottom: -60px; right: 0px; height: 80px; }
#footer .page:after {content: counter(page, my-sec-counter);}
body {font-family: 'Tw Cen MT', sans-serif;}

.marca{
	position:fixed;
	left:50;
	top:100;
	width:80%;
	opacity:8%;
}


/* Padronização visual Bora Comanda: somente apresentação, sem alterar consultas. */
@page { margin: 145px 24px 45px 24px; }
body { font-family: DejaVu Sans, sans-serif !important; color: #263238; }
#header { top: -125px !important; height: 108px !important; padding-bottom: 0 !important; bottom: auto !important; text-align: left !important; }
#header > div:first-child { height: 80px !important; border: none !important; border-bottom: 2px solid #475569 !important; padding-bottom: 6px !important; }
#header > div:first-child table { width: 100% !important; border-collapse: collapse !important; table-layout: fixed; }
#header > div:first-child table td { vertical-align: middle !important; border: none !important; line-height: 1.5; overflow-wrap: break-word; }
#header img#imag { max-width: 95px !important; max-height: 58px !important; width: auto !important; height: auto !important; margin: 0 6px !important; }
#header > div:first-child table td:first-child { width: 20% !important; text-align: center !important; }
#header > div:first-child table td:last-child { width: 35% !important; text-align: right !important; font-size: 9px !important; }
#header > div:first-child table td:last-child big { font-size: 12px !important; font-weight: bold; color: #172b4d; }
#cabecalhotabela { border-bottom: 1px solid #94a3b8 !important; margin-bottom: 6px !important; }
#cabecalhotabela #cabeca { background: #e9eef5 !important; }
#cabecalhotabela td { padding: 6px 3px !important; font-weight: bold; }
#content table { border-collapse: collapse; }
#content td { padding-top: 5px; padding-bottom: 5px; overflow-wrap: break-word; }
#footer { bottom: -34px !important; height: 26px !important; border-top: 1px solid #cbd5e1; color: #64748b; }
#footer hr { display: none; }
.marca { opacity: .07 !important; }

</style>

</head>
<body>
<?php 
if($marca_dagua == 'Sim' and $tipo_rel != 'HTML'){ ?>
<img class="marca" src="<?php echo $url_sistema ?>sistema/img/<?php echo $logo_sistema ?>">	
<?php } ?>


<div id="header" >

		<div style="border-style: solid; font-size: 10px; height: 55px;">
			<table style="width: 100%; border: 0px solid #ccc;">
				<tr>
					<td style="border: 1px; solid #000; width: 25%; text-align: left;">
						<img style="margin-top: 0px; margin-left: 7px;" id="imag"
							src="<?php echo $url_sistema ?>sistema/img/<?php echo $logo_sistema ?>" width="60%">
					</td>

					<td style="text-align: center; font-size: 10px; width: 40%;">

						<b><?php echo mb_strtoupper($nome_sistema) ?></b><br>
						<b>CNPJ: </b><?php echo mb_strtoupper($cnpj_sistema) ?><br>
						<b>INSTAGRAM:</b> <?php echo mb_strtoupper($instagram_sistema) ?><br>
						<?php echo mb_strtoupper($endereco_sistema) ?>


					</td>
				<td style="width: 35%; text-align: right; font-size: 9px;padding-right: 10px;">
						<b><big>RELATÓRIO DE <span style="color:<?php echo $classe_entradas ?>"><?php echo $filtro_tipoF ?> <?php if($filtro_lancamentoF != ""){ echo '('. mb_strtoupper($filtro_lancamentoF).')'; } ?></span></big></b><br> <?php echo mb_strtoupper($texto_filtro) ?> <br> <?php echo mb_strtoupper($data_hoje) ?>
				</td>
			</tr>		
		</table>
	</div>

<br>


		<table id="cabecalhotabela" style="border-bottom-style: solid; font-size: 8px; margin-bottom:10px; width: 100%; table-layout: fixed;">
			<thead>
				
				<tr id="cabeca" style="margin-left: 0px; background-color:#CCC">
					<td style="width:22%">DESCRIÇÃO</td>
					<td style="width:10%">VALOR</td>
					<td style="width:12%">DATA LANÇAMENTO</td>
					<td style="width:12%">DATA VENCIMENTO</td>
					<td style="width:12%">DATA PAGAMENTO</td>
					<td style="width:17%">RECEBIDO POR</td>	
					<td style="width:15%">REFERÊNCIA</td>		
					
				</tr>
			</thead>
		</table>
</div>

<div id="footer" class="row">
<hr style="margin-bottom: 0;">
	<table style="width:100%;">
		<tr style="width:100%;">
			<td style="width:60%; font-size: 10px; text-align: left;"><?php echo $nome_sistema ?> Telefone: <?php echo $telefone_sistema ?></td>
			<td style="width:40%; font-size: 10px; text-align: right;"><p class="page">Página  </p></td>
		</tr>
	</table>
</div>

<div id="content" style="margin-top: -10;">



		<table style="width: 100%; table-layout: fixed; font-size:8px; text-transform: uppercase;">
			<thead>
				<tbody>
					<?php

$total_valor = 0;
$total_valorF = 0;
$total_pendentes = 0;
$total_pendentesF = 0;
$total_pagas = 0;
$total_pagasF = 0;
$pendentes = 0;
$pagas = 0;


if($filtro_delivery == 'Sim'){
	$query = $pdo->query("SELECT * from $filtro_tipo where $filtro_data >= '$dataInicial' and $filtro_data <= '$dataFinal' and pago LIKE '%$filtro_pendentes%' $sql_mesa $sql_balcao and (referencia = 'Delivery' or referencia = 'Retirar' or referencia = 'Consumir Local') and empresa = '$id_empresa' order by $filtro_data asc");
}else{
	$query = $pdo->query("SELECT * from $filtro_tipo where $filtro_data >= '$dataInicial' and $filtro_data <= '$dataFinal' and pago LIKE '%$filtro_pendentes%' and empresa = '$id_empresa' and referencia LIKE '%$filtro_lancamento%' $sql_mesa $sql_balcao order by $filtro_data asc");
}

$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
for($i=0; $i<$linhas; $i++){
	$id = $res[$i]['id'];
$descricao = $res[$i]['descricao'];
$valor = $res[$i]['valor'];
$data_lanc = $res[$i]['data_lanc'];
$data_venc = $res[$i]['vencimento'];
$data_pgto = $res[$i]['data_pgto'];
$usuario_lanc = $res[$i]['usuario_lanc'];
$usuario_pgto = $res[$i]['usuario_pgto'];
$arquivo = $res[$i]['arquivo'];
$pago = $res[$i]['pago'];
$obs = $res[$i]['obs'];
$referencia = $res[$i]['referencia'];
	

	$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));
$data_vencF = implode('/', array_reverse(@explode('-', $data_venc)));
$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
$valorF = number_format($valor, 2, ',', '.');


$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_pgto'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_usu_pgto = $res2[0]['nome'];
}else{
	$nome_usu_pgto = '';
}


if($pago == 'Sim'){
	$classe_pago = 'verde.jpg';	
	$total_pagas += $valor;
	$pagas += 1;
}else{
	$classe_pago = 'vermelho.jpg';	
	$total_pendentes += $valor;
	$pendentes += 1;
}


$total_pagasF = number_format($total_pagas, 2, ',', '.');
$total_pendentesF = number_format($total_pendentes, 2, ',', '.');


if($data_pgtoF == '00/00/0000'){
	$data_pgtoF = 'Pendente';
}
  	 ?>

  	 
      <tr>
<td style="width:22%">
<img style="margin-top: 0px" src="<?php echo $url_sistema ?>sistema/painel/images/<?php echo $classe_pago ?>" width="8px">
	<?php echo $descricao ?></td>
<td style="width:10%">R$ <?php echo $valorF ?></td>
<td style="width:12%"><?php echo $data_lancF ?></td>
<td style="width:12%"><?php echo $data_vencF ?></td>
<td style="width:12%"><?php echo $data_pgtoF ?></td>
<td style="width:17%"><?php echo $nome_usu_pgto ?></td>
<td style="width:15%"><?php echo $referencia ?></td>

    </tr>

<?php } } ?>
				</tbody>
	
			</thead>
		</table>
	


</div>
<hr>
		<table>
			<thead>
				<tbody>
					<tr>

						<td style="font-size: 10px; width:300px; text-align: right;"></td>

						

						<td style="font-size: 10px; width:70px; text-align: right;"><b>Pendentes: <span style="color:red"><?php echo $pendentes ?></span></td>

							<td style="font-size: 10px; width:70px; text-align: right;"><b>Pagas: <span style="color:green"><?php echo $pagas ?></span></td>


								<td style="font-size: 10px; width:140px; text-align: right;"><b>Pendentes: <span style="color:red">R$ <?php echo $total_pendentesF ?></span></td>

									<td style="font-size: 10px; width:120px; text-align: right;"><b>Pagas: <span style="color:green">R$ <?php echo $total_pagasF ?></span></td>
						
					</tr>
				</tbody>
			</thead>
		</table>

</body>

</html>


