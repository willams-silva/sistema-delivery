<?php 
include('data_formatada.php');

if ($token_rel != 'M543664') {
	echo '<script>window.location="../../"</script>';
	exit();
}


?>
<?php
require_once __DIR__ . '/identidade_pdf.php';
$logo_relatorio = sasRelatorioLogo();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
@page{margin:110px 28px 52px 28px}
body{font-family:DejaVu Sans,sans-serif;color:#14243d;font-size:9px}
#header{position:fixed;top:-89px;left:0;right:0;height:70px}
.report-head{width:100%;border-collapse:collapse;border-bottom:3px solid #ffc400}
.report-head td{padding:5px;vertical-align:middle}
.report-logo{width:115px;height:55px}
.report-logo img{max-width:105px;max-height:50px}
.report-info{text-align:right;line-height:1.6;font-size:9px}
.report-title{font-size:14px;font-weight:bold;color:#061b3a}
.report-table{width:100%;table-layout:fixed;border-collapse:collapse}
.report-table thead{display:table-header-group}
.report-table th{background:#061b3a;color:#fff;text-align:left;padding:10px 7px;font-size:9px}
.report-table td{padding:9px 7px;border-bottom:1px solid #dce5f0;vertical-align:top;overflow-wrap:break-word}
.report-table tbody tr:nth-child(even){background:#f1f5fb}
.report-table tbody tr{page-break-inside:avoid}
.total-table{width:100%;border-collapse:collapse;margin-top:12px}
.total-table td{text-align:right;padding:11px 7px;border-top:2px solid #ffc400;background:#e8eff8;font-weight:bold}
#footer{position:fixed;bottom:-38px;left:0;right:0;border-top:1px solid #cbd5e1;padding-top:6px;color:#475569;font-size:8px}
#footer .page:after{content:counter(page)}
.marca{position:fixed;top:190px;left:90px;width:65%;opacity:.07}
<?php echo sasRelatorioCss(); ?>
</style></head><body>
<?php if ($marca_dagua == 'Sim' && $logo_relatorio != '') { ?><img class="marca" src="<?php echo $logo_relatorio; ?>"><?php } ?>
<div id="header"><table class="report-head"><tr><td class="report-logo"><?php if ($logo_relatorio != '') { ?><img src="<?php echo $logo_relatorio; ?>"><?php } ?></td><td class="report-info"><div class="report-title">RELATÓRIO DE INADIMPLENTES</div><div><?php echo htmlspecialchars(mb_strtoupper($data_hoje), ENT_QUOTES, 'UTF-8'); ?></div></td></tr></table></div>
<div id="footer"><table width="100%"><tr><td><?php echo htmlspecialchars($nome_sistema . ' | Telefone: ' . $telefone_sistema, ENT_QUOTES, 'UTF-8'); ?></td><td align="right">Página <span class="page"></span></td></tr></table></div>
<table class="report-table"><thead><tr>
<th style="width:35%">CLIENTE</th><th style="width:18%">TOTAL DÉBITO</th><th style="width:30%">EMAIL</th><th style="width:17%">TELEFONE</th>
</tr></thead><tbody>
<?php
$total_ina = 0;
$total_inaF = 0;
if($mostrar_registros == 'Não'){
	$query9 = $pdo->query("SELECT * from empresas where usuario = '$id_usuario' order by nome asc");
}else{
	$query9 = $pdo->query("SELECT * from empresas order by nome asc");
}
$res9 = $query9->fetchAll(PDO::FETCH_ASSOC);
$linhas9 = @count($res9);
if($linhas9 > 0){
for($i9=0; $i9<$linhas9; $i9++){
	$id_pessoa = $res9[$i9]['id'];
	$nome_pessoa = $res9[$i9]['nome'];
	$tel_pessoa = $res9[$i9]['telefone'];
	$email = $res9[$i9]['email'];


$total_valor = 0;
$total_valorF = 0;



$query = $pdo->query("SELECT * from receber_sas where vencimento < curDate() and pago != 'Sim' and cliente = '$id_pessoa'");
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

$total_valor += $valor;
$total_valorF = @number_format($total_valor, 2, ',', '.');

} 



}else{
	//continue para ele pular para o proximo
	continue;
	//se usar break ele para toda instrução
}
$total_ina += $total_valor;
$total_inaF = @number_format($total_ina, 2, ',', '.');

  	 ?>

  	 
<tr>
<td><?php echo htmlspecialchars((string)$nome_pessoa, ENT_QUOTES, 'UTF-8'); ?></td>
<td style="color:#b42332;font-weight:bold">R$ <?php echo $total_valorF; ?></td>
<td><?php echo htmlspecialchars((string)$email, ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars((string)$tel_pessoa, ENT_QUOTES, 'UTF-8'); ?></td>
</tr>

<?php } } ?>
</tbody></table>
<table class="total-table"><tr><td>TOTAL EM ATRASO: <span style="color:#b42332">R$ <?php echo $total_inaF; ?></span></td></tr></table>
</body></html>