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
$totalAbertos=0;$totalFechados=0;$totalDivergencia=0.0;$totalSobras=0.0;$totalFaltas=0.0;$caixasDivergentes=0;
foreach($caixas as $c){if(empty($c['data_fechamento']))$totalAbertos++;else{$totalFechados++;$diferenca=round((float)$c['quebra'],2);$totalDivergencia+=$diferenca;if($diferenca>0){$totalSobras+=$diferenca;$caixasDivergentes++;}elseif($diferenca<0){$totalFaltas+=abs($diferenca);$caixasDivergentes++;}}}
// Consolidação por forma de pagamento dos caixas exibidos, sem misturar empresas.
$detalhes = [];
if ($caixas) {
    $ids = array_map(static function($c){return (int)$c['id'];},$caixas);
    $marcadores = implode(',',array_fill(0,count($ids),'?'));
    foreach (['receber'=>'entradas','pagar'=>'saidas'] as $tabela=>$tipo) {
        $sql = "SELECT m.caixa, COALESCE(f.nome,'Não identificada') AS forma, SUM(COALESCE(m.subtotal,m.valor)) AS total, COUNT(*) AS quantidade FROM $tabela m INNER JOIN caixas c ON c.id=m.caixa AND c.empresa=m.empresa LEFT JOIN formas_pgto f ON f.id=m.forma_pgto AND f.empresa=m.empresa WHERE m.empresa=? AND m.pago='Sim' AND m.caixa IN ($marcadores) GROUP BY m.caixa,m.forma_pgto,f.nome";
        $st=$pdo->prepare($sql);
        $st->execute(array_merge([(int)$empresa],$ids));
        foreach($st->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $chave=(int)$linha['caixa'];
            $detalhes[$chave][$tipo][]=$linha;
        }
    }
    $sql="SELECT s.caixa, COALESCE(SUM(s.valor),0) AS total, COUNT(*) AS quantidade FROM sangrias s INNER JOIN caixas c ON c.id=s.caixa WHERE c.empresa=? AND s.caixa IN ($marcadores) GROUP BY s.caixa";
    $st=$pdo->prepare($sql);
    $st->execute(array_merge([(int)$empresa],$ids));
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $linha) {
        $detalhes[(int)$linha['caixa']]['sangrias']=$linha;
    }
}
// Último evento de fechamento por caixa, com autor e data registrados.
$auditorias = [];
if ($caixas) {
    $ids = array_map(static function($c){return (int)$c['id'];},$caixas);
    $marcadores = implode(',',array_fill(0,count($ids),'?'));
    $sql = "SELECT a.caixa,a.usuario,a.criado_em,a.saldo_esperado,a.valor_informado,a.diferenca,u.nome AS responsavel FROM auditoria_caixas a LEFT JOIN usuarios u ON u.id=a.usuario AND u.empresa=a.empresa INNER JOIN (SELECT caixa,MAX(id) AS ultimo FROM auditoria_caixas WHERE empresa=? AND evento='FECHAMENTO' AND caixa IN ($marcadores) GROUP BY caixa) ult ON ult.ultimo=a.id WHERE a.empresa=?";
    $st=$pdo->prepare($sql);
    $st->execute(array_merge([(int)$empresa],$ids,[(int)$empresa]));
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $evento) {
        $auditorias[(int)$evento['caixa']]=$evento;
    }
}
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
<div class="row g-3 mb-3">
 <div class="col-md-4"><div class="card card-body"><small>Caixas com divergência</small><h3 class="text-warning"><?= $caixasDivergentes ?></h3></div></div>
 <div class="col-md-4"><div class="card card-body"><small>Total de sobras</small><h3 class="text-success"><?= $moeda($totalSobras) ?></h3></div></div>
 <div class="col-md-4"><div class="card card-body"><small>Total de faltas</small><h3 class="text-danger"><?= $moeda($totalFaltas) ?></h3></div></div>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-striped table-hover"><thead><tr><th>Caixa</th><th>Operador</th><th>Abertura</th><th>Fechamento</th><th>Saldo inicial</th><th>Valor informado</th><th>Diferença</th><th>Status</th><th>Responsável pelo fechamento</th><th>Detalhes</th></tr></thead><tbody>
<?php foreach($caixas as $c): ?>
<?php
  $diferencaCaixa = round((float)$c['quebra'],2);
  $fechado = !empty($c['data_fechamento']);
  $situacao = !$fechado ? 'Em aberto' : ($diferencaCaixa > 0 ? 'Sobra' : ($diferencaCaixa < 0 ? 'Falta' : 'Conferido'));
  $classeSituacao = !$fechado ? 'bg-primary' : ($diferencaCaixa > 0 ? 'bg-success' : ($diferencaCaixa < 0 ? 'bg-danger' : 'bg-secondary'));
?>
<tr>
<td>#<?= (int)$c['id'] ?></td>
<td><?= $h($c['operador_nome'] ?: 'Não identificado') ?></td>
<td><?= $h($c['data_abertura']) ?></td>
<td><?= $h($c['data_fechamento'] ?: '—') ?></td>
<td><?= $moeda($c['valor_abertura']) ?></td>
<td><?= $c['data_fechamento'] ? $moeda($c['valor_fechamento']) : '—' ?></td>
<td class="<?= $fechado && $diferencaCaixa < 0 ? 'text-danger fw-bold' : ($fechado && $diferencaCaixa > 0 ? 'text-success fw-bold' : '') ?>"><?= $fechado ? $moeda($diferencaCaixa) : '—' ?></td>
<td><span class="badge <?= $classeSituacao ?>"><?= $situacao ?></span></td>
<td><?php $aud=$auditorias[(int)$c['id']] ?? null; ?>
<?php if($aud): ?>
<strong><?= $h($aud['responsavel'] ?: 'Usuário #'.$aud['usuario']) ?></strong>
<div class="text-muted small"><?= $h($aud['criado_em']) ?></div>
<?php else: ?><span class="text-muted">Sem registro de auditoria</span><?php endif; ?>
</td>
<td><button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#gc-detalhe-<?= (int)$c['id'] ?>" aria-expanded="false" aria-controls="gc-detalhe-<?= (int)$c['id'] ?>">Detalhar</button></td>
</tr>
<tr class="collapse" id="gc-detalhe-<?= (int)$c['id'] ?>"><td colspan="10">
 <div class="row g-3">
 <?php foreach (['entradas'=>'Recebimentos','saidas'=>'Pagamentos'] as $tipo=>$titulo): ?>
 <div class="col-md-5"><strong><?= $h($titulo) ?> por forma</strong>
 <ul class="list-group mt-2">
 <?php foreach(($detalhes[(int)$c['id']][$tipo] ?? []) as $mov): ?>
 <li class="list-group-item d-flex justify-content-between"><span><?= $h($mov['forma']) ?> (<?= (int)$mov['quantidade'] ?>)</span><strong><?= $moeda($mov['total']) ?></strong></li>
 <?php endforeach; ?>
 <?php if(empty($detalhes[(int)$c['id']][$tipo])): ?><li class="list-group-item text-muted">Sem movimentações pagas.</li><?php endif; ?>
 </ul></div>
 <?php endforeach; ?>
 <div class="col-md-2"><strong>Sangrias</strong><p class="mt-2 mb-0"><?= $moeda($detalhes[(int)$c['id']]['sangrias']['total'] ?? 0) ?></p><small><?= (int)($detalhes[(int)$c['id']]['sangrias']['quantidade'] ?? 0) ?> retirada(s)</small></div>
 </div>
</td></tr>
<?php endforeach; ?>
<?php if(!$caixas): ?><tr><td colspan="9" class="text-center text-muted">Nenhum caixa encontrado no período.</td></tr><?php endif; ?>
</tbody></table><small class="text-muted">Exibindo até 300 caixas no período. Diferença positiva indica sobra; negativa indica falta.</small>
</div></div>
