<?php

$data_atual = date('Y-m-d');
$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual . "-" . $mes_atual . "-01";
$data_inicio_ano = $ano_atual . "-01-01";




//PEGAR DADOS DO MES JANEIRO
$classe_janeiro = '#363636';
$pagar_janeiro = 0;
$receber_janeiro = 0;
$saldo_janeiro = 0;
$data_inicio_mes = $ano_atual . "-01-01";
$data_final_mes = $ano_atual . "-01-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_janeiro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'  and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_janeiro += $res[$i]['subtotal'];
	}
}
$saldo_janeiro = $receber_janeiro - $pagar_janeiro;
$pagar_janeiroF = @number_format($pagar_janeiro, 2, ',', '.');
$receber_janeiroF = @number_format($receber_janeiro, 2, ',', '.');
$saldo_janeiroF = @number_format($saldo_janeiro, 2, ',', '.');
if ($saldo_janeiro > 0) {
	$classe_janeiro = 'green';
} else if ($saldo_janeiro < 0) {
	$classe_janeiro = 'red';
}




//PEGAR DADOS DO MES fevereiro
$classe_fevereiro = '#363636';
$pagar_fevereiro = 0;
$receber_fevereiro = 0;
$saldo_fevereiro = 0;
$data_inicio_mes = $ano_atual . "-02-01";

$bissexto = date('L', @mktime(0, 0, 0, 1, 1, $ano_atual));
if ($bissexto == 1) {
	$data_final_mes = $ano_atual . '-02-29';
} else {
	$data_final_mes = $ano_atual . '-02-28';
}

$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'  and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_fevereiro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_fevereiro += $res[$i]['subtotal'];
	}
}
$saldo_fevereiro = $receber_fevereiro - $pagar_fevereiro;
$pagar_fevereiroF = @number_format($pagar_fevereiro, 2, ',', '.');
$receber_fevereiroF = @number_format($receber_fevereiro, 2, ',', '.');
$saldo_fevereiroF = @number_format($saldo_fevereiro, 2, ',', '.');
if ($saldo_fevereiro > 0) {
	$classe_fevereiro = 'green';
} else if ($saldo_fevereiro < 0) {
	$classe_fevereiro = 'red';
}




//PEGAR DADOS DO MES marco
$classe_marco = '#363636';
$pagar_marco = 0;
$receber_marco = 0;
$saldo_marco = 0;
$data_inicio_mes = $ano_atual . "-03-01";
$data_final_mes = $ano_atual . "-03-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_marco += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_marco += $res[$i]['subtotal'];
	}
}
$saldo_marco = $receber_marco - $pagar_marco;
$pagar_marcoF = @number_format($pagar_marco, 2, ',', '.');
$receber_marcoF = @number_format($receber_marco, 2, ',', '.');
$saldo_marcoF = @number_format($saldo_marco, 2, ',', '.');
if ($saldo_marco > 0) {
	$classe_marco = 'green';
} else if ($saldo_marco < 0) {
	$classe_marco = 'red';
}





//PEGAR DADOS DO MES abril
$classe_abril = '#363636';
$pagar_abril = 0;
$receber_abril = 0;
$saldo_abril = 0;
$data_inicio_mes = $ano_atual . "-04-01";
$data_final_mes = $ano_atual . "-04-30";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_abril += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_abril += $res[$i]['subtotal'];
	}
}
$saldo_abril = $receber_abril - $pagar_abril;
$pagar_abrilF = @number_format($pagar_abril, 2, ',', '.');
$receber_abrilF = @number_format($receber_abril, 2, ',', '.');
$saldo_abrilF = @number_format($saldo_abril, 2, ',', '.');
if ($saldo_abril > 0) {
	$classe_abril = 'green';
} else if ($saldo_abril < 0) {
	$classe_abril = 'red';
}




//PEGAR DADOS DO MES maio
$classe_maio = '#363636';
$pagar_maio = 0;
$receber_maio = 0;
$saldo_maio = 0;
$data_inicio_mes = $ano_atual . "-05-01";
$data_final_mes = $ano_atual . "-05-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_maio += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_maio += $res[$i]['subtotal'];
	}
}
$saldo_maio = $receber_maio - $pagar_maio;
$pagar_maioF = @number_format($pagar_maio, 2, ',', '.');
$receber_maioF = @number_format($receber_maio, 2, ',', '.');
$saldo_maioF = @number_format($saldo_maio, 2, ',', '.');
if ($saldo_maio > 0) {
	$classe_maio = 'green';
} else if ($saldo_maio < 0) {
	$classe_maio = 'red';
}




//PEGAR DADOS DO MES junho
$classe_junho = '#363636';
$pagar_junho = 0;
$receber_junho = 0;
$saldo_junho = 0;
$data_inicio_mes = $ano_atual . "-06-01";
$data_final_mes = $ano_atual . "-06-30";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_junho += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_junho += $res[$i]['subtotal'];
	}
}
$saldo_junho = $receber_junho - $pagar_junho;
$pagar_junhoF = @number_format($pagar_junho, 2, ',', '.');
$receber_junhoF = @number_format($receber_junho, 2, ',', '.');
$saldo_junhoF = @number_format($saldo_junho, 2, ',', '.');
if ($saldo_junho > 0) {
	$classe_junho = 'green';
} else if ($saldo_junho < 0) {
	$classe_junho = 'red';
}





//PEGAR DADOS DO MES julho
$classe_julho = '#363636';
$pagar_julho = 0;
$receber_julho = 0;
$saldo_julho = 0;
$data_inicio_mes = $ano_atual . "-07-01";
$data_final_mes = $ano_atual . "-07-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_julho += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_julho += $res[$i]['subtotal'];
	}
}
$saldo_julho = $receber_julho - $pagar_julho;
$pagar_julhoF = @number_format($pagar_julho, 2, ',', '.');
$receber_julhoF = @number_format($receber_julho, 2, ',', '.');
$saldo_julhoF = @number_format($saldo_julho, 2, ',', '.');
if ($saldo_julho > 0) {
	$classe_julho = 'green';
} else if ($saldo_julho < 0) {
	$classe_julho = 'red';
}




//PEGAR DADOS DO MES agosto
$classe_agosto = '#363636';
$pagar_agosto = 0;
$receber_agosto = 0;
$saldo_agosto = 0;
$data_inicio_mes = $ano_atual . "-08-01";
$data_final_mes = $ano_atual . "-08-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_agosto += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_agosto += $res[$i]['subtotal'];
	}
}
$saldo_agosto = $receber_agosto - $pagar_agosto;
$pagar_agostoF = @number_format($pagar_agosto, 2, ',', '.');
$receber_agostoF = @number_format($receber_agosto, 2, ',', '.');
$saldo_agostoF = @number_format($saldo_agosto, 2, ',', '.');
if ($saldo_agosto > 0) {
	$classe_agosto = 'green';
} else if ($saldo_agosto < 0) {
	$classe_agosto = 'red';
}




//PEGAR DADOS DO MES setembro
$classe_setembro = '#363636';
$pagar_setembro = 0;
$receber_setembro = 0;
$saldo_setembro = 0;
$data_inicio_mes = $ano_atual . "-09-01";
$data_final_mes = $ano_atual . "-09-30";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_setembro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_setembro += $res[$i]['subtotal'];
	}
}
$saldo_setembro = $receber_setembro - $pagar_setembro;
$pagar_setembroF = @number_format($pagar_setembro, 2, ',', '.');
$receber_setembroF = @number_format($receber_setembro, 2, ',', '.');
$saldo_setembroF = @number_format($saldo_setembro, 2, ',', '.');
if ($saldo_setembro > 0) {
	$classe_setembro = 'green';
} else if ($saldo_setembro < 0) {
	$classe_setembro = 'red';
}




//PEGAR DADOS DO MES outubro
$classe_outubro = '#363636';
$pagar_outubro = 0;
$receber_outubro = 0;
$saldo_outubro = 0;
$data_inicio_mes = $ano_atual . "-10-01";
$data_final_mes = $ano_atual . "-10-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_outubro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_outubro += $res[$i]['subtotal'];
	}
}
$saldo_outubro = $receber_outubro - $pagar_outubro;
$pagar_outubroF = @number_format($pagar_outubro, 2, ',', '.');
$receber_outubroF = @number_format($receber_outubro, 2, ',', '.');
$saldo_outubroF = @number_format($saldo_outubro, 2, ',', '.');
if ($saldo_outubro > 0) {
	$classe_outubro = 'green';
} else if ($saldo_outubro < 0) {
	$classe_outubro = 'red';
}




//PEGAR DADOS DO MES novembro
$classe_novembro = '#363636';
$pagar_novembro = 0;
$receber_novembro = 0;
$saldo_novembro = 0;
$data_inicio_mes = $ano_atual . "-11-01";
$data_final_mes = $ano_atual . "-11-30";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_novembro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_novembro += $res[$i]['subtotal'];
	}
}
$saldo_novembro = $receber_novembro - $pagar_novembro;
$pagar_novembroF = @number_format($pagar_novembro, 2, ',', '.');
$receber_novembroF = @number_format($receber_novembro, 2, ',', '.');
$saldo_novembroF = @number_format($saldo_novembro, 2, ',', '.');
if ($saldo_novembro > 0) {
	$classe_novembro = 'green';
} else if ($saldo_novembro < 0) {
	$classe_novembro = 'red';
}




//PEGAR DADOS DO MES dezembro
$classe_dezembro = '#363636';
$pagar_dezembro = 0;
$receber_dezembro = 0;
$saldo_dezembro = 0;
$data_inicio_mes = $ano_atual . "-12-01";
$data_final_mes = $ano_atual . "-12-31";
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$receber_dezembro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$pagar_dezembro += $res[$i]['subtotal'];
	}
}
$saldo_dezembro = $receber_dezembro - $pagar_dezembro;
$pagar_dezembroF = @number_format($pagar_dezembro, 2, ',', '.');
$receber_dezembroF = @number_format($receber_dezembro, 2, ',', '.');
$saldo_dezembroF = @number_format($saldo_dezembro, 2, ',', '.');
if ($saldo_dezembro > 0) {
	$classe_dezembro = 'green';
} else if ($saldo_dezembro < 0) {
	$classe_dezembro = 'red';
}
?>
<?php
$meses_balanco = [
 ['Janeiro', $receber_janeiro, $pagar_janeiro, $saldo_janeiro],
 ['Fevereiro', $receber_fevereiro, $pagar_fevereiro, $saldo_fevereiro],
 ['Março', $receber_marco, $pagar_marco, $saldo_marco],
 ['Abril', $receber_abril, $pagar_abril, $saldo_abril],
 ['Maio', $receber_maio, $pagar_maio, $saldo_maio],
 ['Junho', $receber_junho, $pagar_junho, $saldo_junho],
 ['Julho', $receber_julho, $pagar_julho, $saldo_julho],
 ['Agosto', $receber_agosto, $pagar_agosto, $saldo_agosto],
 ['Setembro', $receber_setembro, $pagar_setembro, $saldo_setembro],
 ['Outubro', $receber_outubro, $pagar_outubro, $saldo_outubro],
 ['Novembro', $receber_novembro, $pagar_novembro, $saldo_novembro],
 ['Dezembro', $receber_dezembro, $pagar_dezembro, $saldo_dezembro],
];
$esc_balanco = static function ($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); };
$moeda_balanco = static function ($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); };
$total_recebimentos_balanco = 0;
$total_pagamentos_balanco = 0;
$max_saldo_balanco = 0;
foreach ($meses_balanco as $mes_balanco) {
 $total_recebimentos_balanco += (float)$mes_balanco[1];
 $total_pagamentos_balanco += (float)$mes_balanco[2];
 $max_saldo_balanco = max($max_saldo_balanco, abs((float)$mes_balanco[3]));
}
$logo_balanco = rtrim((string)$url_sistema, '/') . '/sistema/img/' . rawurlencode((string)$logo_sistema);
?>
<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8">
<style>
@page { size: A4 portrait; margin: 17mm 13mm 17mm 13mm; }
body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 9px; }
.header { border-bottom: 2px solid #475569; padding-bottom: 10px; margin-bottom: 14px; }
.header table { width: 100%; border-collapse: collapse; }
.header td { vertical-align: middle; }
.logo-cell { width: 19%; text-align: center; }
.logo { max-width: 95px; max-height: 60px; width: auto; height: auto; }
.company { width: 46%; padding: 0 10px; line-height: 1.55; overflow-wrap: break-word; }
.company strong { font-size: 11px; color: #172b4d; }
.report { width: 35%; text-align: right; line-height: 1.7; }
.report strong { font-size: 12px; color: #172b4d; }
.report small { color: #64748b; }
.section-title { font-size: 11px; font-weight: bold; color: #172b4d; margin: 12px 0 8px; }
.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
.data th { background: #e9eef5; padding: 9px 7px; border-bottom: 1px solid #94a3b8; text-align: left; }
.data td { padding: 8px 7px; border-bottom: 1px solid #e2e8f0; }
.data tbody tr:nth-child(even) { background: #f8fafc; }
.data tr { page-break-inside: avoid; }
.num { text-align: right !important; white-space: nowrap; }
.negative { color: #b91c1c; font-weight: bold; }
.positive { color: #15803d; font-weight: bold; }
.total td { background: #e9eef5; font-weight: bold; border-top: 2px solid #94a3b8; }
.chart { margin-top: 18px; page-break-inside: avoid; }
.chart-row { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
.chart-label { width: 18%; font-size: 8px; }
.chart-track { width: 57%; background: #eef2f7; height: 12px; }
.chart-bar { height: 12px; background: #64748b; }
.chart-bar.negative-bar { background: #b91c1c; }
.chart-value { width: 25%; text-align: right; font-size: 8px; }
.footer { position: fixed; bottom: -11mm; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 5px; color: #64748b; font-size: 7px; }
.watermark { position: fixed; top: 35%; left: 18%; width: 64%; opacity: .07; z-index: -1; }
</style></head><body>
<?php if (($marca_dagua ?? '') === 'Sim' && ($tipo_rel ?? '') !== 'HTML'): ?><img class="watermark" src="<?php echo $esc_balanco($logo_balanco); ?>" alt=""><?php endif; ?>
<div class="header"><table><tr>
<td class="logo-cell"><img class="logo" src="<?php echo $esc_balanco($logo_balanco); ?>" alt="Logo"></td>
<td class="company"><strong><?php echo $esc_balanco(mb_strtoupper((string)$nome_sistema)); ?></strong>
<?php if (!empty($cnpj_sistema)): ?><div>CNPJ: <?php echo $esc_balanco($cnpj_sistema); ?></div><?php endif; ?>
<?php if (!empty($instagram_sistema)): ?><div>Instagram: <?php echo $esc_balanco($instagram_sistema); ?></div><?php endif; ?>
<?php if (!empty($endereco_sistema)): ?><div><?php echo $esc_balanco($endereco_sistema); ?></div><?php endif; ?>
</td>
<td class="report"><strong>BALANÇO ANUAL</strong><div>Ano: <?php echo $esc_balanco($ano_atual); ?></div><small><?php echo $esc_balanco($data_hoje ?? date('d/m/Y')); ?></small></td>
</tr></table></div>
<div class="section-title">Resumo financeiro mensal</div>
<table class="data"><colgroup><col style="width:20%"><col style="width:27%"><col style="width:27%"><col style="width:26%"></colgroup>
<thead><tr><th>MÊS</th><th class="num">RECEBIMENTOS</th><th class="num">PAGAMENTOS</th><th class="num">SALDO</th></tr></thead>
<tbody>
<?php foreach ($meses_balanco as $mes_balanco): ?>
<tr><td><?php echo $esc_balanco($mes_balanco[0]); ?></td>
<td class="num"><?php echo $moeda_balanco($mes_balanco[1]); ?></td>
<td class="num"><?php echo $moeda_balanco($mes_balanco[2]); ?></td>
<td class="num <?php echo $mes_balanco[3] < 0 ? 'negative' : 'positive'; ?>"><?php echo $moeda_balanco($mes_balanco[3]); ?></td></tr>
<?php endforeach; ?>
<tr class="total"><td>TOTAL ANUAL</td><td class="num"><?php echo $moeda_balanco($total_recebimentos_balanco); ?></td><td class="num"><?php echo $moeda_balanco($total_pagamentos_balanco); ?></td><td class="num"><?php echo $moeda_balanco($total_recebimentos_balanco - $total_pagamentos_balanco); ?></td></tr>
</tbody></table>
<div class="chart"><div class="section-title">Comparativo de saldos por mês</div>
<?php foreach ($meses_balanco as $mes_balanco):
$largura_balanco = $max_saldo_balanco > 0 ? round(abs((float)$mes_balanco[3]) / $max_saldo_balanco * 100, 1) : 0;
?>
<table class="chart-row"><tr>
<td class="chart-label"><?php echo $esc_balanco($mes_balanco[0]); ?></td>
<td class="chart-track"><div class="chart-bar <?php echo $mes_balanco[3] < 0 ? 'negative-bar' : ''; ?>" style="width:<?php echo $largura_balanco; ?>%;"></div></td>
<td class="chart-value"><?php echo $moeda_balanco($mes_balanco[3]); ?></td>
</tr></table>
<?php endforeach; ?>
</div>
<div class="footer"><?php echo $esc_balanco($nome_sistema); ?><?php if (!empty($telefone_sistema)): ?> · Telefone: <?php echo $esc_balanco($telefone_sistema); ?><?php endif; ?></div>
</body></html>