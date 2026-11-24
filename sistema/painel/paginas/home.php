<?php
$pag = 'home';


$sql_usuario = " ";
$sql_usuario_lanc = " ";

//total clientes

$query = $pdo->query("SELECT * from clientes where empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes = @count($res);

//total clientes mes
$query = $pdo->query("SELECT * from clientes where data_cad >= '$data_inicio_mes' and data_cad <= '$data_final_mes' and empresa = '$id_empresa' $sql_usuario");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_clientes_mes = @count($res);

$total_pagar_vencidas = 0;
$query = $pdo->query("SELECT * from pagar where vencimento < curDate() and pago != 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagar_vencidas = @count($res);
for ($i = 0; $i < $contas_pagar_vencidas; $i++) {
	$valor_1 = $res[$i]['valor'];
	$total_pagar_vencidas += $valor_1;
}
$total_pagar_vencidasF = @number_format($total_pagar_vencidas, 2, ',', '.');



$total_receber_vencidas = 0;
$query = $pdo->query("SELECT * from receber where vencimento < curDate() and pago != 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_receber_vencidas = @count($res);
for ($i = 0; $i < $contas_receber_vencidas; $i++) {
	$valor_2 = $res[$i]['valor'];
	$total_receber_vencidas += $valor_2;
}
$total_receber_vencidasF = @number_format($total_receber_vencidas, 2, ',', '.');


//total recebidas mes
$total_recebidas_mes = 0;
$query = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_recebidas_mes = @count($res);
for ($i = 0; $i < $contas_recebidas_mes; $i++) {
	$valor_3 = $res[$i]['valor'];
	$total_recebidas_mes += $valor_3;
}
$total_recebidas_mesF = @number_format($total_recebidas_mes, 2, ',', '.');

//total contas pagas mes
$total_pagas_mes = 0;
$query = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes' and data_pgto <= '$data_final_mes' and pago = 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagas_mes = @count($res);
for ($i = 0; $i < $contas_pagas_mes; $i++) {
	$valor_4 = $res[$i]['valor'];
	$total_pagas_mes += $valor_4;
}
$total_pagas_mesF = @number_format($total_pagas_mes, 2, ',', '.');


$total_saldo_mes = $total_recebidas_mes - $total_pagas_mes;
$total_saldo_mesF = @number_format($total_saldo_mes, 2, ',', '.');
if ($total_saldo_mes >= 0) {
	$classe_saldo = 'iconSusses';
	$classe_saldo2 = 'iconBgSusses';
	$classe_icon_dia = 'fa fa-caret-up text-success';
} else {
	$classe_saldo = 'iconDanger';
	$classe_saldo2 = 'iconBgDanger';
	$classe_icon_dia = 'fa fa-caret-down text-danger';
}




//total recebidas dia
$total_recebidas_dia = 0;
$query = $pdo->query("SELECT * from receber where data_pgto = curDate() and pago = 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_recebidas_dia = @count($res);
for ($i = 0; $i < $contas_recebidas_dia; $i++) {
	$valor_3 = $res[$i]['valor'];
	$total_recebidas_dia += $valor_3;
}
$total_recebidas_diaF = @number_format($total_recebidas_dia, 2, ',', '.');

//total contas pagas dia
$total_pagas_dia = 0;
$query = $pdo->query("SELECT * from pagar where data_pgto = curDate() and pago = 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$contas_pagas_dia = @count($res);
for ($i = 0; $i < $contas_pagas_dia; $i++) {
	$valor_4 = $res[$i]['valor'];
	$total_pagas_dia += $valor_4;
}
$total_pagas_diaF = @number_format($total_pagas_dia, 2, ',', '.');


$total_saldo_dia = $total_recebidas_dia - $total_pagas_dia;
$total_saldo_diaF = @number_format($total_saldo_dia, 2, ',', '.');
if ($total_saldo_dia >= 0) {
	$classe_saldo_dia = 'bg-success';
} else {
	$classe_saldo_dia = 'bg-danger';
}




//valores para o grafico de linhas (ano atual, despesas e recebimentos)
$total_meses_pagar_grafico = '';
$total_meses_receber_grafico = '';

for ($i = 1; $i <= 12; $i++) {
	if ($i < 10) {
		$mes_verificado = '0' . $i;
	} else {
		$mes_verificado = $i;
	}


	$data_inicio_mes_grafico = $ano_atual . "-" . $mes_verificado . "-01";

	if ($mes_verificado == '04' || $mes_verificado == '06' || $mes_verificado == '09' || $mes_verificado == '11') {
		$data_final_mes_grafico = $ano_atual . '-' . $mes_verificado . '-30';
	} else if ($mes_verificado == '02') {
		$bissexto = date('L', @mktime(0, 0, 0, 1, 1, $mes_verificado));
		if ($bissexto == 1) {
			$data_final_mes_grafico = $ano_atual . '-' . $mes_verificado . '-29';
		} else {
			$data_final_mes_grafico = $ano_atual . '-' . $mes_verificado . '-28';
		}
	} else {
		$data_final_mes_grafico = $ano_atual . '-' . $mes_verificado . '-31';
	}




	//total recebidas mes
	$total_recebidas_mes_grafico = 0;
	$query2 = $pdo->query("SELECT * from receber where data_pgto >= '$data_inicio_mes_grafico' and data_pgto <= '$data_final_mes_grafico' and pago = 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	$contas_recebidas_mes_grafico = @count($res2);
	for ($i2 = 0; $i2 < $contas_recebidas_mes_grafico; $i2++) {
		$valor_3 = $res2[$i2]['valor'];
		$total_recebidas_mes_grafico += $valor_3;
	}


	//total contas pagas mes
	$total_pagas_mes_grafico = 0;
	$query2 = $pdo->query("SELECT * from pagar where data_pgto >= '$data_inicio_mes_grafico' and data_pgto <= '$data_final_mes_grafico' and pago = 'Sim' and empresa = '$id_empresa' $sql_usuario_lanc");
	$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
	$contas_pagas_mes_grafico = @count($res2);
	for ($i2 = 0; $i2 < $contas_pagas_mes_grafico; $i2++) {
		$valor_4 = $res2[$i2]['valor'];
		$total_pagas_mes_grafico += $valor_4;
	}


	$total_meses_pagar_grafico .= $total_pagas_mes_grafico . '-';
	$total_meses_receber_grafico .= $total_recebidas_mes_grafico . '-';
}





//tarefas
$query = $pdo->query("SELECT * from tarefas where usuario = '$id_usuario' and status = 'Agendada' and data <= curDate() and hora < curTime() and empresa = '$id_empresa' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$tarefas_atrasadas = @count($res);

$query = $pdo->query("SELECT * from tarefas where usuario = '$id_usuario' and status = 'Agendada' and empresa = '$id_empresa' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_tarefas = @count($res);


$query = $pdo->query("SELECT * from tarefas where usuario = '$id_usuario' and status = 'Agendada' and data = curDate() and empresa = '$id_empresa' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$tarefas_agendadas_hoje = @count($res);

#####ANOTAÇÕES#####################################
$query = $pdo->query("SELECT * from anotacoes where empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
for ($i5 = 0; $i5 < $linhas; $i5++) {
	$msg = $res[$i5]['msg'];
	$titulo = $res[$i5]['titulo'];
}


//verificar se ele tem a permissão de estar nessa página
if (@$home == 'ocultar') {
	echo "<script>window.location='index'</script>";
	exit();
}


##### TOTAIS POR TIPO DE ATENDIMENTO ###################################
// Agregação no banco e exclusão de vendas canceladas.
$tiposAtendimento = [
    'Delivery' => ['quantidade' => 0, 'valor' => 0.0],
    'Consumir Local' => ['quantidade' => 0, 'valor' => 0.0],
    'Retirar' => ['quantidade' => 0, 'valor' => 0.0],
];
$totaisAtendimentoSt = $pdo->prepare("SELECT entrega, COUNT(*) quantidade,
    COALESCE(SUM(valor),0) valor
    FROM vendas WHERE empresa=:empresa
    AND (status IS NULL OR status <> 'Cancelado')
    AND entrega IN ('Delivery','Consumir Local','Retirar')
    GROUP BY entrega");
$totaisAtendimentoSt->execute([':empresa'=>(int)$id_empresa]);
foreach ($totaisAtendimentoSt->fetchAll(PDO::FETCH_ASSOC) as $linhaAtendimento) {
    $tipoAtendimento = $linhaAtendimento['entrega'];
    $tiposAtendimento[$tipoAtendimento] = [
        'quantidade' => (int)$linhaAtendimento['quantidade'],
        'valor' => (float)$linhaAtendimento['valor']
    ];
}
$linhas1 = $tiposAtendimento['Delivery']['quantidade'];
$linhas2 = $tiposAtendimento['Consumir Local']['quantidade'];
$linhas3 = $tiposAtendimento['Retirar']['quantidade'];
$valor_delivery = $tiposAtendimento['Delivery']['valor'];
$valor_local = $tiposAtendimento['Consumir Local']['valor'];
$valor_retirada = $tiposAtendimento['Retirar']['valor'];
$valor_deliveryF = number_format($valor_delivery, 2, ',', '.');
$valor_localF = number_format($valor_local, 2, ',', '.');
$valor_retiradaF = number_format($valor_retirada, 2, ',', '.');
$totalPedidosAtendimento = $linhas1 + $linhas2 + $linhas3;
$porcent_deli = $totalPedidosAtendimento > 0 ? round($linhas1 * 100 / $totalPedidosAtendimento, 2) : 0;
$porcent_local = $totalPedidosAtendimento > 0 ? round($linhas2 * 100 / $totalPedidosAtendimento, 2) : 0;
$porcent_retirada = $totalPedidosAtendimento > 0 ? round($linhas3 * 100 / $totalPedidosAtendimento, 2) : 0;


##### PUXAR TOTAL DA MESA ############################################
$valor_mesa = 0;
$query = $pdo->query("SELECT * from abertura_mesa where empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas4 = @count($res);
for ($i6 = 0; $i6 < $linhas4; $i6++) {
	$valor_mesa += $res[$i6]['total'];
}
$valor_mesaF = @number_format($valor_mesa, 2, ',', '.');
$porcent_mesa = $linhas4 / 100;



$dashboardCaixa=$pdo->prepare("SELECT id,data_abertura FROM caixas
    WHERE empresa=:empresa AND operador=:operador AND data_fechamento IS NULL
    ORDER BY id DESC LIMIT 1");
$dashboardCaixa->execute([':empresa'=>(int)$id_empresa,':operador'=>(int)$id_usuario]);
$dashboardCaixaAberto=$dashboardCaixa->fetch(PDO::FETCH_ASSOC);
$dashboardVendasHoje=$pdo->prepare("SELECT COUNT(*) quantidade,COALESCE(SUM(valor),0) total
    FROM vendas WHERE empresa=:empresa AND data=CURDATE()
    AND (status IS NULL OR status<>'Cancelado')");
$dashboardVendasHoje->execute([':empresa'=>(int)$id_empresa]);
$dashboardResumoHoje=$dashboardVendasHoje->fetch(PDO::FETCH_ASSOC);
$dashboardReceberHoje=$pdo->prepare("SELECT COUNT(*) quantidade,COALESCE(SUM(valor),0) total
    FROM receber WHERE empresa=:empresa AND vencimento=CURDATE() AND pago<>'Sim'");
$dashboardReceberHoje->execute([':empresa'=>(int)$id_empresa]);
$dashboardPendenciasHoje=$dashboardReceberHoje->fetch(PDO::FETCH_ASSOC);
$dashboardCaixasResumo=$pdo->prepare("SELECT
    SUM(CASE WHEN data_fechamento IS NULL THEN 1 ELSE 0 END) abertos,
    SUM(CASE WHEN data_fechamento=CURDATE() THEN 1 ELSE 0 END) fechados_hoje
    FROM caixas WHERE empresa=:empresa");
$dashboardCaixasResumo->execute([':empresa'=>(int)$id_empresa]);
$dashboardCaixas=$dashboardCaixasResumo->fetch(PDO::FETCH_ASSOC);
// Período independente dos indicadores históricos do painel.
$dashboardPeriodo=$_GET['periodo_dash'] ?? 'hoje';
$dashboardDias=['hoje'=>0,'7dias'=>6,'30dias'=>29,'personalizado'=>null];
if (!array_key_exists($dashboardPeriodo,$dashboardDias)) $dashboardPeriodo='hoje';
$dashboardHoje=new DateTimeImmutable('today');
$dashboardInicio=$dashboardHoje->modify('-'.($dashboardDias[$dashboardPeriodo] ?? 0).' days')->format('Y-m-d');
$dashboardFim=$dashboardHoje->format('Y-m-d');
$dashboardErroPeriodo='';
if ($dashboardPeriodo==='personalizado') {
    $inicioDigitado=(string)($_GET['inicio_dash'] ?? '');
    $fimDigitado=(string)($_GET['fim_dash'] ?? '');
    $inicioData=DateTimeImmutable::createFromFormat('!Y-m-d',$inicioDigitado);
    $fimData=DateTimeImmutable::createFromFormat('!Y-m-d',$fimDigitado);
    if ($inicioData && $fimData && $inicioData->format('Y-m-d')===$inicioDigitado
        && $fimData->format('Y-m-d')===$fimDigitado && $inicioData<=$fimData
        && $fimData<=$dashboardHoje && $inicioData>=$dashboardHoje->modify('-366 days')) {
        $dashboardInicio=$inicioDigitado;
        $dashboardFim=$fimDigitado;
    } else {
        $dashboardErroPeriodo='Informe datas válidas, até hoje, em um intervalo dos últimos 366 dias.';
        $dashboardPeriodo='hoje';
    }
}
$dashboardFaturamentoSt=$pdo->prepare("SELECT COUNT(*) quantidade,COALESCE(SUM(valor),0) total
 FROM vendas WHERE empresa=:empresa AND data BETWEEN :inicio AND :fim
 AND (status IS NULL OR status<>'Cancelado')");
$dashboardFaturamentoSt->execute([':empresa'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardFaturamento=$dashboardFaturamentoSt->fetch(PDO::FETCH_ASSOC);
$dashboardRecebidoSt=$pdo->prepare("SELECT COUNT(*) quantidade,COALESCE(SUM(COALESCE(subtotal,valor)),0) total
 FROM receber WHERE empresa=:empresa AND pago='Sim' AND data_pgto BETWEEN :inicio AND :fim");
$dashboardRecebidoSt->execute([':empresa'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardRecebido=$dashboardRecebidoSt->fetch(PDO::FETCH_ASSOC);
$dashboardEstornosSt=$pdo->prepare("SELECT COUNT(*) quantidade,COALESCE(SUM(valor_recebido),0) total
 FROM estornos_vendas WHERE empresa=:empresa AND status='Concluido'
 AND DATE(criado_em) BETWEEN :inicio AND :fim");
$dashboardEstornosSt->execute([':empresa'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardEstornos=$dashboardEstornosSt->fetch(PDO::FETCH_ASSOC);
$dashboardRecebidoLiquido=(float)$dashboardRecebido['total']-(float)$dashboardEstornos['total'];
$dashboardTicketPeriodo=(int)$dashboardFaturamento['quantidade']>0
 ? (float)$dashboardFaturamento['total']/(int)$dashboardFaturamento['quantidade'] : 0.0;

$dashboardAlertasSt=$pdo->prepare("SELECT
 (SELECT COUNT(*) FROM pagar WHERE empresa=:empresa1 AND pago<>'Sim' AND vencimento<CURDATE()) contas_pagar,
 (SELECT COUNT(*) FROM receber WHERE empresa=:empresa2 AND pago<>'Sim' AND vencimento<CURDATE()) contas_receber,
 (SELECT COUNT(*) FROM caixas WHERE empresa=:empresa3 AND data_fechamento IS NOT NULL AND ABS(COALESCE(quebra,0))>0.009 AND data_fechamento BETWEEN :inicio AND :fim) caixas_divergentes");
$dashboardAlertasSt->execute([':empresa1'=>(int)$id_empresa,':empresa2'=>(int)$id_empresa,':empresa3'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardAlertas=$dashboardAlertasSt->fetch(PDO::FETCH_ASSOC);
$dashboardSerieSt=$pdo->prepare("SELECT data,COUNT(*) quantidade,COALESCE(SUM(valor),0) total
 FROM vendas WHERE empresa=:empresa AND data BETWEEN :inicio AND :fim
 AND (status IS NULL OR status<>'Cancelado') GROUP BY data ORDER BY data");
$dashboardSerieSt->execute([':empresa'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardSerie=$dashboardSerieSt->fetchAll(PDO::FETCH_ASSOC);
// Preenche dias sem vendas para o gráfico representar todo o intervalo,
 // inclusive quando o faturamento é zero.
$dashboardSeriePorData=[];
foreach ($dashboardSerie as $ponto) $dashboardSeriePorData[$ponto['data']]=$ponto;
$dashboardSerie=[];
$dashboardDiaCursor=new DateTimeImmutable($dashboardInicio);
$dashboardDiaFim=new DateTimeImmutable($dashboardFim);
while ($dashboardDiaCursor<=$dashboardDiaFim) {
    $diaChave=$dashboardDiaCursor->format('Y-m-d');
    $dashboardSerie[]=$dashboardSeriePorData[$diaChave] ?? ['data'=>$diaChave,'quantidade'=>0,'total'=>0];
    $dashboardDiaCursor=$dashboardDiaCursor->modify('+1 day');
}
$dashboardSerieMax=1.0;
foreach ($dashboardSerie as $ponto) $dashboardSerieMax=max($dashboardSerieMax,(float)$ponto['total']);
$dashboardFormasSt=$pdo->prepare("SELECT f.nome,COUNT(*) quantidade,COALESCE(SUM(COALESCE(r.subtotal,r.valor)),0) total
 FROM receber r INNER JOIN formas_pgto f ON f.id=r.forma_pgto AND f.empresa=r.empresa
 WHERE r.empresa=:empresa AND r.pago='Sim' AND r.data_pgto BETWEEN :inicio AND :fim
 GROUP BY f.id,f.nome ORDER BY total DESC");
$dashboardFormasSt->execute([':empresa'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardFormas=$dashboardFormasSt->fetchAll(PDO::FETCH_ASSOC);
$dashboardTopProdutosSt=$pdo->prepare("SELECT p.nome,COALESCE(SUM(c.quantidade),0) quantidade
 FROM carrinho c INNER JOIN vendas v ON v.id=c.pedido AND v.empresa=c.empresa
 INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa
 WHERE c.empresa=:empresa AND v.data BETWEEN :inicio AND :fim
 AND (v.status IS NULL OR v.status<>'Cancelado')
 GROUP BY p.id,p.nome ORDER BY quantidade DESC,p.nome ASC LIMIT 5");
$dashboardTopProdutosSt->execute([':empresa'=>(int)$id_empresa,':inicio'=>$dashboardInicio,':fim'=>$dashboardFim]);
$dashboardTopProdutos=$dashboardTopProdutosSt->fetchAll(PDO::FETCH_ASSOC);
$dashboardTicketMedio=(int)$dashboardResumoHoje['quantidade']>0
    ? (float)$dashboardResumoHoje['total']/(int)$dashboardResumoHoje['quantidade'] : 0.0;



?>


<style id="bc-dashboard-mobile">
@media(max-width:1023px){
html body .bc-dashboard-mobile{min-width:0;max-width:100%}
html body .bc-dashboard-mobile .ux-home-intro{display:flex!important;flex-direction:column!important;align-items:stretch!important;gap:12px!important;padding:16px!important}
html body .bc-dashboard-mobile .ux-home-intro h1{font-size:clamp(20px,4vw,26px)!important;line-height:1.25!important}
html body .bc-dashboard-mobile .ux-home-shortcuts{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px!important;width:100%!important}
html body .bc-dashboard-mobile .ux-shortcut{display:flex!important;flex-direction:column;align-items:center;justify-content:center;min-height:48px;min-width:0;padding:8px 4px!important;font-size:12px!important;white-space:normal!important;text-align:center}
html body .bc-dashboard-mobile .bc-dashboard-filter{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px!important;padding:12px;border:1px solid #e5d4c6;border-radius:12px;background:#fff}
html body .bc-dashboard-mobile .bc-dashboard-filter>label[for=periodo_dash],html body .bc-dashboard-mobile .bc-dashboard-filter>select,html body .bc-dashboard-mobile .bc-dashboard-filter>.text-muted{grid-column:1/-1}
html body .bc-dashboard-mobile .bc-dashboard-filter :is(input,select){width:100%!important;min-width:0;min-height:42px;color-scheme:light}
html body .bc-dashboard-mobile .bc-dashboard-filter label[for=inicio_dash]{grid-column:1;grid-row:4}
html body .bc-dashboard-mobile .bc-dashboard-filter label[for=fim_dash]{grid-column:2;grid-row:4}
html body .bc-dashboard-mobile .bc-dashboard-filter input{grid-row:5}
html body .bc-dashboard-mobile .bc-dashboard-filter .btn{min-height:44px;display:flex;align-items:center;justify-content:center}
html body .bc-dashboard-mobile .row[aria-label]{--bs-gutter-x:10px;--bs-gutter-y:10px}
html body .bc-dashboard-mobile .row[aria-label]>div{min-width:0}
html body .bc-dashboard-mobile .row[aria-label] .card{margin-bottom:0!important;min-width:0;overflow-wrap:break-word}
html body .bc-dashboard-mobile .row[aria-label] .fs-5{font-size:clamp(17px,3.5vw,22px)!important;line-height:1.3}
html body .bc-dashboard-mobile .sales-card{height:auto!important;min-height:120px}
html body .bc-dashboard-mobile .sales-card .col-8{width:76%!important;min-width:0}
html body .bc-dashboard-mobile .sales-card .col-4{width:24%!important;min-width:0}
html body .bc-dashboard-mobile .sales-card .col-8>div{padding:14px!important}
html body .bc-dashboard-mobile .sales-card h4{white-space:normal!important;overflow-wrap:break-word;font-size:18px!important}
html body .bc-dashboard-mobile .sales-card .circle-icon{max-width:40px;max-height:40px;margin-inline:auto!important}
html body .bc-dashboard-mobile .sales-card p{white-space:normal!important;line-height:1.5}
html body .bc-dashboard-mobile .ux-home-secondary-row .sales-card p span[style*="margin-left"]{display:block;margin-left:0!important}
html body .bc-dashboard-mobile .bc-dashboard-annual{display:block!important}
html body .bc-dashboard-mobile #statistics1{min-width:0;max-width:100%}
html body .bc-dashboard-mobile .bc-dashboard-daily{max-width:100%;overflow-x:auto!important;overscroll-behavior-x:contain;padding-bottom:8px}
html body .bc-dashboard-mobile .table-responsive{max-width:100%;overflow-x:auto}
html body .bc-dashboard-mobile .table-responsive td:nth-child(2){white-space:normal!important;overflow-wrap:break-word}
html body .bc-dashboard-mobile .table-responsive th{white-space:normal!important}
}
@media(max-width:767px){
html body .bc-dashboard-mobile .row[aria-label="Resumo financeiro do período"]>div,html body .bc-dashboard-mobile .row[aria-label="Resumo operacional"]>div,html body .bc-dashboard-mobile .row[aria-label="Indicadores complementares"]>div{width:50%!important}
html body .bc-dashboard-mobile .row[aria-label="Resumo financeiro do período"]>div:last-child,html body .bc-dashboard-mobile .row[aria-label="Resumo operacional"]>div:last-child{width:100%!important}
html body .bc-dashboard-mobile .row[aria-label] .card{padding:12px!important}
html body .bc-dashboard-mobile .ux-home-kpi-row>div{width:100%!important}
html body .bc-dashboard-mobile .bc-dashboard-annual .card-body{padding:8px!important}
}
</style>
<div class="bc-dashboard-mobile">
<section class="ux-home-intro" aria-label="Resumo do painel">
  <div>
    <span class="ux-home-eyebrow"><i class="fa fa-chart-line" aria-hidden="true"></i> Visão geral</span>
    <h1>Seu negócio em um só lugar</h1>
    <p>Acompanhe os resultados e acesse rapidamente as principais áreas do sistema.</p>
  </div>
  <div class="ux-home-shortcuts" aria-label="Atalhos">
    <a href="clientes" class="ux-shortcut"><i class="fa fa-users" aria-hidden="true"></i> Clientes</a>
    <a href="receber" class="ux-shortcut"><i class="fa fa-wallet" aria-hidden="true"></i> Recebimentos</a>
    <a href="pagar" class="ux-shortcut"><i class="fa fa-file-invoice-dollar" aria-hidden="true"></i> Despesas</a>
  </div>
</section>
<form method="get" class="d-flex flex-wrap align-items-center gap-2 mt-3 mb-2 bc-dashboard-filter" aria-label="Filtrar indicadores financeiros">
  <label for="periodo_dash" class="fw-semibold mb-0">Período dos indicadores:</label>
  <select name="periodo_dash" id="periodo_dash" class="form-select form-select-sm" style="width:auto">
    <option value="hoje" <?php echo $dashboardPeriodo==='hoje'?'selected':''; ?>>Hoje</option>
    <option value="7dias" <?php echo $dashboardPeriodo==='7dias'?'selected':''; ?>>Últimos 7 dias</option>
    <option value="30dias" <?php echo $dashboardPeriodo==='30dias'?'selected':''; ?>>Últimos 30 dias</option>
    <option value="personalizado" <?php echo $dashboardPeriodo==='personalizado'?'selected':''; ?>>Personalizado</option>
  </select>
  <span class="small text-muted">Datas personalizadas:</span>
  <label class="small mb-0" for="inicio_dash">De</label>
  <input type="date" id="inicio_dash" name="inicio_dash" class="form-control form-control-sm" style="width:auto" value="<?php echo htmlspecialchars($dashboardInicio,ENT_QUOTES,'UTF-8'); ?>">
  <label class="small mb-0" for="fim_dash">Até</label>
  <input type="date" id="fim_dash" name="fim_dash" class="form-control form-control-sm" style="width:auto" value="<?php echo htmlspecialchars($dashboardFim,ENT_QUOTES,'UTF-8'); ?>">
  <button class="btn btn-primary btn-sm" type="submit">Aplicar</button>
  <a href="home" class="btn btn-outline-secondary btn-sm">Limpar filtro</a>
</form>
<p class="small text-muted mb-2">Indicadores financeiros: <?php echo date('d/m/Y',strtotime($dashboardInicio)); ?> a <?php echo date('d/m/Y',strtotime($dashboardFim)); ?>. Os demais indicadores podem usar períodos próprios.</p>
<?php if ($dashboardErroPeriodo!==''): ?><div class="alert alert-warning" role="alert"><?php echo htmlspecialchars($dashboardErroPeriodo,ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
<div class="row g-3 mb-3" aria-label="Resumo financeiro do período">
  <div class="col-12 col-md-4"><div class="card h-100 p-3">
    <div class="small text-muted">Faturamento (vendas realizadas)</div>
    <div class="fs-5 fw-bold">R$ <?php echo number_format((float)$dashboardFaturamento['total'],2,',','.'); ?></div>
    <div class="small text-muted"><?php echo (int)$dashboardFaturamento['quantidade']; ?> vendas, sem canceladas</div>
  </div></div>
  <div class="col-12 col-md-4"><div class="card h-100 p-3">
    <div class="small text-muted">Recebimentos confirmados</div>
    <div class="fs-5 fw-bold">R$ <?php echo number_format((float)$dashboardRecebido['total'],2,',','.'); ?></div>
    <div class="small text-muted">Inclui contas recebidas no período</div>
  </div></div>
  <div class="col-12 col-md-4"><div class="card h-100 p-3">
    <div class="small text-muted">Ticket médio do período</div>
    <div class="fs-5 fw-bold">R$ <?php echo number_format($dashboardTicketPeriodo,2,',','.'); ?></div>
    <div class="small text-muted">Faturamento dividido pelas vendas</div>
  </div></div>
</div>
<div class="row g-3 mb-3" aria-label="Estornos e recebimento líquido">
  <div class="col-12 col-md-6"><div class="card h-100 p-3">
    <div class="small text-muted">Estornos concluídos no período</div>
    <div class="fs-5 fw-bold">R$ <?php echo number_format((float)$dashboardEstornos['total'],2,',','.'); ?></div>
    <div class="small text-muted"><?php echo (int)$dashboardEstornos['quantidade']; ?> estorno(s) concluído(s)</div>
  </div></div>
  <div class="col-12 col-md-6"><div class="card h-100 p-3">
    <div class="small text-muted">Recebimentos menos estornos concluídos</div>
    <div class="fs-5 fw-bold">R$ <?php echo number_format($dashboardRecebidoLiquido,2,',','.'); ?></div>
    <div class="small text-muted">Indicador gerencial; não equivale ao saldo de caixa</div>
  </div></div>
</div>
<section class="card p-3 mb-3" aria-label="Alertas operacionais">
  <h5 class="mb-2"><i class="fa fa-exclamation-circle me-2"></i>Alertas operacionais</h5>
  <div class="row g-2">
    <div class="col-12 col-md-4"><a href="pagar" class="d-block text-decoration-none">Contas a pagar vencidas: <strong><?php echo (int)$dashboardAlertas['contas_pagar']; ?></strong></a></div>
    <div class="col-12 col-md-4"><a href="receber" class="d-block text-decoration-none">Contas a receber vencidas: <strong><?php echo (int)$dashboardAlertas['contas_receber']; ?></strong></a></div>
    <div class="col-12 col-md-4">Fechamentos com diferença no período: <strong><?php echo (int)$dashboardAlertas['caixas_divergentes']; ?></strong></div>
  </div>
</section>
<div class="row g-3 mb-3" aria-label="Análise de vendas e pagamentos">
  <div class="col-12 col-lg-7"><section class="card p-3 h-100">
    <h5>Evolução do faturamento</h5>
    <div class="d-flex align-items-end gap-2 overflow-auto bc-dashboard-daily" style="min-height:170px" role="img" aria-label="Barras de faturamento por dia">
    <?php foreach ($dashboardSerie as $ponto): ?>
      <div class="text-center" style="min-width:55px;flex:1">
        <div class="small text-muted">R$ <?php echo number_format((float)$ponto['total'],2,',','.'); ?></div>
        <div class="bg-primary rounded mx-auto" style="height:<?php echo max(3,round((float)$ponto['total']/$dashboardSerieMax*105)); ?>px;max-width:45px"></div>
        <div class="small text-muted mt-1"><?php echo date('d/m',strtotime($ponto['data'])); ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$dashboardSerie): ?><p class="text-muted">Sem vendas no período.</p><?php endif; ?>
    </div>
  </section></div>
  <div class="col-12 col-lg-5"><section class="card p-3 h-100">
    <h5>Recebimentos por forma de pagamento</h5>
    <?php foreach ($dashboardFormas as $forma): ?>
      <div class="d-flex justify-content-between gap-2 border-bottom py-2">
        <span><?php echo htmlspecialchars($forma['nome'],ENT_QUOTES,'UTF-8'); ?></span>
        <strong>R$ <?php echo number_format((float)$forma['total'],2,',','.'); ?></strong>
      </div>
    <?php endforeach; ?>
    <?php if (!$dashboardFormas): ?><p class="text-muted">Sem recebimentos no período.</p><?php endif; ?>
  </section></div>
</div>
<section class="card p-3 mb-3" aria-label="Produtos mais vendidos">
  <h5>Top 5 produtos mais vendidos</h5>
  <div class="table-responsive"><table class="table table-sm align-middle mb-0">
    <thead><tr><th scope="col">Posição</th><th scope="col">Produto</th><th scope="col" class="text-end">Quantidade</th></tr></thead>
    <tbody>
    <?php foreach ($dashboardTopProdutos as $posicao=>$produto): ?>
      <tr><td><?php echo $posicao+1; ?>º</td>
        <td><?php echo htmlspecialchars($produto['nome'],ENT_QUOTES,'UTF-8'); ?></td>
        <td class="text-end"><?php echo number_format((float)$produto['quantidade'],2,',','.'); ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$dashboardTopProdutos): ?><tr><td colspan="3" class="text-muted">Nenhum produto vendido no período.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<div class="row g-3 mt-2 mb-3" aria-label="Resumo operacional">
  <div class="col-12 col-md-4">
    <div class="card h-100 p-3">
      <div class="text-muted small"><i class="fa fa-cash-register me-2"></i>Meu caixa</div>
      <div class="fs-5 fw-bold mt-1"><?php echo $dashboardCaixaAberto ? 'Aberto' : 'Fechado'; ?></div>
      <div class="small text-muted"><?php echo $dashboardCaixaAberto ? 'Caixa #'.(int)$dashboardCaixaAberto['id'] : 'Nenhum caixa aberto para seu usuário'; ?></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card h-100 p-3">
      <div class="text-muted small"><i class="fa fa-shopping-cart me-2"></i>Vendas de hoje</div>
      <div class="fs-5 fw-bold mt-1">R$ <?php echo number_format((float)$dashboardResumoHoje['total'],2,',','.'); ?></div>
      <div class="small text-muted"><?php echo (int)$dashboardResumoHoje['quantidade']; ?> venda(s), sem canceladas</div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card h-100 p-3">
      <div class="text-muted small"><i class="fa fa-clock me-2"></i>Recebimentos vencendo hoje</div>
      <div class="fs-5 fw-bold mt-1">R$ <?php echo number_format((float)$dashboardPendenciasHoje['total'],2,',','.'); ?></div>
      <div class="small text-muted"><?php echo (int)$dashboardPendenciasHoje['quantidade']; ?> conta(s) ainda pendente(s)</div>
    </div>
  </div>
</div>
<div class="row g-3 mb-3" aria-label="Indicadores complementares">
  <div class="col-12 col-md-6">
    <div class="card h-100 p-3">
      <div class="text-muted small"><i class="fa fa-receipt me-2"></i>Ticket médio de hoje</div>
      <div class="fs-5 fw-bold mt-1">R$ <?php echo number_format($dashboardTicketMedio,2,',','.'); ?></div>
      <div class="small text-muted">Valor médio por venda não cancelada</div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card h-100 p-3">
      <div class="text-muted small"><i class="fa fa-cash-register me-2"></i>Caixas da empresa</div>
      <div class="fs-5 fw-bold mt-1"><?php echo (int)$dashboardCaixas['abertos']; ?> aberto(s)</div>
      <div class="small text-muted"><?php echo (int)$dashboardCaixas['fechados_hoje']; ?> fechado(s) hoje</div>
    </div>
  </div>
</div>
<div class="mt-4 justify-content-between">


	<?php if ($ativo_sistema == '') { ?>

		<div style="background: #ffc341; color:#3e3e3e; padding:10px; font-size:14px; margin-bottom:10px">

			<div><i class="fa fa-info-circle"></i> <b>Aviso: </b> Prezado Cliente, não identificamos o pagamento de sua última
				mensalidade, entre em contato conosco o mais rápido possivel para regularizar o pagamento, caso contário seu
				acesso ao sistema será desativado.</div>

		</div>

	<?php } ?>




	<div class="row ux-home-kpi-row">

		<div class="col-xl-3 col-lg-12 col-md-6 col-xs-12">
			<div class="card sales-card"
				style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); height:130px; border-radius: 10px;">
				<a href="clientes">
					<div class="row">
						<div class="col-8">
							<div class="ps-4 pt-4 pe-3 pb-4">
								<div class="">
									<p class="mb-1">Clientes Cadastrados</p>
								</div>
								<div class="pb-0 mt-0">
									<div class="d-flex">
										<h4 class="tx-20 font-weight-semibold mb-2"><?php echo $total_clientes ?></h4>
									</div>
									<p class="mb-0 tx-12 text-muted">Este Mês<i class="fa fa-caret-up mx-1 text-success"></i>
										<span class="iconSusses tx-11"><?php echo $total_clientes_mes ?></span>
									</p>
								</div>
							</div>
						</div>
						<div class="col-4">

							<div class=" zoom circle-icon iconBgSusses text-center align-self-center overflow-hidden">
								<i class="fe fe-user tx-16 text-white"></i>
							</div>

						</div>
					</div>
				</a>
			</div>
		</div>


		<div class="col-xl-3 col-lg-12 col-md-6 col-xs-12">
			<div class="card sales-card"
				style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); height:130px; border-radius: 10px">
				<a href="pagar">
					<div class="row">
						<div class="col-8">
							<div class="ps-4 pt-4 pe-3 pb-4">
								<div class="">
									<p class=" mb-1">Despesas Vencidas</p>
								</div>
								<div class="pb-0 mt-0">
									<div class="d-flex">
										<h4 class="tx-20 font-weight-semibold mb-2">R$ <?php echo $total_pagar_vencidasF ?></h4>
									</div>
									<p class="mb-0 tx-12 text-muted">Pagar Vencidas<i class="fa fa-caret-down mx-1 text-danger"></i>
										<span class="iconDanger tx-11"><?php echo $contas_pagar_vencidas ?></span>
									</p>
								</div>
							</div>
						</div>
						<div class="col-4">

							<div class="zoom circle-icon iconBgDanger text-center align-self-center overflow-hidden">
								<i class="bi bi-currency-dollar tx-16 text-white"></i>
							</div>

						</div>
					</div>
				</a>
			</div>
		</div>
		<div class="col-xl-3 col-lg-12 col-md-6 col-xs-12">
			<div class="card sales-card"
				style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); height:130px; border-radius: 10px">
				<a href="receber">
					<div class="row">
						<div class="col-8">
							<div class="ps-4 pt-4 pe-3 pb-4">
								<div class="">
									<p class=" mb-1">Receber Vencidas</p>
								</div>
								<div class="pb-0 mt-0">
									<div class="d-flex">
										<h4 class="tx-20 font-weight-semibold mb-2">R$ <?php echo $total_receber_vencidasF ?></h4>
									</div>
									<p class="mb-0 tx-12 text-muted">Receb. Vencidas<i class="fa fa-caret-up mx-1 text-success"></i>
										<span class="iconSusses tx-11"><?php echo $contas_receber_vencidas ?></span>
									</p>

								</div>
							</div>
						</div>
						<div class="col-4">

							<div class=" zoom circle-icon iconBgSusses text-center align-self-center overflow-hidden">
								<i class="bi bi-currency-dollar tx-16 text-white"></i>
							</div>

						</div>
					</div>
				</a>
			</div>
		</div>

		<div class="col-xl-3 col-lg-12 col-md-6 col-xs-12">
			<div class="card sales-card"
				style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1); height:130px; border-radius: 10px">
				<a href="receber">
					<div class="row">
						<div class="col-8">
							<div class="ps-4 pt-4 pe-3 pb-4">
								<div class="">
									<p class=" mb-1">Saldo no Mês</p>
								</div>
								<div class="pb-0 mt-0">
									<div class="d-flex">
										<h4 class="tx-20 font-weight-semibold mb-2 <?php echo $classe_saldo ?>">R$
											<?php echo $total_saldo_mesF ?>
										</h4>
									</div>
									<p class="mb-0 tx-12 text-muted">Saldo Hoje<i class="mx-1 <?php echo $classe_icon_dia ?>"></i>
										<span class="iconHome tx-11 <?php echo $classe_saldo ?> tx-11">R$
											<?php echo $total_saldo_diaF ?></span>
									</p>

								</div>
							</div>
						</div>
						<div class="col-4">

							<div class=" zoom circle-icon text-center align-self-center overflow-hidden <?php echo $classe_saldo2 ?>">
								<i class="bi bi-currency-dollar tx-16 text-white"></i>
							</div>

						</div>
					</div>
				</a>
			</div>
		</div>
	</div>


	<div id="statistics2"></div>



	<!-- ################################ FIM PAINEL DE INFORMAÇÕES ###################################### -->
	<?php

	$query = $pdo->query("SELECT * from anotacoes where mostrar_home = 'Sim' and empresa = '$id_empresa' order by id desc");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$linhas = @count($res);
	if ($linhas > 0) {

		echo <<<HTML
<small>

<div class='row'>
	
	<thead style="margin-bottom: 50px"> 
		
		
			<div class="col-xl-12" style="margin-bottom: -17px">
			<div class="card sales-card border border-dark" style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.2);">
			

<div  style="text-align:center; font-size: 20px; padding: 4px"><strong>PAINEL DE INFORMAÇÕES</strong> </div>
				
			</div>


	</div>


	</thead> 
	<tbody>	


		
HTML;

		for ($i = 0; $i < $linhas; $i++) {
			$id = $res[$i]['id'];
			$titulo = $res[$i]['titulo'];
			$msg = $res[$i]['msg'];
			$usuario = $res[$i]['usuario'];
			$data = $res[$i]['data'];


			$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario' and empresa = '$id_empresa'");
			$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
			if (@count($res2) > 0) {
				$nome_usuario = $res2[0]['nome'];
			} else {
				$nome_usuario = 'Sem Usuário';
			}

			$dataF = implode('/', array_reverse(@explode('-', $data)));


			$msgF = mb_strimwidth($msg, 0, 250, "...");


			echo <<<HTML
<tr>

			<div class="col-xl-12" style="margin-bottom: -18px;">
			<div class="card sales-card border border-white" style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1);">
			

<div style="font-size: 12px; padding: 4px"><b>{$titulo}:</b> {$msgF}</div>
				
			</div>


	</div>


</tr>
HTML;
		}
	}




	echo <<<HTML
</tbody>
</div>

HTML;
	?>


	<!-- ################################ FIM PAINEL DE INFORMAÇÕES ###################################### -->







	<div class="card custom-card overflow-hidden ocultar_mobile bc-dashboard-annual">
		<div class="card-header border-bottom-0">
			<div>
				<h3 class="card-title mb-2 ">Recebimentos / Despesas <?php echo $ano_atual ?></h3> <span
					class="d-block tx-12 mb-0 text-muted"></span>
			</div>
		</div>
		<div class="card-body">
			<div id="statistics1"></div>
		</div>
	</div>


</div>




<div class="row ux-home-secondary-row">


	<div class="col-12 col-md-6 col-lg-6 col-xl-6">
		<a href="tarefas">
			<div class="card sales-card " style="box-shadow: 2px 2px 0px 0px rgba(0, 0, 0, 0.1);">
				<div class="row">

					<div class="col-8">
						<div class="ps-4 pt-4 pe-3 pb-4">
							<div class="">
								<h6 class="mb-2 tx-12 ">Total de Tarefas Pendentes</h6>
							</div>
							<div class="pb-0 mt-0">
								<div class="d-flex">
									<h4 class="tx-20 font-weight-semibold mb-2 text-danger"><?php echo $total_tarefas ?></h4>
								</div>
								<p class="mb-0 tx-12 text-muted">Pendentes Hoje<i class="fa fa-caret-up mx-2 text-success"></i>
									<span class="text-success font-weight-semibold"> <?php echo $tarefas_agendadas_hoje ?></span>

									<span style="margin-left: 20px">Tarefas Atrasadas<i class="fa fa-caret-down mx-2 text-danger"></i>
										<span class="text-danger font-weight-semibold"> <?php echo $tarefas_atrasadas ?></span></span>
								</p>

							</div>
						</div>
					</div>

					<div class="col-4">
						<div class="circle-icon bg-primary-transparent text-center align-self-center overflow-hidden">
							<i class="bi bi-card-checklist tx-16 text-primary"></i>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>

	<div class="col-12 col-md-6 col-lg-6 col-xl-6">
		<div class="card">
			<div class="card-header pb-3">
				<h3 class="card-title mb-2">Vendas / DELIVERY</h3>
			</div>
			<div class="card-body p-0 customers mt-1">
				<div class="country-card pt-0">
					<div class="mb-4">

						<div class="d-flex">
							<span class="tx-13 font-weight-semibold">Delivery</span>
							<div class="ms-auto"><span class="text-info mx-1"><i class="fa-solid fa-dollar-sign"></i></span><span
									class="number-font"><b> R$ <?php echo $valor_deliveryF ?></b></span> (<?php echo $porcent_deli ?>%)</div>
						</div>
						<div class="progress ht-8 br-5 mt-2">
							<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: <?php echo $porcent_deli ?>%"></div>
						</div>
					</div>
					<div class="mb-4">
						<div class="d-flex mb-1">
							<span class="tx-13 font-weight-semibold">Consumir Local</span>
							<div class="ms-auto"><span class="text-info mx-1"><i class="fa-solid fa-dollar-sign"></i></span><span
									class="number-font"><b> R$ <?php echo $valor_localF ?></b></span> (<?php echo $porcent_local ?>%)</div>
						</div>
						<div class="progress ht-8 br-5 mt-2">
							<div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: <?php echo $porcent_local ?>%"></div>
						</div>
					</div>
					<div class="mb-4">
						<div class="d-flex">
							<span class="tx-13 font-weight-semibold">Retirada</span>
							<div class="ms-auto"><span class="text-info mx-1"><i class="fa-solid fa-dollar-sign"></i></span><span
									class="number-font"><b> R$ <?php echo $valor_retiradaF ?></b></span> (<?php echo $porcent_retirada ?>%)</div>
						</div>
						<div class="progress ht-8 br-5 mt-2">
							<div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary" style="width: <?php echo $porcent_retirada ?>%">
							</div>
						</div>
					</div>
					<div class="mb-4">
						<div class="d-flex">
							<span class="tx-13 font-weight-semibold">Mesa</span>
							<div class="ms-auto"><span class="text-info mx-1"><i class="fa-solid fa-dollar-sign"></i></span><span
									class="number-font"><b> R$ <?php echo $valor_mesaF ?></b></span> (<?php echo $porcent_mesa ?>%)</div>
						</div>
						<div class="progress ht-8 br-5 mt-2">
							<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: <?php echo $porcent_mesa ?>%"></div>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>


</div>





<div class="row row-sm">




</div>



<script type="text/javascript">
	// GRAFICO DE BARRAS
	function statistics1() {
		var total_pagar = "<?= $total_meses_pagar_grafico ?>";
		var total_receber = "<?= $total_meses_receber_grafico ?>";

		var split_pagar = total_pagar.split("-");
		var split_receber = total_receber.split("-");


		setTimeout(() => {
			var options1 = {
				series: [{
					name: 'Contas à Receber',
					data: [split_receber[0], split_receber[1], split_receber[2], split_receber[3], split_receber[4], split_receber[5], split_receber[6], split_receber[7], split_receber[8], split_receber[9], split_receber[10], split_receber[11]],
				}, {
					name: 'Contas à Pagar',
					data: [split_pagar[0], split_pagar[1], split_pagar[2], split_pagar[3], split_pagar[4], split_pagar[5], split_pagar[6], split_pagar[7], split_pagar[8], split_pagar[9], split_pagar[10], split_pagar[11]],
				}],
				chart: {
					type: 'bar',
					height: 280
				},
				grid: {
					borderColor: '#f2f6f7',
				},
				colors: ["#098522", "#bf281d"],
				plotOptions: {
					bar: {
						colors: {
							ranges: [{
								from: -100,
								to: -46,
								color: '#098522'
							}, {
								from: -45,
								to: 0,
								color: '#bf281d'
							}]
						},
						columnWidth: '40%',
					}
				},
				dataLabels: {
					enabled: false,
				},
				stroke: {
					show: true,
					width: 4,
					colors: ['transparent']
				},
				legend: {
					show: true,
					position: 'top',
				},
				yaxis: {
					title: {
						text: 'Valores',
						style: {
							color: '#adb5be',
							fontSize: '14px',
							fontFamily: 'poppins, sans-serif',
							fontWeight: 600,
							cssClass: 'apexcharts-yaxis-label',
						},
					},
					labels: {
						formatter: function(y) {
							return y.toFixed(0) + "";
						}
					}
				},
				xaxis: {
					type: 'month',
					categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
					axisBorder: {
						show: true,
						color: 'rgba(119, 119, 142, 0.05)',
						offsetX: 0,
						offsetY: 0,
					},
					axisTicks: {
						show: true,
						borderType: 'solid',
						color: 'rgba(119, 119, 142, 0.05)',
						width: 6,
						offsetX: 0,
						offsetY: 0
					},
					labels: {
						rotate: -90
					}
				}
			};
			if (window.matchMedia('(max-width: 1023px)').matches) {
 options1.chart.height = 300;
 options1.chart.toolbar = {show:false};
 options1.xaxis.labels.rotate = -45;
 options1.xaxis.labels.style = {fontSize:'10px'};
 options1.yaxis.title.text = '';
 options1.legend.fontSize = '11px';
 options1.stroke.width = 1;
}
 document.getElementById('statistics1').innerHTML = '';
			var chart1 = new ApexCharts(document.querySelector("#statistics1"), options1);
			chart1.render();
		}, 300);
	}
</script>





</div><!-- bc-dashboard-mobile -->
<div class="modal fade" id="modalAlertaSaas" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-top: 4px solid #ffc107;">
      <div class="modal-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <span class="alert-badge">
            <i class="fas fa-exclamation-triangle"></i>
          </span>
          <h5 class="modal-title mb-0" id="alertaTitulo"></h5>
        </div>
      </div>
      <div class="modal-body">
        <div id="alertaTexto" style="font-weight: normal;"></div>
      </div>
      <div class="modal-footer justify-content-end">
        <button type="button" class="btn btn-primary" id="btnFecharAlerta">Fechar</button>
      </div>
    </div>
  </div>
</div>


<style>
  .alert-badge{
    display:inline-flex; align-items:center; justify-content:center;
    width:44px; height:44px; border-radius:50%;
    background: rgba(255,193,7,.18); /* amarelo suave */
    color:#b58100; font-size:1.25rem;
    box-shadow: 0 2px 6px rgba(0,0,0,.06) inset;
  }
</style>


<script>
$(function () {
  $.getJSON('alerta_atual.php', function (resp) {
    if (!resp || !resp.ok || !resp.alerta) return;

    const a = resp.alerta;                       // { id, titulo, texto, ... }
    const title = (a.titulo || 'Aviso');
    const text  = (a.texto  || '');

    // fingerprint do conteúdo (título + texto)
    const normTitle = title.trim().toLowerCase();
    const normText  = text.replace(/\s+/g, ' ').trim();
    const fp = fnv1a(normTitle + '\n' + normText);

    // chave por ID (se existir) ou por título normalizado
    const storageKey = 'alerta_saas_fp_' + (a.id ? ('id_' + a.id) : ('t_' + encodeURIComponent(normTitle)));
    const seen = localStorage.getItem(storageKey);

    if (seen === fp) return; // já visto com esse mesmo conteúdo

    // Preenche e mostra modal
    $('#alertaTitulo').text(title);
    $('#alertaTexto').html(text);

    const modalEl = document.getElementById('modalAlertaSaas');
    const modal   = new bootstrap.Modal(modalEl);

    const markSeen = () => localStorage.setItem(storageKey, fp);

    $('#btnFecharAlerta').off('click').on('click', function () {
      markSeen();
      modal.hide();
    });

    // se fechar por qualquer outro meio no futuro, ainda marca como visto
    modalEl.addEventListener('hidden.bs.modal', function () {
      markSeen();
    }, { once: true });

    modal.show();
  });

  // hash rápido FNV-1a (32-bit) para fingerprint
  function fnv1a(str) {
    let h = 0x811c9dc5;
    for (let i = 0; i < str.length; i++) {
      h ^= str.charCodeAt(i);
      h = (h + ((h<<1) + (h<<4) + (h<<7) + (h<<8) + (h<<24))) >>> 0;
    }
    return ('00000000' + h.toString(16)).slice(-8);
  }
});
</script>