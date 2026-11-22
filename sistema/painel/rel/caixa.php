<?php

$data_atual = date('Y-m-d');
$query = $pdo->query("SELECT * from usuarios where id = '$operador'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$nome_operador = @$res[0]['nome'];


$dataInicialF = implode('/', array_reverse(explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(explode('-', $dataFinal)));




$filtro_operador = "";
if ($operador == "") {
	$filtro_operador = '';
} else {
	$filtro_operador = " / OPERADOR " . mb_strtoupper($nome_operador);
}





$datas = "";
if ($dataInicial == $dataFinal) {
	$datas = $dataInicialF;
} else {
	$datas = $dataInicialF . ' à ' . $dataFinalF;
}


$texto_filtro = $datas;



?>

<!DOCTYPE html>
<html>

<head>

<style>
@page { margin: 115px 22px 55px 22px; }
body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#512c24; }
#header { position:fixed; top:-100px; left:0; right:0; height:91px; }
#footer { position:fixed; bottom:-42px; left:0; right:0; height:32px; border-top:1px solid #d7bda6; padding-top:5px; color:#806357; }
#footer .page:after { content:counter(page); }
.report-head { border:1px solid #d7bda6; background:#fffaf3; padding:8px 10px; height:67px; }
.report-head table { width:100%; border-collapse:collapse; table-layout:fixed; }
.report-head td { vertical-align:middle; border:0; }
.report-logo { width:23%; }
.report-logo img { max-width:112px; max-height:52px; }
.report-company { width:37%; font-size:8px; line-height:1.5; }
.report-title { width:40%; text-align:right; font-size:8px; line-height:1.6; }
.report-title strong { font-size:12px; white-space:nowrap; color:#512c24; }
.report-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:8px; }
.report-table th { background:#f1dfce; color:#512c24; padding:7px 4px; text-align:left; border-bottom:1px solid #d7bda6; font-size:7px; white-space:nowrap; }
.report-table td { padding:7px 4px; border-bottom:1px solid #ead9c7; vertical-align:top; overflow-wrap:break-word; }
.report-table tr { page-break-inside:avoid; }
.report-table tbody tr:nth-child(even) { background:#fffaf3; }
.report-table .money { white-space:nowrap; font-size:7px; }
.report-summary { margin-top:16px; padding:10px; background:#f1dfce; border:1px solid #d7bda6; color:#512c24; font-weight:bold; }
.report-summary span { margin-right:22px; }
.report-empty { text-align:center; padding:20px; color:#806357; }
</style>

</head>

<body>

    <?php
    // Identidade visual da empresa atual, sem logo fixa do Bora Comanda.
    $logoArquivo = null;
    foreach ([$logo_rel ?? '', $logo_sistema ?? ''] as $logoNome) {
        $logoNome = basename(trim((string)$logoNome));
        if ($logoNome === '' || $logoNome === 'sem-foto.png') continue;
        $candidate = realpath(__DIR__ . '/../../img/' . $logoNome);
        if ($candidate && is_file($candidate) && is_readable($candidate)) { $logoArquivo = $candidate; break; }
    }
    $logoData = '';
    if ($logoArquivo) {
        $mime = function_exists('mime_content_type') ? mime_content_type($logoArquivo) : '';
        if (!in_array($mime, ['image/jpeg','image/png','image/gif'], true)) {
            $ext = strtolower(pathinfo($logoArquivo, PATHINFO_EXTENSION));
            $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif'][$ext] ?? '';
        }
        if ($mime) $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoArquivo));
    }
    ?>
    <div id="header">
      <div class="report-head">
        <table><tr>
          <td class="report-logo">
            <?php if ($logoData): ?><img src="<?php echo $logoData ?>" alt="Logo da empresa">
            <?php else: ?><strong><?php echo htmlspecialchars((string)$nome_sistema, ENT_QUOTES, 'UTF-8') ?></strong><?php endif; ?>
          </td>
          <td class="report-company">
            <strong><?php echo htmlspecialchars(mb_strtoupper((string)$nome_sistema), ENT_QUOTES, 'UTF-8') ?></strong><br>
            <?php echo htmlspecialchars((string)$cnpj_sistema, ENT_QUOTES, 'UTF-8') ?><br>
            <?php echo htmlspecialchars((string)$endereco_sistema, ENT_QUOTES, 'UTF-8') ?>
          </td>
          <td class="report-title">
            <strong>RELATÓRIO DE CAIXAS</strong><br>
            <?php echo htmlspecialchars((string)$texto_filtro, ENT_QUOTES, 'UTF-8') ?><br>
            <?php echo htmlspecialchars((string)$filtro_operador, ENT_QUOTES, 'UTF-8') ?>
          </td>
        </tr></table>
      </div>
    </div>
    <div id="footer">
      <table style="width:100%;"><tr>
        <td style="text-align:left;"><?php echo htmlspecialchars((string)$nome_sistema, ENT_QUOTES, 'UTF-8') ?> — <?php echo htmlspecialchars((string)$telefone_sistema, ENT_QUOTES, 'UTF-8') ?></td>
        <td style="text-align:right;">Página <span class="page"></span></td>
      </tr></table>
    </div>

	<div id="content" style="margin-top: 0;">


		<table class="report-table">
            <thead><tr>
              <th style="width:18%">OPERADOR</th><th style="width:11%">ABERTURA</th>
              <th style="width:11%">R$ ABERTURA</th><th style="width:12%">R$ FECHAMENTO</th>
              <th style="width:10%">SANGRIAS</th><th style="width:9%">QUEBRA</th>
              <th style="width:15%">ABERTO POR</th><th style="width:14%">FECHADO POR</th>
            </tr></thead><tbody>
				<?php


				$total_caixas = 0;
				$total_quebras = 0;


				if ($operador == "") {
					$query = $pdo->query("SELECT * from caixas where data_abertura >= '$dataInicial' and data_abertura <= '$dataFinal' and empresa = '$id_empresa' ");
				} else {
					$query = $pdo->query("SELECT * from caixas where data_abertura >= '$dataInicial' and data_abertura <= '$dataFinal' and operador = '$operador' and empresa = '$id_empresa' ");
				}



				$res = $query->fetchAll(PDO::FETCH_ASSOC);

				$linhas = @count($res);

				if ($linhas > 0) {
					for ($i = 0; $i < $linhas; $i++) {
						$id = $res[$i]['id'];
						$operador = $res[$i]['operador'];
						$data_abertura = $res[$i]['data_abertura'];
						$data_fechamento = $res[$i]['data_fechamento'];
						$valor_abertura = $res[$i]['valor_abertura'];
						$valor_fechamento = $res[$i]['valor_fechamento'];
						$quebra = $res[$i]['quebra'];
						$usuario_abertura = $res[$i]['usuario_abertura'];
						$usuario_fechamento = $res[$i]['usuario_fechamento'];
						$obs = $res[$i]['obs'];

						$data_aberturaF = @implode('/', array_reverse(@explode('-', $data_abertura)));
						$data_fechamentoF = @implode('/', array_reverse(@explode('-', $data_fechamento)));
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
							$total_quebras += 1;
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
						$query2 = $pdo->query("SELECT * FROM receber where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						if (@count($res2) > 0) {
							for ($i2 = 0; $i2 < @count($res2); $i2++) {
								$valor_rec = $res2[$i2]['valor'];
								$total_recebido += $valor_rec;
							}
						}

						//totalizar saidas
						$total_saidas = 0;
						$query2 = $pdo->query("SELECT * FROM pagar where usuario_pgto = '$operador' and data_pgto >= '$data_abertura' and data_pgto <= '$data_fechamento_consulta'");
						$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
						if (@count($res2) > 0) {
							for ($i2 = 0; $i2 < @count($res2); $i2++) {
								$valor_sai = $res2[$i2]['valor'];
								$total_saidas += $valor_sai;
							}
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
						$total_sangriasF = @number_format($total_sangrias, 2, ',', '.');
						$total_caixa = $total_recebido - $total_saidas + $valor_abertura - $total_sangrias;

				?>



						<tr>

							<td><?php echo htmlspecialchars((string)$nome_operador, ENT_QUOTES, 'UTF-8') ?></td>
							<td><?php echo $data_aberturaF ?></td>
							<td class="money">R$ <?php echo $valor_aberturaF ?></td>
							<td class="money"><?php echo $valor_fechamentoF === '—' ? '—' : 'R$ ' . $valor_fechamentoF ?></td>
							<td class="money">R$ <?php echo $total_sangriasF ?></td>
							<td class="money" style="color:<?php echo $classe_quebra ?>">R$ <?php echo $quebraF ?></td>
							<td><?php echo htmlspecialchars((string)$nome_usu_abertura, ENT_QUOTES, 'UTF-8') ?></td>
							<td><?php echo htmlspecialchars((string)$nome_usu_fechamento, ENT_QUOTES, 'UTF-8') ?></td>


						</tr>


				<?php }
				} ?>
            </tbody>
        </table>


	</div>
    <div class="report-summary">
      <span>CAIXAS: <?php echo (int)$linhas ?></span>
      <span>QUEBRAS: <?php echo (int)$total_quebras ?></span>
    </div>

</body>

</html>