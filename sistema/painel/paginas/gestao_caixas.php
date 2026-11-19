<?php
// Visão gerencial, somente leitura; operações permanecem no menu Caixas.
if (!in_array($_SESSION['nivel'] ?? '', ['Administrador','Gerente'], true)) {
    http_response_code(403);
    echo '<div class="alert alert-warning">Acesso restrito a administradores e gerentes.</div>';
    return;
}
$empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$empresa) { http_response_code(403); return; }
$inicio = (string)($_GET['inicio'] ?? date('Y-m-01'));
$fim = (string)($_GET['fim'] ?? date('Y-m-d'));
foreach ([$inicio,$fim] as $data) {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d',$data);
    if (!$d || $d->format('Y-m-d') !== $data) { $inicio=date('Y-m-01'); $fim=date('Y-m-d'); break; }
}
if ($inicio > $fim) { [$inicio,$fim]=[$fim,$inicio]; }
$consulta=$pdo->prepare("SELECT c.id,c.data_abertura,c.data_fechamento,c.valor_abertura,c.valor_fechamento,c.quebra,c.obs,u.nome AS operador_nome FROM caixas c LEFT JOIN usuarios u ON u.id=c.operador AND u.empresa=c.empresa WHERE c.empresa=:empresa AND c.data_abertura BETWEEN :inicio AND :fim ORDER BY c.id DESC LIMIT 300");
$consulta->execute([':empresa'=>$empresa,':inicio'=>$inicio,':fim'=>$fim]);
$caixas=$consulta->fetchAll(PDO::FETCH_ASSOC);
$totalAbertos=0;$totalFechados=0;$totalDivergencia=0.0;
foreach($caixas as $c){if(empty($c['data_fechamento']))$totalAbertos++;else{$totalFechados++;$totalDivergencia+=(float)$c['quebra'];}}
$h=static function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
$moeda=static function($v){return 'R$ '.number_format((float)$v,2,',','.');};
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div><h3 class="mb-1">Gestão de Caixas</h3><small class="text-muted">Acompanhamento gerencial — sem alterar os caixas operacionais.</small></div>
  <a href="caixas" class="btn btn-primary"><i class="fa fa-briefcase me-1"></i> Abrir menu Caixas</a>
</div>
<form method="get" action="index.php" class="card card-body mb-3">
<input type="hidden" name="pagina" value="gestao_caixas">
<div class="row g-2 align-items-end">
 <div class="col-md-4"><label for="gc-inicio" class="form-label">De</label><input id="gc-inicio" class="form-control" type="date" name="inicio" value="<?= $h($inicio) ?>"></div>
 <div class="col-md-4"><label for="gc-fim" class="form-label">Até</label><input id="gc-fim" class="form-control" type="date" name="fim" value="<?= $h($fim) ?>"></div>
 <div class="col-md-4"><button class="btn btn-primary w-100" type="submit">Consultar</button></div>
</div></form>
<div class="row g-3 mb-3">
 <div class="col-md-4"><div class="card card-body"><small>Caixas abertos</small><h3><?= $totalAbertos ?></h3></div></div>
 <div class="col-md-4"><div class="card card-body"><small>Caixas fechados</small><h3><?= $totalFechados ?></h3></div></div>
 <div class="col-md-4"><div class="card card-body"><small>Diferença líquida dos fechamentos</small><h3><?= $moeda($totalDivergencia) ?></h3></div></div>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-striped table-hover"><thead><tr><th>Caixa</th><th>Operador</th><th>Abertura</th><th>Fechamento</th><th>Saldo inicial</th><th>Valor informado</th><th>Diferença</th><th>Status</th></tr></thead><tbody>
<?php foreach($caixas as $c): ?>
<tr>
<td>#<?= (int)$c['id'] ?></td>
<td><?= $h($c['operador_nome'] ?: 'Não identificado') ?></td>
<td><?= $h($c['data_abertura']) ?></td>
<td><?= $h($c['data_fechamento'] ?: '—') ?></td>
<td><?= $moeda($c['valor_abertura']) ?></td>
<td><?= $c['data_fechamento'] ? $moeda($c['valor_fechamento']) : '—' ?></td>
<td><?= $c['data_fechamento'] ? $moeda($c['quebra']) : '—' ?></td>
<td><?= $c['data_fechamento'] ? '<span class="badge bg-secondary">Fechado</span>' : '<span class="badge bg-success">Aberto</span>' ?></td>
</tr>
<?php endforeach; ?>
<?php if(!$caixas): ?><tr><td colspan="8" class="text-center text-muted">Nenhum caixa encontrado no período.</td></tr><?php endif; ?>
</tbody></table><small class="text-muted">Exibindo até 300 caixas no período. Diferença positiva indica sobra; negativa indica falta.</small>
</div></div>
