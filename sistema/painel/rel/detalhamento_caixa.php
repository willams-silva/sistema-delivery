<?php
$data_atual = date('Y-m-d');

$query = $pdo->query("SELECT * from caixas where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);

if ($linhas == 0) {

	echo 'Caixa não encontrado!';

	exit();
} else {

	$operador = $res[0]['operador'];
	$data_abertura = $res[0]['data_abertura'];
	$data_fechamento = $res[0]['data_fechamento'];
	$valor_abertura = $res[0]['valor_abertura'];
	$valor_fechamento = $res[0]['valor_fechamento'];
	$quebra = $res[0]['quebra'];
	$usuario_abertura = $res[0]['usuario_abertura'];
	$usuario_fechamento = $res[0]['usuario_fechamento'];
	$obs = $res[0]['obs'];

	$data_aberturaF = @implode('/', array_reverse(@explode('-', $data_abertura)));

	$data_fechamentoF = $data_fechamento ? @implode('/', array_reverse(@explode('-', $data_fechamento))) : 'Em aberto';

	$valor_aberturaF = @number_format($valor_abertura, 2, ',', '.');
	$valor_fechamentoF = $valor_fechamento === null || $valor_fechamento === '' ? '—' : @number_format($valor_fechamento, 2, ',', '.');
	$quebraF = @number_format($quebra, 2, ',', '.');



	$query2 = $pdo->query("SELECT * FROM usuarios where id = '$operador'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res2) > 0) {
		$nome_operador = $res2[0]['nome'];
	} else {

		$nome_operador = '';
	}


	$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_abertura'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res2) > 0) {
		$nome_usu_abertura = $res2[0]['nome'];
	} else {
		$nome_usu_abertura = '';
	}




	$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_fechamento'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res2) > 0) {
		$nome_usu_fechamento = $res2[0]['nome'];
	} else {
		$nome_usu_fechamento = '';
	}



	if ($valor_fechamento == '') {
		$classe_aberto = 'red';
		$ocultar = '';
	} else {
		$classe_aberto = 'green';
		$ocultar = 'ocultar';
	}

	if ($quebra != 0) {
		$classe_quebra = 'red';
	} else {
		$classe_quebra = 'green';
	}



	if ($data_fechamento == "") {
		$data_fechamento_consulta = $data_atual;
	} else {
		$data_fechamento_consulta = $data_fechamento;
	}


	//buscar total movimentado pelo caixa
	//totalizar recebimentos
	$total_recebido = 0;
	$query2 = $pdo->query("SELECT * FROM receber where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta' and caixa = '$id'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res2) > 0) {
		for ($i2 = 0; $i2 < @count($res2); $i2++) {
			$valor_rec = $res2[$i2]['valor'];
			$total_recebido += $valor_rec;
		}
	}



	//totalizar saidas
	$total_saidas = 0;
	$query2 = $pdo->query("SELECT * FROM pagar where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta' and caixa = '$id'");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	if (@count($res2) > 0) {
		for ($i2 = 0; $i2 < @count($res2); $i2++) {
			$valor_sai = $res2[$i2]['valor'];
			$total_saidas += $valor_sai;
		}
	}




	$total_saidasF = @number_format($total_saidas, 2, ',', '.');
	$total_recebidoF = @number_format($total_recebido, 2, ',', '.');
}




//totalizar sangrias
$total_sangrias = 0;
$query2 = $pdo->query("SELECT * FROM sangrias where caixa = '$id'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if (@count($res2) > 0) {
	for ($i2 = 0; $i2 < @count($res2); $i2++) {
		$valor_s = $res2[$i2]['valor'];
		$total_sangrias += $valor_s;
	}
}


$total_caixa = $total_recebido - $total_saidas + $valor_abertura - $total_sangrias;

$total_caixaF = @number_format($total_caixa, 2, ',', '.');


$total_sangriasF = @number_format($total_sangrias, 2, ',', '.');



$texto_filtro = 'OPERADOR: ' . $nome_operador;


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
	
        /* Relatorio PDF A4: tipografia e tabelas consistentes no Dompdf. */
        @page { margin: 118px 26px 64px 26px; }
        body { font-family: DejaVu Sans, sans-serif; color:#42251f; font-size:10px; }
        #header { top:-100px; left:0; right:0; height:86px; bottom:auto; padding:0; text-align:left; }
        #header > div:first-child { border:1px solid #d7bda6!important; border-radius:5px; height:69px!important; padding:4px; background:#fffaf4; }
        #header table { border-collapse:collapse; }
        #header table td { vertical-align:middle; }
        #header table td:first-child { border:0!important; }
        #content { margin-top:0!important; }
        #footer { bottom:-47px; height:37px; left:0; right:0; color:#806357; }
        #footer table { border-collapse:collapse; }
        #footer td { font-size:8px!important; }
        table#cabecalhotabela { border-collapse:collapse!important; width:100%!important; table-layout:fixed; margin-bottom:12px!important; border:1px solid #e2cdbb!important; }
        table#cabecalhotabela tr:first-child { background:#f1dfce!important; }
        table#cabecalhotabela td { padding:6px 5px!important; vertical-align:top!important; border-bottom:1px solid #e8d9cc; overflow-wrap:break-word; word-wrap:break-word; }
        table#cabecalhotabela tr:first-child td { color:#512c24; font-weight:bold; }
        table#cabecalhotabela tr { page-break-inside:avoid; }
        #header > div[style*="border-bottom"], #content > div[style*="border-bottom"] { border-color:#b87542!important; }
        .marca { left:12%; top:24%; width:75%; opacity:0.06; }
        .report-section-title { margin:19px 0 8px; padding:7px 9px; background:#512c24; color:#fffaf3; font-size:11px; font-weight:bold; }
        .report-section-title span { color:#fffaf3!important; }
        .report-total { margin-top:18px; padding:11px; border:1px solid #d7bda6; background:#f1dfce; color:#512c24; font-weight:bold; font-size:13px; }
        .report-total span { color:#512c24!important; }
        .report-note { padding:8px; border:1px solid #e2cdbb; background:#fffaf4; margin:8px 0; font-size:10px; }
        .report-signature { margin-top:35px; text-align:center; font-size:10px; }
        .report-signature-line { border-top:1px solid #876458; width:260px; margin:0 auto 6px; }
            /* Cabecalho: manter logo e titulo inteiramente dentro da borda. */
        #header { height:82px!important; }
        #header .report-header-box {
            height:70px!important; padding:7px 12px!important;
            border:1px solid #d7bda6!important; border-radius:5px;
            background:#fffaf4; box-sizing:border-box;
        }
        #header .report-header-table { width:100%; height:54px; table-layout:fixed; border:0; }
        #header .report-header-logo { width:32%; vertical-align:middle; text-align:left; padding:0; border:0!important; }
        #header .report-header-logo img { display:block; max-width:115px; max-height:48px; width:auto; height:auto; }
        #header .report-header-info { width:68%; text-align:right; vertical-align:middle; padding:0 2px 0 8px; border:0!important; font-size:9px; line-height:1.55; }
        #header .report-header-title { font-size:13px; font-weight:bold; color:#512c24; margin-bottom:3px; }
        /* Nomes e titulos em linha unica, com espaco para caber no A4. */
        #header .report-header-info { width:75%!important; white-space:nowrap!important; font-size:8px!important; }
        #header .report-header-logo { width:25%!important; }
        #header .report-header-title { white-space:nowrap!important; font-size:12px!important; }
        #header .report-header-info div { white-space:nowrap!important; }
        table#cabecalhotabela tr:first-child td {
            white-space:nowrap!important;
            font-size:8px!important;
            padding:6px 3px!important;
        }
        table#cabecalhotabela td small { font-size:8px!important; }
        table#cabecalhotabela:first-of-type td { font-size:9px; }
</style>



</head>

<body>

	<?php

	if ($marca_dagua == 'Sim') { ?>
		<?php /* Marca d'agua global removida: nunca exibir logo de outra empresa. */ ?>
	<?php } ?>





	<div id="header">
        <div class="report-header-box">
            <table class="report-header-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="report-header-logo">
                        <?php
                        // Logo do cliente atual, carregada da configuracao da empresa.
                        // Nao utilizar a logo global do Bora Comanda.
                        $logoArquivo = null;
                        foreach ([$logo_rel ?? '', $logo_sistema ?? ''] as $logoNome) {
                            $logoNome = basename(trim((string)$logoNome));
                            if ($logoNome === '' || $logoNome === 'sem-foto.png') continue;
                            $candidato = realpath(__DIR__ . '/../../img/' . $logoNome);
                            if ($candidato && is_file($candidato) && is_readable($candidato)) {
                                $logoArquivo = $candidato;
                                break;
                            }
                        }
                        if ($logoArquivo) {
                            $tipoImagem = function_exists('mime_content_type') ? mime_content_type($logoArquivo) : '';
                            if (!in_array($tipoImagem, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
                                $extensao = strtolower(pathinfo($logoArquivo, PATHINFO_EXTENSION));
                                $tipoImagem = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'][$extensao] ?? '';
                            }
                            if ($tipoImagem) {
                                echo '<img src="data:' . $tipoImagem . ';base64,' . base64_encode(file_get_contents($logoArquivo)) . '" alt="Logo da empresa" />';
                            } else {
                                echo '<strong>' . htmlspecialchars((string)$nome_sistema, ENT_QUOTES, 'UTF-8') . '</strong>';
                            }
                        } else {
                            echo '<strong>' . htmlspecialchars((string)$nome_sistema, ENT_QUOTES, 'UTF-8') . '</strong>';
                        }
                        ?>
                    </td>
                    <td class="report-header-info">
                        <div class="report-header-title">DETALHAMENTO CAIXA</div>
                        <div><strong>OPERADOR:</strong> <?php echo htmlspecialchars(mb_strtoupper((string)$nome_operador), ENT_QUOTES, 'UTF-8'); ?></div>
                        <div><?php echo htmlspecialchars(mb_strtoupper((string)$data_hoje), ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>




		<table id="cabecalhotabela" style="border-bottom-style: solid; font-size: 12px; margin-bottom:10px; width: 100%; table-layout: fixed;">

			<thead>



				<tr id="cabeca" style="margin-left: 0px; background-color:#CCC; font-weight: bold">

					<td style="width:27%">ABERTURA</td>
					<td style="width:27%">FECHAMENTO</td>
					<td style="width:16%">R$ ABERTURA</td>
					<td style="width:16%">R$ FECHAMENTO</td>
					<td style="width:14%">QUEBRA</td>

				</tr>



				<tr id="cabeca" style="margin-left: 0px;">
					<td style="width:27%"><?php echo $data_aberturaF ?> <small>(<?php echo $nome_usu_abertura ?>)</small></td>
					<td style="width:27%"><?php echo $data_fechamentoF ?> <small>(<?php echo $nome_usu_fechamento ?>)</small></td>
					<td style="width:16%">R$ <?php echo $valor_aberturaF ?></td>
					<td style="width:16%"><?php echo $valor_fechamentoF === '—' ? '—' : 'R$ ' . $valor_fechamentoF ?></td>
					<td style="width:14%; color:<?php echo $classe_quebra ?>">R$ <?php echo $quebraF ?></td>

				</tr>
			</thead>
		</table>




		<?php if ($obs != "") { ?>
			<table><tbody>
					<tr>
						<td class="report-note" style="text-align:left"><b>Observações:</b> <?php echo htmlspecialchars((string)$obs, ENT_QUOTES, 'UTF-8') ?></td>

					</tr>
				</tbody></table>

		<?php } ?>




		<?php if ($total_recebido > 0 || $total_saidas > 0) { ?>

			<div class="report-section-title">

				<div style="font-size: 11px; margin-bottom: 7px"><b>DETALHAMENTO DE ENTRADAS / TOTAL <span style="color:green">R$ <?php echo $total_recebidoF ?></span></b></div>

			</div>

			<table id="cabecalhotabela" style="border-bottom-style: solid; font-size: 11px; margin-bottom:10px; width: 100%; table-layout: fixed; margin-top: 5px">

				<thead>



					<tr id="cabeca" style="margin-left: 0px; background-color:#CCC; font-weight: bold">

						<?php
						$query2 = $pdo->query("SELECT * FROM formas_pgto order by id asc");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						if (@count($res2) > 0) {
							for ($i2 = 0; $i2 < @count($res2); $i2++) {
								$nome_pgto = $res2[$i2]['nome'];

								echo '<td style="width:20%">' . $nome_pgto . '</td>';
							}
						}
						?>

					</tr>



					<tr id="cabeca" style="margin-left: 0px;">
						<?php
						//PEGAR O ID DA FORMA DE PAGAMENTO
						$query2 = $pdo->query("SELECT * FROM formas_pgto order by id asc");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						if (@count($res2) > 0) {
							for ($i2 = 0; $i2 < @count($res2); $i2++) {
								$id_pgto = $res2[$i2]['id'];

								//TOTAL DE ENTRADAS POR ESSA FORMA DE PGTO
								$total_recebido_pgto = 0;
								$query3 = $pdo->query("SELECT * FROM receber where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta' and forma_pgto = '$id_pgto' and caixa = '$id'");
								$res3 = $query3->fetchAll(PDO::FETCH_ASSOC);

								if (@count($res3) > 0) {
									for ($i3 = 0; $i3 < @count($res3); $i3++) {
										$valor_rec_pgto = $res3[$i3]['valor'];
										$total_recebido_pgto += $valor_rec_pgto;
									}
								}

								$total_recebido_pgtoF = @number_format($total_recebido_pgto, 2, ',', '.');

								echo '<td style="width:20%; color:green">R$ ' . $total_recebido_pgtoF . '</td>';
							}
						}
						?>

					</tr>
				</thead>
			</table>



			<table id="cabecalhotabela" style="width: 100%; table-layout: fixed; font-size:9px; text-transform: uppercase;">

				<thead>

					<tr id="cabeca" style="margin-left: 0px; background-color:#CCC; font-weight: bold">

						<td style="width:25%">DESCRIÇÃO</td>
						<td style="width:9%">REFERÊNCIA</td>
						<td style="width:10%">VALOR</td>
						<td style="width:9%">DATA</td>
						<td style="width:9%">HORA</td>
						<td style="width:20%">CLIENTE</td>
						<td style="width:17%">FORMA PGTO</td>


					</tr>



					<?php

					$total_servicos = 0;
					$total_consumo = 0;
					$total_final = 0;
					$total_servicosF = 0;
					$total_consumoF = 0;
					$total_finalF = 0;

					$query = $pdo->query("SELECT * from receber where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta' and caixa = '$id' ORDER BY id ASC");
					$res = $query->fetchAll(PDO::FETCH_ASSOC);
					$linhas = @count($res);
					if ($linhas > 0) {
						for ($i = 0; $i < $linhas; $i++) {

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
							$cliente = $res[$i]['cliente'];
							$hora = $res[$i]['hora'];
							$referencia = $res[$i]['referencia'];
							$forma_pgto = $res[$i]['forma_pgto'];



							$forma_pgtoF = $forma_pgto;

							$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));
							$data_vencF = implode('/', array_reverse(@explode('-', $data_venc)));
							$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
							$valorF = @number_format($valor, 2, ',', '.');

							$horaF = date("H:i", @strtotime($hora));


							$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_usu_lanc = $res2[0]['nome'];
							} else {
								$nome_usu_lanc = 'Sem Usuário';
							}

							$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_cliente = $res2[0]['nome'];
							} else {
								$nome_cliente = 'Sem Registro';
							}



							if ($referencia == 'Venda') {
								$total_consumo += $valor;
							} else {
								$total_servicos += $valor;
							}


							$total_final = $total_consumo + $total_servicos;


							$total_consumoF = @number_format($total_consumo, 2, ',', '.');
							$total_servicosF = @number_format($total_servicos, 2, ',', '.');
							$total_finalF = @number_format($total_final, 2, ',', '.');


							$query2 = $pdo->query("SELECT * FROM formas_pgto WHERE id = '$forma_pgto'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_do_pgto = $res2[0]['nome'];
							}


					?>


							<tr>

								<td style="width:25%"><?php echo $descricao ?></td>
								<td style="width:9%;"><?php echo $referencia ?></td>
								<td style="width:10%; color:green">R$ <?php echo $valorF ?></td>
								<td style="width:9%"><?php echo $data_pgtoF ?></td>
								<td style="width:9%; "><?php echo $horaF ?></td>
								<td style="width:20%; "><?php echo $nome_cliente ?></td>
								<td style="width:17%"><?php echo @$nome_do_pgto ?></td>



							</tr>



					<?php }
					} ?>


				</thead>

			</table>





			<div class="report-section-title">

				<div style="font-size: 11px; margin-bottom: 7px"><b>DETALHAMENTO DE SAÍDAS / TOTAL <span style="color:red">R$ <?php echo $total_saidasF ?></span></b></div>

			</div>

			<table id="cabecalhotabela" style="border-bottom-style: solid; font-size: 11px; margin-bottom:10px; width: 100%; table-layout: fixed; margin-top: 5px">

				<thead>



					<tr id="cabeca" style="margin-left: 0px; background-color:#CCC; font-weight: bold">

						<?php
						$query2 = $pdo->query("SELECT * FROM formas_pgto order by id asc");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						if (@count($res2) > 0) {
							for ($i2 = 0; $i2 < @count($res2); $i2++) {
								$nome_pgto = $res2[$i2]['nome'];

								echo '<td style="width:20%">' . $nome_pgto . '</td>';
							}
						}
						?>

					</tr>



					<tr id="cabeca" style="margin-left: 0px;">

						<?php
						$query2 = $pdo->query("SELECT * FROM formas_pgto order by id asc");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						if (@count($res2) > 0) {
							for ($i2 = 0; $i2 < @count($res2); $i2++) {
								$id_pgto = $res2[$i2]['id'];


								//total de entradas por essa forma de pgto	
								$total_recebido_pgto = 0;
								
								$query4 = $pdo->query("SELECT * FROM pagar where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta' and forma_pgto = '$id_pgto' and caixa = '$id'");
								$res4 = $query4->fetchAll(PDO::FETCH_ASSOC);
								
								if (@count($res4) > 0) {
									for ($i4 = 0; $i4 < @count($res4); $i4++) {
										$valor_rec_pgto = $res4[$i4]['valor'];
										$total_recebido_pgto += $valor_rec_pgto;
									}

								}

								$total_recebido_pgtoF = @number_format($total_recebido_pgto, 2, ',', '.');


								echo '<td style="width:20%; color:red">R$ ' . $total_recebido_pgtoF . '</td>';
							}
						}


						?>

					</tr>

				</thead>

			</table>





			<table id="cabecalhotabela" style="width: 100%; table-layout: fixed; font-size:9px; text-transform: uppercase;">

				<thead>



					<tr id="cabeca" style="margin-left: 0px; background-color:#CCC; font-weight: bold">

						<td style="width:25%">DESCRIÇÃO</td>
						<td style="width:9%">REFERÊNCIA</td>
						<td style="width:10%">VALOR</td>
						<td style="width:9%">DATA</td>
						<td style="width:9%">HORA</td>
						<td style="width:20%">PESSOA</td>
						<td style="width:17%">FORMA PGTO</td>





					</tr>



					<?php

					$total_servicos = 0;
					$total_consumo = 0;
					$total_final = 0;
					$total_servicosF = 0;
					$total_consumoF = 0;
					$total_finalF = 0;

					$query = $pdo->query("SELECT * from pagar where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta' and caixa = '$id' ORDER BY ID ASC");

					$res = $query->fetchAll(PDO::FETCH_ASSOC);
					$linhas = @count($res);
					if ($linhas > 0) {
						for ($i = 0; $i < $linhas; $i++) {


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
							$funcionario = $res[$i]['funcionario'];
							$fornecedor = $res[$i]['fornecedor'];
							$hora = $res[$i]['hora'];
							$referencia = $res[$i]['referencia'];
							$forma_pgto = $res[$i]['forma_pgto'];

							$forma_pgtoF = $forma_pgto;


							$data_lancF = implode('/', array_reverse(@explode('-', $data_lanc)));
							$data_vencF = implode('/', array_reverse(@explode('-', $data_venc)));
							$data_pgtoF = implode('/', array_reverse(@explode('-', $data_pgto)));
							$valorF = @number_format($valor, 2, ',', '.');

							$horaF = date("H:i", @strtotime($hora));


							$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_lanc'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);

							if (@count($res2) > 0) {
								$nome_usu_lanc = $res2[0]['nome'];
							} else {
								$nome_usu_lanc = 'Sem Usuário';
							}



							if ($referencia == 'Venda') {
								$total_consumo += $valor;
							} else {
								$total_servicos += $valor;
							}



							$total_final = $total_consumo + $total_servicos;

							$total_consumoF = @number_format($total_consumo, 2, ',', '.');
							$total_servicosF = @number_format($total_servicos, 2, ',', '.');
							$total_finalF = @number_format($total_final, 2, ',', '.');

							$nome_pessoa = 'Sem Registro';
							$tipo_pessoa = 'Pessoa';
							$pix_pessoa = 'Sem Registro';


							$query2 = $pdo->query("SELECT * FROM fornecedores where id = '$fornecedor'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_pessoa = $res2[0]['nome'];
								$pix_pessoa = $res2[0]['pix'];
								$tipo_pessoa = 'Fornecedor';
							}


							$query2 = $pdo->query("SELECT * FROM usuarios where id = '$funcionario'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_pessoa = @$res2[0]['nome'];
								$pix_pessoa = @$res2[0]['chave_pix'];
								$tipo_pessoa = 'Funcionário';
							}


							$query2 = $pdo->query("SELECT * FROM formas_pgto WHERE id = '$forma_pgto'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_do_pgto = $res2[0]['nome'];
							}



					?>





							<tr>

								<td style="width:25%"><?php echo $descricao ?></td>
								<td style="width:9%;"><?php echo $referencia  ?> </td>
								<td style="width:10%; color:red">R$ <?php echo $valorF ?></td>
								<td style="width:9%"><?php echo $data_pgtoF ?></td>
								<td style="width:9%; "><?php echo $horaF ?></td>
								<td style="width:20%; "><?php echo $nome_pessoa ?></td>
								<td style="width:17%"><?php echo $nome_do_pgto ?></td>

							</tr>



					<?php }
					} ?>





				</thead>

			</table>




		<?php } ?>









		<?php if ($total_sangrias > 0) { ?>

			<div class="report-section-title">

				<div style="font-size: 11px; margin-bottom: 7px"><b>DETALHAMENTO DE SANGRIAS / TOTAL <span style="color:red">R$ <?php echo $total_sangriasF ?></span></b></div>

			</div>




			<table id="cabecalhotabela" style="width: 100%; table-layout: fixed; font-size:9px; text-transform: uppercase;">

				<thead>



					<tr id="cabeca" style="margin-left: 0px; background-color:#CCC; font-weight: bold">
						<td style="width:20%">VALOR</td>
						<td style="width:40%">FEITA POR</td>
						<td style="width:20%">DATA</td>
						<td style="width:20%">HORA</td>

					</tr>


					<?php

					$query = $pdo->query("SELECT * from sangrias where caixa = '$id' ORDER BY ID ASC");
					$res = $query->fetchAll(PDO::FETCH_ASSOC);
					$linhas = @count($res);
					if ($linhas > 0) {
						for ($i = 0; $i < $linhas; $i++) {

							$valor = $res[$i]['valor'];
							$usuario = $res[$i]['usuario'];
							$data = $res[$i]['data'];
							$hora = $res[$i]['hora'];
							$feito_por = $res[$i]['feito_por'];


							$dataF = implode('/', array_reverse(@explode('-', $data)));
							$valorF = @number_format($valor, 2, ',', '.');
							$horaF = date("H:i", @strtotime($hora));

							$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_usu_lanc = $res2[0]['nome'];
							} else {
								$nome_usu_lanc = 'Sem Usuário';
							}


							$query2 = $pdo->query("SELECT * FROM usuarios where id = '$feito_por'");
							$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
							if (@count($res2) > 0) {
								$nome_usu_feito = $res2[0]['nome'];
							} else {
								$nome_usu_feito = 'Sem Usuário';
							}


					?>





							<tr>

								<td style="width:20%; color:red">R$ <?php echo $valorF ?></td>
								<td style="width:40%"><?php echo $nome_usu_feito ?></td>
								<td style="width:20%; "><?php echo $dataF ?></td>
								<td style="width:20%; "><?php echo $horaF ?></td>





							</tr>



					<?php }
					} ?>





				</thead>

			</table>




		<?php } ?>




		<hr>

		<div class="report-section-title">

			<div class="report-total"><b>VALOR FINAL / TOTAL <span style="color:green">R$ <?php echo $total_caixaF ?></span></b></div>

		</div>



		<br><br>
		<div class="report-signature"><div class="report-signature-line"></div>Assinatura do responsável</div>


	</div>




	<div id="footer" class="row">
		<hr style="margin-bottom: 0;">
		<table style="width:100%;">
			<tr style="width:100%;">
				<td style="width:50%; font-size: 10px; text-align: left;"><?php echo $nome_sistema ?> Telefone: <?php echo $telefone_sistema ?></td>

				<td style="width:50%; font-size: 10px; text-align: right;"> Endereço: <?php echo $endereco_sistema ?></td>

			</tr>

		</table>

	</div>


</body>

</html>