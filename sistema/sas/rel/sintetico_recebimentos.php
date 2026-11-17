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


if($filtro_lancamento == 'cliente'){		
	$filtro_lancamentoF = 'Recebimentos Clientes';
	$filtro_tabela = 'empresas';		
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


if($filtro_tipo == "receber"){
	$filtro_tipo = "receber_sas";
}else{
	$filtro_tipo = "pagar_sas";
}


?>
<!DOCTYPE html>
<html>
<head>

<style>
@page{margin:115px 28px 52px 28px}
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#14243d}
#header{position:fixed;top:-94px;left:0;right:0;height:76px}
.report-head{width:100%;border-collapse:collapse;border-bottom:3px solid #ffc400}
.report-head td{vertical-align:middle;padding:6px}
.report-logo{width:115px;height:58px}
.report-logo img{max-width:105px;max-height:55px}
.report-info{text-align:right;font-size:9px;line-height:1.5}
.report-title{color:#061b3a;font-size:13px;font-weight:bold}
#content{margin:0}
.report-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:9px}
.report-table thead{display:table-header-group}
.report-table th{background:#061b3a;color:#fff;text-align:left;font-size:8px;padding:9px 5px;border-right:1px solid #294562}
.report-table td{padding:9px 5px;vertical-align:top;border-bottom:1px solid #e2e8f0;overflow-wrap:break-word}
.report-table tbody tr:nth-child(even){background:#f1f5fb}
.report-table tbody tr{page-break-inside:avoid}
#footer{position:fixed;bottom:-37px;left:0;right:0;border-top:1px solid #cbd5e1;padding-top:5px;color:#475569;font-size:8px}
#footer .page:after{content:counter(page)}
.marca{position:fixed;top:220px;left:90px;width:65%;opacity:.07}
</style>

</head>
<body>
<?php
$logo_relatorio = '';
$nome_logo_relatorio = isset($logo_rel) && is_string($logo_rel) && $logo_rel !== '' ? basename($logo_rel) : 'logo.jpg';
$logo_path = realpath(__DIR__ . '/../../img/' . $nome_logo_relatorio);
if ($logo_path && is_file($logo_path) && is_readable($logo_path)) {
    $logo_bytes = file_get_contents($logo_path);
    if ($logo_bytes !== false) $logo_mime = function_exists('mime_content_type') ? mime_content_type($logo_path) : 'image/jpeg';
        if (in_array($logo_mime, array('image/jpeg', 'image/png', 'image/gif'), true)) {
            $logo_relatorio = 'data:' . $logo_mime . ';base64,' . base64_encode($logo_bytes);
        }
}
?>
<?php if ($marca_dagua == 'Sim' && $logo_relatorio != '') { ?><img class="marca" src="<?php echo $logo_relatorio; ?>"><?php } ?>
<div id="header"><table class="report-head"><tr>
<td class="report-logo"><?php if ($logo_relatorio != '') { ?><img src="<?php echo $logo_relatorio; ?>"><?php } ?></td>
<td class="report-info"><div class="report-title">RELATÓRIO SINTÉTICO DE RECEBIMENTOS</div>
<div><b><?php echo htmlspecialchars(mb_strtoupper($filtro_lancamentoF), ENT_QUOTES, 'UTF-8'); ?></b></div>
<div><?php echo htmlspecialchars(mb_strtoupper($texto_filtro), ENT_QUOTES, 'UTF-8'); ?></div>
<div><?php echo htmlspecialchars(mb_strtoupper($data_hoje), ENT_QUOTES, 'UTF-8'); ?></div>
</td></tr></table></div>
<div id="footer"><table width="100%"><tr><td><?php echo htmlspecialchars($nome_sistema . ' | Telefone: ' . $telefone_sistema, ENT_QUOTES, 'UTF-8'); ?></td><td align="right">Página <span class="page"></span></td></tr></table></div>
<div id="content"><table class="report-table"><thead><tr>
<th style="width:30%">CLIENTE</th><th style="width:12%">VALOR TOTAL</th><th style="width:12%">PENDENTES</th><th style="width:12%">RECEBIDAS</th><th style="width:10%">CONTAS</th><th style="width:24%">TELEFONE</th>
</tr></thead><tbody>
<?php
if($mostrar_registros == 'Não'){
	$query9 = $pdo->query("SELECT * from $filtro_tabela where usuario = '$id_usuario' order by nome asc");
}else{
	$query9 = $pdo->query("SELECT * from $filtro_tabela order by nome asc");
}
$res9 = $query9->fetchAll(PDO::FETCH_ASSOC);
$linhas9 = @count($res9);
if($linhas9 > 0){
for($i9=0; $i9<$linhas9; $i9++){
	$id_pessoa = $res9[$i9]['id'];
	$nome_pessoa = $res9[$i9]['nome'];
	$tel_pessoa = $res9[$i9]['telefone'];


$total_valor = 0;
$total_valorF = 0;
$total_pendentes = 0;
$total_pendentesF = 0;
$total_pagas = 0;
$total_pagasF = 0;
$pendentes = 0;
$pagas = 0;

$query = $pdo->query("SELECT * from $filtro_tipo where $filtro_data >= '$dataInicial' and $filtro_data <= '$dataFinal' and pago LIKE '%$filtro_pendentes%' and $filtro_lancamento = '$id_pessoa' order by $filtro_data asc");
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


$total_valorF = @number_format($total_valor, 2, ',', '.');
$total_pagasF = @number_format($total_pagas, 2, ',', '.');
$total_pendentesF = @number_format($total_pendentes, 2, ',', '.');


if($data_pgtoF == '00/00/0000'){
	$data_pgtoF = 'Pendente';
}

} 

}else{
	//continue para ele pular para o proximo
	continue;
	//se usar break ele para toda instrução
}
  	 ?>

  	 
<tr>
<td style="width:30%"><?php echo htmlspecialchars($nome_pessoa, ENT_QUOTES, 'UTF-8'); ?></td>
<td style="width:12%">R$ <?php echo $total_valorF ?></td>
<td style="width:12%;color:#b42332">R$ <?php echo $total_pendentesF ?></td>
<td style="width:12%;color:#15803d">R$ <?php echo $total_pagasF ?></td>
<td style="width:10%"><?php echo $linhas ?></td>
<td style="width:24%"><?php echo htmlspecialchars($tel_pessoa, ENT_QUOTES, 'UTF-8'); ?></td>
</tr>

<?php } } ?>
</tbody></table></div>
</body></html>
