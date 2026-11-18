<?php
$dataInicialF = implode('/', array_reverse(@explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(@explode('-', $dataFinal)));

$filtro_lancamentoF = 'PAGAMENTOS';
$filtro_tabela = 'fornecedores';
$filtro_tipoF = "";
if ($filtro_tipo == "receber") {
	$filtro_tipoF = 'ENTRADAS / GANHOS';
	$classe_entradas = 'green';
} else {
	$filtro_tipoF = 'SAÍDAS / DESPESAS';
	$classe_entradas = 'red';
}


$filtro_dataF = "";
if ($filtro_data == "data_lanc") {
	$filtro_dataF = 'DATA DE LANÇAMENTO';
} else if ($filtro_data == "data_venc") {
	$filtro_dataF = 'DATA DE VENCIMENTO';
} else {
	$filtro_dataF = "DATA DE PAGAMENTO";
}


if ($filtro_lancamento == 'fornecedor') {
	$filtro_lancamentoF = 'Pagamentos Fornecedores';
	$filtro_tabela = 'fornecedores';
}

if ($filtro_lancamento == 'funcionario') {
	$filtro_lancamentoF = 'Pagamentos Funcionários';
	$filtro_tabela = 'usuarios';
}


$filtro_pendentesF = "";
if ($filtro_pendentes == "Não") {
	$filtro_pendentesF = 'PENDENTES';
} else if ($filtro_pendentes == "Sim") {
	$filtro_pendentesF = 'PAGAS';
} else {
	$filtro_pendentesF = "";
}


$datas = "";
if ($dataInicial == $dataFinal) {
	$datas = $dataInicialF;
} else {
	$datas = $dataInicialF . ' à ' . $dataFinalF;
}

$texto_filtro = $filtro_dataF . ' : ' . $datas . ' ' . $filtro_pendentesF;



?>
<!DOCTYPE html>
<html>

<head>

	<style>
		@import url('https://fonts.cdnfonts.com/css/tw-cen-mt-condensed');

		@page {
			margin: 145px 20px 25px 20px;
		}

		#header {
			position: fixed;
			left: 0px;
			top: -110px;
			bottom: 100px;
			right: 0px;
			height: 35px;
			text-align: center;
			padding-bottom: 100px;
		}

		#content {
			margin-top: 0px;
		}

		#footer {
			position: fixed;
			left: 0px;
			bottom: -60px;
			right: 0px;
			height: 80px;
		}

		#footer .page:after {
			content: counter(page, my-sec-counter);
		}

		body {
			font-family: 'Tw Cen MT', sans-serif;
		}

		.marca {
			position: fixed;
			left: 50;
			top: 100;
			width: 80%;
			opacity: 8%;
		}
	
/* Bora Comanda: impressão sintética em A4 */
@page { size: A4 portrait; margin: 18mm 12mm 17mm 12mm; }
body { font-family: DejaVu Sans, sans-serif !important; color: #263238; font-size: 8px; margin: 0; }
#header { position: static !important; height: auto !important; bottom: auto !important; padding: 0 !important; margin: 0 0 12px !important; text-align: left !important; }
#header > div:first-child { border: none !important; border-bottom: 2px solid #475569 !important; height: auto !important; min-height: 75px; padding-bottom: 10px; }
#header > div:first-child table { width: 100%; border-collapse: collapse; table-layout: fixed; }
#header > div:first-child td { border: none !important; vertical-align: middle; line-height: 1.5; overflow-wrap: break-word; }
#header > div:first-child td:first-child { width: 19% !important; text-align: center !important; }
#header > div:first-child td:nth-child(2) { width: 46% !important; text-align: left !important; }
#header > div:first-child td:last-child { width: 35% !important; text-align: right !important; }
#header img#imag { max-width: 95px !important; max-height: 60px !important; width: auto !important; height: auto !important; margin: 0 !important; }
#header big { font-size: 12px; color: #172b4d; font-weight: bold; }
#content { margin: 0 !important; }
#relatorio-dados { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8px !important; }
#relatorio-dados thead { display: table-header-group; }
#relatorio-dados th { background: #e9eef5; padding: 8px 4px; border-bottom: 1px solid #94a3b8; text-align: left; font-weight: bold; }
#relatorio-dados td { padding: 8px 4px; border-bottom: 1px solid #e2e8f0; vertical-align: top; overflow-wrap: break-word; }
#relatorio-dados tr { page-break-inside: avoid; }
#relatorio-dados tbody tr:nth-child(even) { background: #f8fafc; }
#footer { bottom: -11mm !important; height: 20px !important; border-top: 1px solid #cbd5e1; color: #64748b; }
#footer hr { display: none; }
.marca { opacity: .07 !important; }

</style>

</head>

<body>
	<?php
	if ($marca_dagua == 'Sim' and $tipo_rel != 'HTML') { ?>
		<img class="marca" src="<?php echo $url_sistema ?>sistema/img/<?php echo $logo_sistema ?>">
	<?php } ?>


	<div id="header">

		<div style="border-style: solid; font-size: 10px; height: 55px;">
			<table style="width: 100%; border: 0px solid #ccc;">
				<tr>
					<td style="width:19%; text-align:center;">
						<img style="margin-top: 0px; margin-left: 7px;" id="imag"
							src="<?php echo $url_sistema ?>sistema/img/<?php echo $logo_sistema ?>" style="max-width:95px; max-height:60px; width:auto; height:auto;">
					</td>

					<td style="text-align:left; font-size:9px; width:46%;">

						<b><?php echo mb_strtoupper($nome_sistema) ?></b><br>
						<b>CNPJ: </b><?php echo mb_strtoupper($cnpj_sistema) ?><br>
						<b>INSTAGRAM:</b> <?php echo mb_strtoupper($instagram_sistema) ?><br>
						<?php echo mb_strtoupper($endereco_sistema) ?>

					</td>
					<td style="width: 30%; text-align: right; font-size: 9px;padding-right: 10px;">
						<b><big>RELATÓRIO DE <?php echo mb_strtoupper($filtro_lancamentoF) ?></big></b><br> <?php echo mb_strtoupper($texto_filtro) ?> <br> <?php echo mb_strtoupper($data_hoje) ?>
					</td>
				</tr>
			</table>
		</div>

		<br>


		
	</div>

	<div id="footer" class="row">
		<hr style="margin-bottom: -10;">
		<table style="width:100%;">
			<tr style="width:100%;">
				<td style="width:60%; font-size: 10px; text-align: left;"><?php echo $nome_sistema ?> Telefone: <?php echo $telefone_sistema ?></td>
				<td style="width:40%; font-size: 10px; text-align: right;">
					<p class="page">Página </p>
				</td>
			</tr>
		</table>
	</div>

	<div id="content" style="margin-top: 0;">



		<table id="relatorio-dados" style="width: 100%; table-layout: fixed; font-size:9px; text-transform: uppercase;">
			<thead><tr  style="margin-left: 0px; background-color:#CCC">
					<th style="width:30%">NOME</th>
					<th style="width:10%">VALOR TOTAL</th>
					<th style="width:10%">PENDENTES</th>
					<th style="width:10%">PAGAS</th>
					<th style="width:10%">CONTAS</th>
					<th style="width:30%">CHAVE PIX</th>


				</tr></thead>
			<tbody>
				<?php


					$query9 = $pdo->query("SELECT * from $filtro_tabela where empresa = '$id_empresa' order by nome asc");
				$res9 = $query9->fetchAll(PDO::FETCH_ASSOC);
				$linhas9 = @count($res9);
				if ($linhas9 > 0) {
					for ($i9 = 0; $i9 < $linhas9; $i9++) {
						$id_pessoa = $res9[$i9]['id'];
						$nome_pessoa = $res9[$i9]['nome'];
						$pix_pessoa = $res9[$i9]['pix'];


						$total_valor = 0;
						$total_valorF = 0;
						$total_pendentes = 0;
						$total_pendentesF = 0;
						$total_pagas = 0;
						$total_pagasF = 0;
						$pendentes = 0;
						$pagas = 0;

						$query = $pdo->query("SELECT * from $filtro_tipo where $filtro_data >= '$dataInicial' and $filtro_data <= '$dataFinal' and pago LIKE '%$filtro_pendentes%' and $filtro_lancamento = '$id_pessoa' and empresa = '$id_empresa' order by $filtro_data asc");
						$res = $query->fetchAll(PDO::FETCH_ASSOC);
						$linhas = @count($res);
						if ($linhas > 0) {
							for ($i = 0; $i < $linhas; $i++) {
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
								if (@count($res2) > 0) {
									$nome_usu_pgto = $res2[0]['nome'];
								} else {
									$nome_usu_pgto = '';
								}


								if ($pago == 'Sim') {
									$classe_pago = 'verde.jpg';
									$total_pagas += $valor;
									$pagas += 1;
								} else {
									$classe_pago = 'vermelho.jpg';
									$total_pendentes += $valor;
									$pendentes += 1;
								}

								$total_valor += $valor;
								$total_valorF = @number_format($total_valor, 2, ',', '.');
								$total_pagasF = @number_format($total_pagas, 2, ',', '.');
								$total_pendentesF = @number_format($total_pendentes, 2, ',', '.');


								if ($data_pgtoF == '00/00/0000') {
									$data_pgtoF = 'Pendente';
								}
							}
						} else {
							//continue para ele pular para o proximo
							continue;
							//se usar break ele para toda instrução
						}
				?>


						<tr>
							<td style="width:30%">
								<?php echo $nome_pessoa ?></td>
							<td style="width:10%">R$ <?php echo $total_valorF ?></td>
							<td style="width:10%; color:red">R$ <?php echo $total_pendentesF ?></td>
							<td style="width:10%; color:green">R$ <?php echo $total_pagasF ?></td>
							<td style="width:10%"><?php echo $linhas ?></td>
							<td style="width:30%"><?php echo $pix_pessoa ?></td>
						</tr>

				<?php }
				}  ?>
			</tbody>
</table>
</div>

</body>

</html>