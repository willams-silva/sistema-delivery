<?php 
include('data_formatada.php');

if ($token_rel != 'M543664') {
	echo '<script>window.location="../../"</script>';
	exit();
}

$data_atual = date('Y-m-d');
$mes_atual = Date('m');
$ano_atual = Date('Y');
$data_inicio_mes = $ano_atual."-".$mes_atual."-01";
$data_inicio_ano = $ano_atual."-01-01";




//PEGAR DADOS DO MES JANEIRO
$classe_janeiro = '#363636';
$pagar_janeiro = 0;
$receber_janeiro = 0;
$saldo_janeiro = 0;
$data_inicio_mes = $ano_atual."-01-01";
$data_final_mes = $ano_atual."-01-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_janeiro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_janeiro += $res[$i]['subtotal'];
	}
}
$saldo_janeiro = $receber_janeiro - $pagar_janeiro;
$pagar_janeiroF = @number_format($pagar_janeiro, 2, ',', '.');
$receber_janeiroF = @number_format($receber_janeiro, 2, ',', '.');
$saldo_janeiroF = @number_format($saldo_janeiro, 2, ',', '.');
if($saldo_janeiro > 0){
	$classe_janeiro = 'green';
}else if($saldo_janeiro < 0){
	$classe_janeiro = 'red';
}




//PEGAR DADOS DO MES fevereiro
$classe_fevereiro = '#363636';
$pagar_fevereiro = 0;
$receber_fevereiro = 0;
$saldo_fevereiro = 0;
$data_inicio_mes = $ano_atual."-02-01";

$bissexto = date('L', @mktime(0, 0, 0, 1, 1, $ano_atual));
	if($bissexto == 1){
		$data_final_mes = $ano_atual.'-02-29';
	}else{
		$data_final_mes = $ano_atual.'-02-28';
	}

$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_fevereiro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_fevereiro += $res[$i]['subtotal'];
	}
}
$saldo_fevereiro = $receber_fevereiro - $pagar_fevereiro;
$pagar_fevereiroF = @number_format($pagar_fevereiro, 2, ',', '.');
$receber_fevereiroF = @number_format($receber_fevereiro, 2, ',', '.');
$saldo_fevereiroF = @number_format($saldo_fevereiro, 2, ',', '.');
if($saldo_fevereiro > 0){
	$classe_fevereiro = 'green';
}else if($saldo_fevereiro < 0){
	$classe_fevereiro = 'red';
}




//PEGAR DADOS DO MES marco
$classe_marco = '#363636';
$pagar_marco = 0;
$receber_marco = 0;
$saldo_marco = 0;
$data_inicio_mes = $ano_atual."-03-01";
$data_final_mes = $ano_atual."-03-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_marco += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_marco += $res[$i]['subtotal'];
	}
}
$saldo_marco = $receber_marco - $pagar_marco;
$pagar_marcoF = @number_format($pagar_marco, 2, ',', '.');
$receber_marcoF = @number_format($receber_marco, 2, ',', '.');
$saldo_marcoF = @number_format($saldo_marco, 2, ',', '.');
if($saldo_marco > 0){
	$classe_marco = 'green';
}else if($saldo_marco < 0){
	$classe_marco = 'red';
}





//PEGAR DADOS DO MES abril
$classe_abril = '#363636';
$pagar_abril = 0;
$receber_abril = 0;
$saldo_abril = 0;
$data_inicio_mes = $ano_atual."-04-01";
$data_final_mes = $ano_atual."-04-30";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_abril += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_abril += $res[$i]['subtotal'];
	}
}
$saldo_abril = $receber_abril - $pagar_abril;
$pagar_abrilF = @number_format($pagar_abril, 2, ',', '.');
$receber_abrilF = @number_format($receber_abril, 2, ',', '.');
$saldo_abrilF = @number_format($saldo_abril, 2, ',', '.');
if($saldo_abril > 0){
	$classe_abril = 'green';
}else if($saldo_abril < 0){
	$classe_abril = 'red';
}




//PEGAR DADOS DO MES maio
$classe_maio = '#363636';
$pagar_maio = 0;
$receber_maio = 0;
$saldo_maio = 0;
$data_inicio_mes = $ano_atual."-05-01";
$data_final_mes = $ano_atual."-05-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_maio += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_maio += $res[$i]['subtotal'];
	}
}
$saldo_maio = $receber_maio - $pagar_maio;
$pagar_maioF = @number_format($pagar_maio, 2, ',', '.');
$receber_maioF = @number_format($receber_maio, 2, ',', '.');
$saldo_maioF = @number_format($saldo_maio, 2, ',', '.');
if($saldo_maio > 0){
	$classe_maio = 'green';
}else if($saldo_maio < 0){
	$classe_maio = 'red';
}




//PEGAR DADOS DO MES junho
$classe_junho = '#363636';
$pagar_junho = 0;
$receber_junho = 0;
$saldo_junho = 0;
$data_inicio_mes = $ano_atual."-06-01";
$data_final_mes = $ano_atual."-06-30";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_junho += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_junho += $res[$i]['subtotal'];
	}
}
$saldo_junho = $receber_junho - $pagar_junho;
$pagar_junhoF = @number_format($pagar_junho, 2, ',', '.');
$receber_junhoF = @number_format($receber_junho, 2, ',', '.');
$saldo_junhoF = @number_format($saldo_junho, 2, ',', '.');
if($saldo_junho > 0){
	$classe_junho = 'green';
}else if($saldo_junho < 0){
	$classe_junho = 'red';
}





//PEGAR DADOS DO MES julho
$classe_julho = '#363636';
$pagar_julho = 0;
$receber_julho = 0;
$saldo_julho = 0;
$data_inicio_mes = $ano_atual."-07-01";
$data_final_mes = $ano_atual."-07-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_julho += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_julho += $res[$i]['subtotal'];
	}
}
$saldo_julho = $receber_julho - $pagar_julho;
$pagar_julhoF = @number_format($pagar_julho, 2, ',', '.');
$receber_julhoF = @number_format($receber_julho, 2, ',', '.');
$saldo_julhoF = @number_format($saldo_julho, 2, ',', '.');
if($saldo_julho > 0){
	$classe_julho = 'green';
}else if($saldo_julho < 0){
	$classe_julho = 'red';
}




//PEGAR DADOS DO MES agosto
$classe_agosto = '#363636';
$pagar_agosto = 0;
$receber_agosto = 0;
$saldo_agosto = 0;
$data_inicio_mes = $ano_atual."-08-01";
$data_final_mes = $ano_atual."-08-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_agosto += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_agosto += $res[$i]['subtotal'];
	}
}
$saldo_agosto = $receber_agosto - $pagar_agosto;
$pagar_agostoF = @number_format($pagar_agosto, 2, ',', '.');
$receber_agostoF = @number_format($receber_agosto, 2, ',', '.');
$saldo_agostoF = @number_format($saldo_agosto, 2, ',', '.');
if($saldo_agosto > 0){
	$classe_agosto = 'green';
}else if($saldo_agosto < 0){
	$classe_agosto = 'red';
}




//PEGAR DADOS DO MES setembro
$classe_setembro = '#363636';
$pagar_setembro = 0;
$receber_setembro = 0;
$saldo_setembro = 0;
$data_inicio_mes = $ano_atual."-09-01";
$data_final_mes = $ano_atual."-09-30";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_setembro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_setembro += $res[$i]['subtotal'];
	}
}
$saldo_setembro = $receber_setembro - $pagar_setembro;
$pagar_setembroF = @number_format($pagar_setembro, 2, ',', '.');
$receber_setembroF = @number_format($receber_setembro, 2, ',', '.');
$saldo_setembroF = @number_format($saldo_setembro, 2, ',', '.');
if($saldo_setembro > 0){
	$classe_setembro = 'green';
}else if($saldo_setembro < 0){
	$classe_setembro = 'red';
}




//PEGAR DADOS DO MES outubro
$classe_outubro = '#363636';
$pagar_outubro = 0;
$receber_outubro = 0;
$saldo_outubro = 0;
$data_inicio_mes = $ano_atual."-10-01";
$data_final_mes = $ano_atual."-10-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_outubro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_outubro += $res[$i]['subtotal'];
	}
}
$saldo_outubro = $receber_outubro - $pagar_outubro;
$pagar_outubroF = @number_format($pagar_outubro, 2, ',', '.');
$receber_outubroF = @number_format($receber_outubro, 2, ',', '.');
$saldo_outubroF = @number_format($saldo_outubro, 2, ',', '.');
if($saldo_outubro > 0){
	$classe_outubro = 'green';
}else if($saldo_outubro < 0){
	$classe_outubro = 'red';
}




//PEGAR DADOS DO MES novembro
$classe_novembro = '#363636';
$pagar_novembro = 0;
$receber_novembro = 0;
$saldo_novembro = 0;
$data_inicio_mes = $ano_atual."-11-01";
$data_final_mes = $ano_atual."-11-30";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_novembro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_novembro += $res[$i]['subtotal'];
	}
}
$saldo_novembro = $receber_novembro - $pagar_novembro;
$pagar_novembroF = @number_format($pagar_novembro, 2, ',', '.');
$receber_novembroF = @number_format($receber_novembro, 2, ',', '.');
$saldo_novembroF = @number_format($saldo_novembro, 2, ',', '.');
if($saldo_novembro > 0){
	$classe_novembro = 'green';
}else if($saldo_novembro < 0){
	$classe_novembro = 'red';
}




//PEGAR DADOS DO MES dezembro
$classe_dezembro = '#363636';
$pagar_dezembro = 0;
$receber_dezembro = 0;
$saldo_dezembro = 0;
$data_inicio_mes = $ano_atual."-12-01";
$data_final_mes = $ano_atual."-12-31";
$query = $pdo->query("SELECT * from receber_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$receber_dezembro += $res[$i]['subtotal'];
	}
}
$query = $pdo->query("SELECT * from pagar_sas where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if($linhas > 0){
	for($i=0; $i<$linhas; $i++){
		$pagar_dezembro += $res[$i]['subtotal'];
	}
}
$saldo_dezembro = $receber_dezembro - $pagar_dezembro;
$pagar_dezembroF = @number_format($pagar_dezembro, 2, ',', '.');
$receber_dezembroF = @number_format($receber_dezembro, 2, ',', '.');
$saldo_dezembroF = @number_format($saldo_dezembro, 2, ',', '.');
if($saldo_dezembro > 0){
	$classe_dezembro = 'green';
}else if($saldo_dezembro < 0){
	$classe_dezembro = 'red';
}
?>
<?php
// Apenas totais de apresentação: os valores mensais continuam vindo das consultas originais.
$total_receber_ano = $receber_janeiro + $receber_fevereiro + $receber_marco + $receber_abril + $receber_maio + $receber_junho + $receber_julho + $receber_agosto + $receber_setembro + $receber_outubro + $receber_novembro + $receber_dezembro;
$total_pagar_ano = $pagar_janeiro + $pagar_fevereiro + $pagar_marco + $pagar_abril + $pagar_maio + $pagar_junho + $pagar_julho + $pagar_agosto + $pagar_setembro + $pagar_outubro + $pagar_novembro + $pagar_dezembro;
$total_saldo_ano = $total_receber_ano - $total_pagar_ano;
$logo_relatorio = '';
// O painel salva a logo de relatório em sistema/img/logo.jpg.
$logo_path = __DIR__ . '/../../img/logo.jpg';
if (is_file($logo_path) && is_readable($logo_path)) {
    $logo_bytes = file_get_contents($logo_path);
    if ($logo_bytes !== false && $logo_bytes !== '') {
        $logo_info = @getimagesize($logo_path);
        $logo_mime = isset($logo_info['mime']) ? $logo_info['mime'] : '';
        if (in_array($logo_mime, array('image/jpeg', 'image/png', 'image/gif'), true)) {
            $logo_relatorio = 'data:' . $logo_mime . ';base64,' . base64_encode($logo_bytes);
        }
    }
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
@page{margin:105px 30px 55px 30px}
body{font-family:DejaVu Sans,sans-serif;color:#14243d;font-size:10px}
#header{position:fixed;top:-84px;left:0;right:0;height:67px}
.report-head{width:100%;border-collapse:collapse;border-bottom:3px solid #ffc400}
.report-head td{padding:5px;vertical-align:middle}
.report-logo{width:115px;height:55px}
.report-logo img{max-width:105px;max-height:50px}
.report-info{text-align:right;font-size:10px;line-height:1.6}
.report-title{font-size:15px;font-weight:bold;color:#061b3a}
.report-year{color:#475569}
.report-table{width:100%;border-collapse:collapse;table-layout:fixed}
.report-table th{background:#061b3a;color:white;padding:12px 9px;text-align:left;font-size:10px}
.report-table td{padding:11px 9px;border-bottom:1px solid #dce5f0}
.report-table tbody tr:nth-child(even){background:#f1f5fb}
.report-table tr{page-break-inside:avoid}
.report-table tfoot td{background:#e8eff8;font-weight:bold;border-top:2px solid #ffc400;padding:13px 9px}
.report-table th:not(:first-child),.report-table td:not(:first-child){text-align:right}
#footer{position:fixed;bottom:-38px;left:0;right:0;border-top:1px solid #cbd5e1;padding-top:6px;color:#475569;font-size:8px}
#footer .page:after{content:counter(page)}
.marca{position:fixed;top:180px;left:90px;width:65%;opacity:.07}
</style></head><body>
<?php if ($marca_dagua == 'Sim' && $logo_relatorio != '') { ?><img class="marca" src="<?php echo $logo_relatorio; ?>"><?php } ?>
<div id="header"><table class="report-head"><tr><td class="report-logo"><?php if ($logo_relatorio != '') { ?><img src="<?php echo $logo_relatorio; ?>"><?php } ?></td><td class="report-info"><div class="report-title">BALANÇO ANUAL</div><div class="report-year">Exercício <?php echo htmlspecialchars((string)$ano_atual, ENT_QUOTES, 'UTF-8'); ?></div><div><?php echo htmlspecialchars(mb_strtoupper($data_hoje), ENT_QUOTES, 'UTF-8'); ?></div></td></tr></table></div>
<div id="footer"><table width="100%"><tr><td><?php echo htmlspecialchars($nome_sistema . ' | Telefone: ' . $telefone_sistema, ENT_QUOTES, 'UTF-8'); ?></td><td align="right">Página <span class="page"></span></td></tr></table></div>
<table class="report-table">
<thead><tr><th style="width:22%">MÊS</th><th style="width:26%">RECEBIMENTOS</th><th style="width:26%">PAGAMENTOS</th><th style="width:26%">SALDO</th></tr></thead>
<tbody>
<tr><td>Janeiro</td><td>R$ <?php echo $receber_janeiroF; ?></td><td>R$ <?php echo $pagar_janeiroF; ?></td><td style="color:<?php echo $saldo_janeiro < 0 ? '#b42332' : ($saldo_janeiro > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_janeiroF; ?></td></tr>
<tr><td>Fevereiro</td><td>R$ <?php echo $receber_fevereiroF; ?></td><td>R$ <?php echo $pagar_fevereiroF; ?></td><td style="color:<?php echo $saldo_fevereiro < 0 ? '#b42332' : ($saldo_fevereiro > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_fevereiroF; ?></td></tr>
<tr><td>Março</td><td>R$ <?php echo $receber_marcoF; ?></td><td>R$ <?php echo $pagar_marcoF; ?></td><td style="color:<?php echo $saldo_marco < 0 ? '#b42332' : ($saldo_marco > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_marcoF; ?></td></tr>
<tr><td>Abril</td><td>R$ <?php echo $receber_abrilF; ?></td><td>R$ <?php echo $pagar_abrilF; ?></td><td style="color:<?php echo $saldo_abril < 0 ? '#b42332' : ($saldo_abril > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_abrilF; ?></td></tr>
<tr><td>Maio</td><td>R$ <?php echo $receber_maioF; ?></td><td>R$ <?php echo $pagar_maioF; ?></td><td style="color:<?php echo $saldo_maio < 0 ? '#b42332' : ($saldo_maio > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_maioF; ?></td></tr>
<tr><td>Junho</td><td>R$ <?php echo $receber_junhoF; ?></td><td>R$ <?php echo $pagar_junhoF; ?></td><td style="color:<?php echo $saldo_junho < 0 ? '#b42332' : ($saldo_junho > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_junhoF; ?></td></tr>
<tr><td>Julho</td><td>R$ <?php echo $receber_julhoF; ?></td><td>R$ <?php echo $pagar_julhoF; ?></td><td style="color:<?php echo $saldo_julho < 0 ? '#b42332' : ($saldo_julho > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_julhoF; ?></td></tr>
<tr><td>Agosto</td><td>R$ <?php echo $receber_agostoF; ?></td><td>R$ <?php echo $pagar_agostoF; ?></td><td style="color:<?php echo $saldo_agosto < 0 ? '#b42332' : ($saldo_agosto > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_agostoF; ?></td></tr>
<tr><td>Setembro</td><td>R$ <?php echo $receber_setembroF; ?></td><td>R$ <?php echo $pagar_setembroF; ?></td><td style="color:<?php echo $saldo_setembro < 0 ? '#b42332' : ($saldo_setembro > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_setembroF; ?></td></tr>
<tr><td>Outubro</td><td>R$ <?php echo $receber_outubroF; ?></td><td>R$ <?php echo $pagar_outubroF; ?></td><td style="color:<?php echo $saldo_outubro < 0 ? '#b42332' : ($saldo_outubro > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_outubroF; ?></td></tr>
<tr><td>Novembro</td><td>R$ <?php echo $receber_novembroF; ?></td><td>R$ <?php echo $pagar_novembroF; ?></td><td style="color:<?php echo $saldo_novembro < 0 ? '#b42332' : ($saldo_novembro > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_novembroF; ?></td></tr>
<tr><td>Dezembro</td><td>R$ <?php echo $receber_dezembroF; ?></td><td>R$ <?php echo $pagar_dezembroF; ?></td><td style="color:<?php echo $saldo_dezembro < 0 ? '#b42332' : ($saldo_dezembro > 0 ? '#15803d' : '#475569'); ?>;font-weight:bold">R$ <?php echo $saldo_dezembroF; ?></td></tr>
</tbody>
<tfoot><tr><td>TOTAL ANUAL</td><td>R$ <?php echo number_format($total_receber_ano,2,',','.'); ?></td><td>R$ <?php echo number_format($total_pagar_ano,2,',','.'); ?></td><td style="color:<?php echo $total_saldo_ano < 0 ? '#b42332' : '#15803d'; ?>">R$ <?php echo number_format($total_saldo_ano,2,',','.'); ?></td></tr></tfoot>
</table>
</body></html>