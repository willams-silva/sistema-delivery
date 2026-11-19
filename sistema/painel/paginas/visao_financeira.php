<?php
// Painel somente leitura: fonte única são as contas efetivamente baixadas.
$pag = 'visao_financeira';
if (($_SESSION['nivel'] ?? '') !== 'Administrador' && ($_SESSION['nivel'] ?? '') !== 'Gerente') {
    http_response_code(403);
    echo '<div class="alert alert-danger">Acesso restrito à administração financeira.</div>';
    return;
}
$empresaFinanceira = (int) ($id_empresa ?? 0);
if ($empresaFinanceira <= 0) { http_response_code(403); return; }
$inicioFinanceiro = $_GET['inicio'] ?? date('Y-m-01');
$fimFinanceiro = $_GET['fim'] ?? date('Y-m-t');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicioFinanceiro) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fimFinanceiro)
    || !checkdate((int)substr($inicioFinanceiro, 5, 2), (int)substr($inicioFinanceiro, 8, 2), (int)substr($inicioFinanceiro, 0, 4))
    || !checkdate((int)substr($fimFinanceiro, 5, 2), (int)substr($fimFinanceiro, 8, 2), (int)substr($fimFinanceiro, 0, 4))
    || $inicioFinanceiro > $fimFinanceiro) {
    $inicioFinanceiro = date('Y-m-01');
    $fimFinanceiro = date('Y-m-t');
}
$valorFinanceiro = static function ($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); };
$consultaFinanceira = static function ($pdo, $sql, $params) {
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return (float)$st->fetchColumn();
};
$parametros = [':empresa' => $empresaFinanceira, ':inicio' => $inicioFinanceiro, ':fim' => $fimFinanceiro];
$entradas = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM receber WHERE empresa=:empresa AND pago='Sim' AND data_pgto BETWEEN :inicio AND :fim", $parametros);
$saidas = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM pagar WHERE empresa=:empresa AND pago='Sim' AND data_pgto BETWEEN :inicio AND :fim", $parametros);
$saldoAnterior = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM receber WHERE empresa=:empresa AND pago='Sim' AND data_pgto < :inicio", [':empresa'=>$empresaFinanceira, ':inicio'=>$inicioFinanceiro])
    - $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM pagar WHERE empresa=:empresa AND pago='Sim' AND data_pgto < :inicio", [':empresa'=>$empresaFinanceira, ':inicio'=>$inicioFinanceiro]);
$pendenteReceber = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM receber WHERE empresa=:empresa AND pago <> 'Sim' AND vencimento BETWEEN :inicio AND :fim", $parametros);
$pendentePagar = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM pagar WHERE empresa=:empresa AND pago <> 'Sim' AND vencimento BETWEEN :inicio AND :fim", $parametros);
// Alertas de inadimplência: valores vencidos e ainda não baixados, restritos à empresa.
$hojeFinanceiro = date('Y-m-d');
$vencidoReceber = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM receber WHERE empresa=:empresa AND pago='Não' AND vencimento < :hoje", [':empresa'=>$empresaFinanceira, ':hoje'=>$hojeFinanceiro]);
$vencidoPagar = $consultaFinanceira($pdo, "SELECT COALESCE(SUM(valor),0) FROM pagar WHERE empresa=:empresa AND pago='Não' AND vencimento < :hoje", [':empresa'=>$empresaFinanceira, ':hoje'=>$hojeFinanceiro]);
$quantidadeVencidaReceber = (int)$consultaFinanceira($pdo, "SELECT COUNT(*) FROM receber WHERE empresa=:empresa AND pago='Não' AND vencimento < :hoje", [':empresa'=>$empresaFinanceira, ':hoje'=>$hojeFinanceiro]);
$quantidadeVencidaPagar = (int)$consultaFinanceira($pdo, "SELECT COUNT(*) FROM pagar WHERE empresa=:empresa AND pago='Não' AND vencimento < :hoje", [':empresa'=>$empresaFinanceira, ':hoje'=>$hojeFinanceiro]);

$st = $pdo->prepare("SELECT dia, SUM(entrada) entrada, SUM(saida) saida FROM (
 SELECT data_pgto dia, valor entrada, 0 saida FROM receber WHERE empresa=:empresa1 AND pago='Sim' AND data_pgto BETWEEN :inicio1 AND :fim1
 UNION ALL
 SELECT data_pgto dia, 0 entrada, valor saida FROM pagar WHERE empresa=:empresa2 AND pago='Sim' AND data_pgto BETWEEN :inicio2 AND :fim2
) t GROUP BY dia ORDER BY dia");
$st->execute([':empresa1'=>$empresaFinanceira, ':inicio1'=>$inicioFinanceiro, ':fim1'=>$fimFinanceiro, ':empresa2'=>$empresaFinanceira, ':inicio2'=>$inicioFinanceiro, ':fim2'=>$fimFinanceiro]);
$movimentos = $st->fetchAll(PDO::FETCH_ASSOC);

// Previsões por dia: somente títulos ainda não baixados; não altera lançamentos.
$previsaoSt = $pdo->prepare("SELECT dia, SUM(entrada) entrada, SUM(saida) saida FROM (
 SELECT vencimento dia, valor entrada, 0 saida FROM receber WHERE empresa=:empresa1 AND pago <> 'Sim' AND vencimento BETWEEN :inicio1 AND :fim1
 UNION ALL
 SELECT vencimento dia, 0 entrada, valor saida FROM pagar WHERE empresa=:empresa2 AND pago <> 'Sim' AND vencimento BETWEEN :inicio2 AND :fim2
) t GROUP BY dia ORDER BY dia");
$previsaoSt->execute([':empresa1'=>$empresaFinanceira, ':inicio1'=>$inicioFinanceiro, ':fim1'=>$fimFinanceiro, ':empresa2'=>$empresaFinanceira, ':inicio2'=>$inicioFinanceiro, ':fim2'=>$fimFinanceiro]);
$previsoes = $previsaoSt->fetchAll(PDO::FETCH_ASSOC);
$diarioFinanceiro = [];
foreach ($movimentos as $m) {
    $diarioFinanceiro[$m['dia']] = ['entrada'=>(float)$m['entrada'], 'saida'=>(float)$m['saida'], 'prev_entrada'=>0, 'prev_saida'=>0];
}
foreach ($previsoes as $m) {
    if (!isset($diarioFinanceiro[$m['dia']])) $diarioFinanceiro[$m['dia']] = ['entrada'=>0, 'saida'=>0, 'prev_entrada'=>0, 'prev_saida'=>0];
    $diarioFinanceiro[$m['dia']]['prev_entrada'] = (float)$m['entrada'];
    $diarioFinanceiro[$m['dia']]['prev_saida'] = (float)$m['saida'];
}
ksort($diarioFinanceiro);


// Conferência por forma de pagamento: não soma vendas novamente às contas recebidas.
$formasSt = $pdo->prepare("SELECT COALESCE(fp.nome, 'Não identificada') AS forma,
 COUNT(r.id) AS quantidade, COALESCE(SUM(r.valor),0) AS total
 FROM receber r
 LEFT JOIN formas_pgto fp ON fp.id = r.forma_pgto AND fp.empresa = r.empresa
 WHERE r.empresa = :empresa AND r.pago = 'Sim' AND r.data_pgto BETWEEN :inicio AND :fim
 GROUP BY fp.id, fp.nome ORDER BY total DESC");
$formasSt->execute($parametros);
$formasRecebidas = $formasSt->fetchAll(PDO::FETCH_ASSOC);
$totalConferido = array_sum(array_map(static function ($item) { return (float)$item['total']; }, $formasRecebidas));
$diferencaConferencia = round($entradas - $totalConferido, 2);


// Rastreabilidade consultiva: autores e baixadores já registrados nas contas.
$historicoSt = $pdo->prepare("SELECT mov.tipo, mov.id, mov.descricao, mov.valor, mov.data_pgto, mov.usuario_lanc, mov.usuario_pgto,
 ul.nome AS lancado_por, up.nome AS baixado_por FROM (
 SELECT 'Recebimento' tipo, id, descricao, valor, data_pgto, usuario_lanc, usuario_pgto, empresa FROM receber
 WHERE empresa=:empresa1 AND pago='Sim' AND data_pgto BETWEEN :inicio1 AND :fim1
 UNION ALL
 SELECT 'Pagamento' tipo, id, descricao, valor, data_pgto, usuario_lanc, usuario_pgto, empresa FROM pagar
 WHERE empresa=:empresa2 AND pago='Sim' AND data_pgto BETWEEN :inicio2 AND :fim2
) mov
LEFT JOIN usuarios ul ON ul.id=mov.usuario_lanc AND ul.empresa=mov.empresa
LEFT JOIN usuarios up ON up.id=mov.usuario_pgto AND up.empresa=mov.empresa
ORDER BY mov.data_pgto DESC, mov.id DESC LIMIT 100");
$historicoSt->execute([':empresa1'=>$empresaFinanceira, ':inicio1'=>$inicioFinanceiro, ':fim1'=>$fimFinanceiro, ':empresa2'=>$empresaFinanceira, ':inicio2'=>$inicioFinanceiro, ':fim2'=>$fimFinanceiro]);
$historicoFinanceiro = $historicoSt->fetchAll(PDO::FETCH_ASSOC);

$saldoPeriodo = $entradas - $saidas;
?>
<style>
.bc-financeiro .bc-card{border:1px solid #e2e8f0;border-radius:14px;background:#fff;padding:18px;height:100%;box-shadow:0 3px 12px rgba(0,0,0,.04)}
.bc-financeiro .bc-card small{color:#64748b;display:block;margin-bottom:7px}
.bc-financeiro .bc-card strong{font-size:clamp(19px,2.5vw,27px);overflow-wrap:anywhere}
.bc-financeiro .bc-note{font-size:12px;color:#64748b}
.bc-financeiro table{width:100%;border-collapse:collapse}
.bc-financeiro th,.bc-financeiro td{padding:11px;border-bottom:1px solid #e2e8f0;text-align:right}
.bc-financeiro th:first-child,.bc-financeiro td:first-child{text-align:left}
.bc-financeiro .bc-chart{display:flex;align-items:end;gap:5px;height:150px;border-bottom:1px solid #cbd5e1;padding-top:12px}
.bc-financeiro .bc-chart div{flex:1;min-width:2px}
.bc-financeiro .bc-chart .bc-in{background:#16a34a}.bc-financeiro .bc-chart .bc-out{background:#dc2626}
</style>
<div class="container-fluid bc-financeiro">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
 <div><h3 class="mb-1">Visão Financeira</h3><p class="bc-note">Movimentações realizadas e contas previstas · Somente leitura</p></div>
 <form method="get" action="index.php" class="d-flex flex-wrap gap-2 align-items-end">
 <input type="hidden" name="pagina" value="visao_financeira">
 <label>De <input class="form-control" type="date" name="inicio" value="<?php echo htmlspecialchars($inicioFinanceiro, ENT_QUOTES); ?>" required></label>
 <label>Até <input class="form-control" type="date" name="fim" value="<?php echo htmlspecialchars($fimFinanceiro, ENT_QUOTES); ?>" required></label>
 <button class="btn btn-primary" type="submit">Filtrar</button>
 </form>
</div>
<div class="row g-3">
<?php foreach ([
 ['Saldo anterior (contas)', $saldoAnterior],
 ['Entradas recebidas', $entradas],
 ['Saídas pagas', $saidas],
 ['Saldo do período', $saldoPeriodo],
 ['Saldo acumulado (contas)', $saldoAnterior+$saldoPeriodo],
 ['A receber no período', $pendenteReceber],
 ['A pagar no período', $pendentePagar]
] as $card): ?>
<div class="col-12 col-sm-6 col-xl-3"><div class="bc-card"><small><?php echo htmlspecialchars($card[0], ENT_QUOTES, 'UTF-8'); ?></small><strong><?php echo $valorFinanceiro($card[1]); ?></strong></div></div>
<?php endforeach; ?>
</div>
<div class="bc-card mt-3">
<h5>Alertas financeiros</h5>
<p class="bc-note">Pendências vencidas até ontem, independentemente do período selecionado. Não incluem contas já baixadas.</p>
<div class="row g-3">
<div class="col-12 col-md-6"><div class="alert <?php echo $quantidadeVencidaReceber ? 'alert-warning' : 'alert-light'; ?> mb-0">
<strong>Recebimentos em atraso</strong><p class="mb-1"><?php echo $valorFinanceiro($vencidoReceber); ?></p>
<small><?php echo $quantidadeVencidaReceber; ?> título(s) vencido(s)</small></div></div>
<div class="col-12 col-md-6"><div class="alert <?php echo $quantidadeVencidaPagar ? 'alert-danger' : 'alert-light'; ?> mb-0">
<strong>Pagamentos em atraso</strong><p class="mb-1"><?php echo $valorFinanceiro($vencidoPagar); ?></p>
<small><?php echo $quantidadeVencidaPagar; ?> título(s) vencido(s)</small></div></div>
</div></div>
<div class="row g-3 mt-1">
<div class="col-12 col-lg-7"><div class="bc-card"><h5>Entradas e saídas por dia</h5><p class="bc-note">Verde: entradas · Vermelho: saídas</p>
<?php $maxGrafico = 1; foreach ($movimentos as $m) { $maxGrafico = max($maxGrafico, (float)$m['entrada'], (float)$m['saida']); } ?>
<div class="bc-chart" role="img" aria-label="Comparativo diário de entradas e saídas">
<?php foreach ($movimentos as $m): ?>
<div title="<?php echo htmlspecialchars($m['dia'].' · Entradas '.$valorFinanceiro($m['entrada']).' · Saídas '.$valorFinanceiro($m['saida']), ENT_QUOTES, 'UTF-8'); ?>">
<div class="bc-in" style="height:<?php echo max(1,round((float)$m['entrada']/$maxGrafico*125)); ?>px"></div>
<div class="bc-out" style="height:<?php echo max(1,round((float)$m['saida']/$maxGrafico*125)); ?>px"></div>
</div>
<?php endforeach; ?>
</div>
<?php if (!$movimentos): ?><p class="bc-note mt-2">Nenhuma movimentação realizada neste período.</p><?php endif; ?>
</div></div>
<div class="col-12 col-lg-5"><div class="bc-card"><h5>Previsão por vencimento</h5>
<p>Contas a receber: <strong><?php echo $valorFinanceiro($pendenteReceber); ?></strong></p>
<p>Contas a pagar: <strong><?php echo $valorFinanceiro($pendentePagar); ?></strong></p>
<p>Saldo previsto do período: <strong><?php echo $valorFinanceiro($pendenteReceber-$pendentePagar); ?></strong></p>
<p class="bc-note">Valores previstos não são somados ao saldo realizado.</p>
</div></div>
</div>
<div class="bc-card mt-3">
<h5>Movimentações e previsão por dia</h5>
<p class="bc-note">O saldo realizado acumula apenas contas baixadas. A projeção acrescenta contas pendentes por vencimento; não é saldo bancário.</p>
<div class="table-responsive"><table><thead><tr><th>Data</th><th>Entradas realizadas</th><th>Saídas realizadas</th><th>Saldo realizado acumulado</th><th>A receber previsto</th><th>A pagar previsto</th><th>Saldo projetado</th></tr></thead><tbody>
<?php
$saldoDiario = $saldoAnterior;
$saldoProjetado = $saldoAnterior;
foreach ($diarioFinanceiro as $dia => $m):
    $saldoDiario += $m['entrada'] - $m['saida'];
    $saldoProjetado += $m['entrada'] - $m['saida'] + $m['prev_entrada'] - $m['prev_saida'];
?>
<tr>
<td><?php echo htmlspecialchars(date('d/m/Y', strtotime($dia)), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo $valorFinanceiro($m['entrada']); ?></td>
<td><?php echo $valorFinanceiro($m['saida']); ?></td>
<td><?php echo $valorFinanceiro($saldoDiario); ?></td>
<td><?php echo $valorFinanceiro($m['prev_entrada']); ?></td>
<td><?php echo $valorFinanceiro($m['prev_saida']); ?></td>
<td><?php echo $valorFinanceiro($saldoProjetado); ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$diarioFinanceiro): ?><tr><td colspan="7" style="text-align:center">Sem lançamentos no período selecionado.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="bc-card mt-3">
<h5>Conferência de recebimentos por forma de pagamento</h5>
<p class="bc-note">Agrupamento dos registros baixados em Contas a Receber. Não duplica vendas do PDV e não comprova depósito bancário.</p>
<div class="table-responsive"><table><thead><tr><th>Forma de pagamento</th><th>Quantidade de lançamentos</th><th>Total registrado</th></tr></thead><tbody>
<?php foreach ($formasRecebidas as $forma): ?>
<tr><td><?php echo htmlspecialchars((string)$forma['forma'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo (int)$forma['quantidade']; ?></td><td><?php echo $valorFinanceiro($forma['total']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$formasRecebidas): ?><tr><td colspan="3" style="text-align:center">Sem recebimentos baixados no período.</td></tr><?php endif; ?>
</tbody><tfoot><tr><th>Total conferido</th><th></th><th><?php echo $valorFinanceiro($totalConferido); ?></th></tr>
<tr><th>Diferença frente às entradas realizadas</th><th></th><th><?php echo $valorFinanceiro($diferencaConferencia); ?></th></tr></tfoot></table></div>
<p class="bc-note mt-2">A conciliação bancária completa ainda depende de taxas de cartão, antecipações e confirmação das liquidações.</p>
</div>
<div class="bc-card mt-3">
<h5>Rastreabilidade dos lançamentos baixados</h5>
<p class="bc-note">Últimos 100 registros do período, com responsáveis informados pelo sistema. Esta consulta não é um log de alterações.</p>
<div class="table-responsive"><table><thead><tr><th>Data da baixa</th><th>Tipo</th><th>Descrição</th><th>Valor</th><th>Lançado por</th><th>Baixado por</th></tr></thead><tbody>
<?php foreach ($historicoFinanceiro as $registro): ?>
<tr>
<td><?php echo htmlspecialchars(date('d/m/Y', strtotime($registro['data_pgto'])), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($registro['tipo'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars((string)$registro['descricao'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo $valorFinanceiro($registro['valor']); ?></td>
<td><?php echo htmlspecialchars((string)($registro['lancado_por'] ?: 'Não identificado'), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars((string)($registro['baixado_por'] ?: 'Não identificado'), ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$historicoFinanceiro): ?><tr><td colspan="6" style="text-align:center">Nenhum lançamento baixado no período.</td></tr><?php endif; ?>
</tbody></table></div></div>
<p class="bc-note mt-3">Importante: saldo acumulado calculado apenas com contas baixadas em receber/pagar. Não representa necessariamente o saldo bancário ou o dinheiro físico no caixa. Conciliação por forma de pagamento e auditoria de alterações exigem integração adicional.</p>
</div>