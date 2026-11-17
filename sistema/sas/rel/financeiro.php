<?php 
include('data_formatada.php');

if ($token_rel != 'M543664') {
	echo '<script>window.location="../../"</script>';
	exit();
}


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


if($filtro_tipo == 'receber'){
	$filtro_tipo = 'receber_sas';
}else{
	$filtro_tipo = 'pagar_sas';
}

?>
<!DOCTYPE html>
<html>
<head>

<style>
@page { margin: 115px 28px 55px 28px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #14243d; }
#header { position: fixed; top: -94px; left: 0; right: 0; height: 76px; }
.report-head { width: 100%; border-collapse: collapse; border-bottom: 3px solid #ffc400; }
.report-head td { vertical-align: middle; padding: 6px; }
.report-logo { width: 115px; height: 58px; text-align: left; }
.report-logo img { max-width: 105px; max-height: 55px; }
.report-info { text-align: right; font-size: 8px; line-height: 1.5; }
.report-title { color: #061b3a; font-size: 13px; font-weight: bold; }
.report-subtitle { font-size: 10px; font-weight: bold; color: #167347; }
#content { margin: 0; }
.report-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8px; }
.report-table thead { display: table-header-group; }
.report-table thead th { background: #061b3a; color: #fff; text-align: left; font-size: 7px; padding: 8px 3px; border-right: 1px solid #294562; }
.report-table tbody td { padding: 7px 3px; vertical-align: top; border-bottom: 1px solid #e2e8f0; overflow-wrap: break-word; }
.report-table tbody tr:nth-child(even) { background: #f1f5fb; }
.report-table tbody tr { page-break-inside: avoid; }
.status-dot { font-size: 11px; font-weight: bold; }
#footer { position: fixed; bottom: -39px; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 5px; color: #475569; font-size: 8px; }
#footer .page:after { content: counter(page); }
.summary-table { width: 100%; margin-top: 15px; border-top: 2px solid #ffc400; border-collapse: collapse; }
.summary-table td { padding: 10px 4px; text-align: right; font-size: 9px; }
.marca { position: fixed; top: 220px; left: 90px; width: 65%; opacity: .07; }
</style>

</head>
<body>
<?php
// Imagens locais incorporadas evitam ícones quebrados no Dompdf.
$logo_relatorio = '';
$logo_path = realpath(__DIR__ . '/../../img/logo.jpg');
if ($logo_path && is_file($logo_path) && is_readable($logo_path)) {
    $logo_bytes = file_get_contents($logo_path);
    if ($logo_bytes !== false) {
        $logo_relatorio = 'data:image/jpeg;base64,' . base64_encode($logo_bytes);
    }
}
?>
<?php if ($marca_dagua == 'Sim' && $logo_relatorio != '') { ?>
<img class="marca" src="<?php echo $logo_relatorio; ?>">
<?php } ?>
<div id="header">
  <table class="report-head"><tr>
    <td class="report-logo"><?php if ($logo_relatorio != '') { ?><img src="<?php echo $logo_relatorio; ?>"><?php } ?></td>
    <td class="report-info">
      <div class="report-title">RELATÓRIO FINANCEIRO</div>
      <div class="report-subtitle"><?php echo htmlspecialchars($filtro_tipoF, ENT_QUOTES, 'UTF-8'); ?><?php if ($filtro_lancamentoF != '') { echo ' (' . htmlspecialchars(mb_strtoupper($filtro_lancamentoF), ENT_QUOTES, 'UTF-8') . ')'; } ?></div>
      <div><?php echo htmlspecialchars(mb_strtoupper($texto_filtro), ENT_QUOTES, 'UTF-8'); ?></div>
      <div><?php echo htmlspecialchars(mb_strtoupper($data_hoje), ENT_QUOTES, 'UTF-8'); ?></div>
    </td>
  </tr></table>
</div>
<div id="footer"><table width="100%"><tr><td><?php echo htmlspecialchars($nome_sistema . ' | Telefone: ' . $telefone_sistema, ENT_QUOTES, 'UTF-8'); ?></td><td align="right">Página <span class="page"></span></td></tr></table></div>
<div id="content">
<table class="report-table">
<thead><tr>
<th style="width:22%">DESCRIÇÃO</th><th style="width:10%">VALOR</th><th style="width:12%">LANÇAMENTO</th><th style="width:12%">VENCIMENTO</th><th style="width:12%">PAGAMENTO</th><th style="width:17%">RECEBIDO POR</th><th style="width:15%">REFERÊNCIA</th>
</tr></thead><tbody>
<?php

$total_valor = 0;
$total_valorF = 0;
$total_pendentes = 0;
$total_pendentesF = 0;
$total_pagas = 0;
$total_pagasF = 0;
$pendentes = 0;
$pagas = 0;

if($mostrar_registros == 'Não'){
$query = $pdo->query("SELECT * from $filtro_tipo where $filtro_data >= '$dataInicial' and $filtro_data <= '$dataFinal' and pago LIKE '%$filtro_pendentes%' and referencia LIKE '%$filtro_lancamento%' and usuario_lanc = '$id_usuario' order by $filtro_data asc");
}else{
	$query = $pdo->query("SELECT * from $filtro_tipo where $filtro_data >= '$dataInicial' and $filtro_data <= '$dataFinal' and pago LIKE '%$filtro_pendentes%' and referencia LIKE '%$filtro_lancamento%' order by $filtro_data asc");
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
<td style="width:22%"><span class="status-dot" style="color:<?php echo $pago == 'Sim' ? '#15803d' : '#dc2626'; ?>">&#9679;</span> <?php echo htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8'); ?></td>
<td style="width:10%">R$ <?php echo $valorF ?></td>
<td style="width:12%"><?php echo $data_lancF ?></td>
<td style="width:12%"><?php echo $data_vencF ?></td>
<td style="width:12%"><?php echo $data_pgtoF ?></td>
<td style="width:17%"><?php echo htmlspecialchars($nome_usu_pgto, ENT_QUOTES, 'UTF-8'); ?></td>
<td style="width:15%"><?php echo htmlspecialchars($referencia, ENT_QUOTES, 'UTF-8'); ?></td>
</tr>

<?php } } ?>
</tbody></table></div>
<table class="summary-table"><tr>
<td><b>Pendentes: <span style="color:#dc2626"><?php echo $pendentes ?></span></b></td>
<td><b>Pagas: <span style="color:#15803d"><?php echo $pagas ?></span></b></td>
<td><b>Pendentes: <span style="color:#dc2626">R$ <?php echo $total_pendentesF ?></span></b></td>
<td><b>Pagas: <span style="color:#15803d">R$ <?php echo $total_pagasF ?></span></b></td>
</tr></table>
</body></html>
