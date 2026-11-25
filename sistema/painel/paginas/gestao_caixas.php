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
    $sql = "SELECT a.caixa,a.usuario,a.criado_em,a.saldo_esperado,a.valor_informado,a.diferenca,a.detalhes,u.nome AS responsavel FROM auditoria_caixas a LEFT JOIN usuarios u ON u.id=a.usuario AND u.empresa=a.empresa INNER JOIN (SELECT caixa,MAX(id) AS ultimo FROM auditoria_caixas WHERE empresa=? AND evento='FECHAMENTO' AND caixa IN ($marcadores) GROUP BY caixa) ult ON ult.ultimo=a.id WHERE a.empresa=?";
    $st=$pdo->prepare($sql);
    $st->execute(array_merge([(int)$empresa],$ids,[(int)$empresa]));
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $evento) {
        $auditorias[(int)$evento['caixa']]=$evento;
    }
}
// Diagnóstico não destrutivo: recebimentos/pagamentos quitados sem caixa associado.
$pendenciasVinculo = ['receber'=>0,'pagar'=>0];
foreach (['receber','pagar'] as $origem) {
    $st=$pdo->prepare("SELECT COUNT(*) FROM $origem m LEFT JOIN caixas c ON c.id=m.caixa AND c.empresa=m.empresa WHERE m.empresa=:empresa AND m.pago='Sim' AND (m.caixa IS NULL OR m.caixa=0 OR c.id IS NULL)");
    $st->execute([':empresa'=>$empresa]);
    $pendenciasVinculo[$origem]=(int)$st->fetchColumn();
}
$h=static function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
$moeda=static function($v){return 'R$ '.number_format((float)$v,2,',','.');};
?>
<style>
html body .bc-gc .card{background:#fff!important;border:1px solid #e5d4c6!important;border-radius:14px!important;box-shadow:0 3px 12px rgba(78,45,32,.06)!important;color:#4e2d20}
html body .bc-gc .card small,html body .bc-gc .text-muted{color:#795038!important}
html body .bc-gc .card h3{font-weight:700;color:#4e2d20}
html body .bc-gc .form-control{background:#fff!important;border:1px solid #d7c5b7!important;border-radius:9px!important;color:#4e2d20!important}
html body .bc-gc .form-label{color:#795038!important}
html body .bc-gc .btn-primary{background:#914a29!important;border-color:#914a29!important;color:#fff!important;border-radius:9px!important}
html body .bc-gc .btn-outline-primary{background:#fff!important;border-color:#d7c5b7!important;color:#63351e!important;border-radius:8px!important}
html body .bc-gc .btn-outline-primary:hover{background:#914a29!important;color:#fff!important}
html body .bc-gc .alert-warning{background:#fff4dc!important;color:#855900!important;border-color:#ecd5a0!important}
html body .bc-gc .alert-warning *{color:#855900!important}
html body .bc-gc table thead th{background:#f4e9df!important;color:#63351e!important}
html body .bc-gc table :is(th,td),html body .bc-gc .list-group-item,html body .bc-gc .border{border-color:#eaded4!important}
html body .bc-gc .list-group-item{background:#fff!important;color:#4e2d20!important}
html body .bc-gc table tbody tr:hover{background:#fcf8f4!important}
html body .bc-gc .badge.bg-primary{background:#914a29!important;color:#fff!important}
html body .bc-gc .text-success{color:#13745a!important}
html body .bc-gc .text-danger{color:#b42332!important}
html body .bc-gc .text-warning{color:#855900!important}
 
html body .bc-gc .gc-detail-grid{padding:18px;background:#fcf8f4;border-radius:12px;align-items:stretch}
html body .bc-gc .gc-detail-panel{background:#fff;border:1px solid #e5d4c6;border-radius:12px;padding:16px;height:100%;font-weight:400}
html body .bc-gc .gc-detail-panel>strong{display:block;font-size:16px;margin-bottom:12px;color:#63351e}
html body .bc-gc .gc-detail-grid .list-group-item{padding:12px;font-weight:400}
html body .bc-gc .gc-detail-grid .list-group-item strong{font-weight:600}
html body .bc-gc .gc-detail-grid .gc-amount{font-size:24px;font-weight:700;color:#63351e}
html body .bc-gc .gc-detail-grid .gc-detail-panel .row>div{padding:12px;font-weight:400}
html body .bc-gc table tbody tr:hover,html body .bc-gc table tbody tr:hover>td{--bs-table-accent-bg:transparent!important;--bs-table-bg-state:transparent!important;box-shadow:none!important;background-color:transparent!important}
html body .bc-gc .btn-outline-primary:hover{background:#fff!important;color:#63351e!important;border-color:#d7c5b7!important;box-shadow:none!important;transform:none!important}
html body .bc-gc .btn-outline-primary[aria-expanded="true"]{background:#f4e9df!important;color:#63351e!important}
</style>
<style id="bc-mobile-stage4">
@media(max-width:1023px){
html body .bc-gc{min-width:0;max-width:100%}
html body .bc-gc :is(.form-control,.btn){min-height:44px;max-width:100%}
html body .bc-gc .card-body{padding:12px!important;min-width:0}
html body .bc-gc .table-responsive{max-width:100%;overflow-x:auto}
html body .bc-gc .gc-detail-panel{min-width:0;overflow-wrap:break-word}
html body .bc-gc .gc-detail-panel .list-group-item{flex-wrap:wrap;gap:6px}
}
@media(max-width:767px){
html body .bc-gc>.row.g-3>div{width:50%;min-width:0}
html body .bc-gc>.row.g-3>div:last-child{width:100%}
html body .bc-gc>.row.g-3 h3{font-size:18px!important;overflow-wrap:break-word}
html body .bc-gc .gc-detail-grid>div{width:100%}
}
</style>
<div class="bc-gc">
<div class="card mb-3" style="margin-top:16px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-cash-register me-2"></i>Gestão de Caixas</h2><span style="font-size:12px;color:#795038">Acompanhamento gerencial — sem alterar os caixas operacionais.</span></div></div>
<div class="d-flex justify-content-start mb-3">
  <a href="caixas" class="btn btn-primary"><i class="fa fa-briefcase me-1"></i> Abrir menu Caixas</a>
</div>
<form method="get" action="index.php" class="card card-body mb-3">
<input type="hidden" name="pagina" value="gestao_caixas">
<div class="row g-2 align-items-end">
 <div class="col-md-4"><label for="gc-inicio" class="form-label">De</label><input id="gc-inicio" class="form-control" type="date" name="inicio" value="<?= $h($inicio) ?>"></div>
 <div class="col-md-4"><label for="gc-fim" class="form-label">Até</label><input id="gc-fim" class="form-control" type="date" name="fim" value="<?= $h($fim) ?>"></div>
 <div class="col-md-4"><button class="btn btn-primary w-100" type="submit">Consultar</button></div>
</div></form>
<?php if (array_sum($pendenciasVinculo)>0): ?>
<div class="alert alert-warning" role="alert">
<strong><i class="fa fa-exclamation-triangle me-1"></i> Atenção à integração financeira</strong>
<div>Existem <?= $pendenciasVinculo['receber'] ?> recebimento(s) e <?= $pendenciasVinculo['pagar'] ?> pagamento(s) quitado(s) sem vínculo válido com um caixa. Esses registros não entram no cálculo de nenhum fechamento.</div>
<small>Diagnóstico de toda a empresa, não limitado ao período filtrado. Nenhum lançamento foi alterado.</small>
</div>
<?php endif; ?>
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
<table class="table table-striped"><thead><tr><th>Caixa</th><th>Operador</th><th>Abertura</th><th>Fechamento</th><th>Saldo inicial</th><th>Valor informado</th><th>Diferença</th><th>Status</th><th>Responsável pelo fechamento</th><th>Detalhes</th></tr></thead><tbody>
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
<?php else: ?><span class="text-muted"><?= $fechado ? 'Fechamento anterior à auditoria' : 'Caixa ainda aberto' ?></span><?php endif; ?>
</td>
<td><button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#gc-detalhe-<?= (int)$c['id'] ?>" aria-expanded="false" aria-controls="gc-detalhe-<?= (int)$c['id'] ?>"><i class="fa fa-list-ul me-1"></i> Detalhar</button></td>
</tr>
<tr class="collapse" id="gc-detalhe-<?= (int)$c['id'] ?>"><td colspan="10">
 <div class="row g-3 gc-detail-grid">
 <?php foreach (['entradas'=>'Recebimentos','saidas'=>'Pagamentos'] as $tipo=>$titulo): ?>
 <div class="col-md-5"><div class="gc-detail-panel"><strong><i class="fa <?= $tipo === 'entradas' ? 'fa-arrow-down' : 'fa-arrow-up' ?> me-2"></i><?= $h($titulo) ?> por forma</strong>
 <ul class="list-group mt-2">
 <?php foreach(($detalhes[(int)$c['id']][$tipo] ?? []) as $mov): ?>
 <li class="list-group-item d-flex justify-content-between"><span><?= $h($mov['forma']) ?> (<?= (int)$mov['quantidade'] ?>)</span><strong><?= $moeda($mov['total']) ?></strong></li>
 <?php endforeach; ?>
 <?php if(empty($detalhes[(int)$c['id']][$tipo])): ?><li class="list-group-item text-muted">Sem movimentações pagas.</li><?php endif; ?>
 </ul></div></div>
 <?php endforeach; ?>
 <?php
 $dadosAuditados = null;
 if ($aud && !empty($aud['detalhes'])) {
     $json = json_decode($aud['detalhes'],true);
     if (is_array($json) && isset($json['entradas_dinheiro'],$json['saidas_dinheiro'],$json['sangrias'])) {
         $dadosAuditados=$json;
     }
 }
 ?>
 <div class="col-12 mt-3">
 <div class="gc-detail-panel">
 <strong><i class="fa fa-calculator me-1"></i> Conferência do numerário</strong>
 <?php if ($dadosAuditados): ?>
 <small class="d-block text-muted mb-2">Valores registrados no momento do fechamento (auditoria).</small>
 <div class="row g-2">
 <div class="col-md-2">Saldo inicial<br><strong><?= $moeda($c['valor_abertura']) ?></strong></div>
 <div class="col-md-2">+ Entradas em dinheiro<br><strong><?= $moeda($dadosAuditados['entradas_dinheiro']) ?></strong></div>
 <div class="col-md-2">− Saídas em dinheiro<br><strong><?= $moeda($dadosAuditados['saidas_dinheiro']) ?></strong></div>
 <div class="col-md-2">− Sangrias<br><strong><?= $moeda($dadosAuditados['sangrias']) ?></strong></div>
 <div class="col-md-2">= Saldo esperado<br><strong><?= $moeda($aud['saldo_esperado']) ?></strong></div>
 <div class="col-md-2">Contado / diferença<br><strong><?= $moeda($aud['valor_informado']) ?></strong><br><small><?= $moeda($aud['diferenca']) ?></small></div>
 </div>
 <?php elseif ($fechado): ?>
 <p class="text-muted mb-0 mt-2">Fechamento anterior à auditoria: não existe memória de cálculo confiável para reconstruir os valores da época.</p>
 <?php else: ?>
 <p class="text-muted mb-0 mt-2">Caixa em aberto. A conferência definitiva será registrada no fechamento.</p>
 <?php endif; ?>
 </div></div>
 <div class="col-md-2 order-md-first"><div class="gc-detail-panel"><strong><i class="fa fa-money-bill me-2"></i>Sangrias</strong><p class="mt-2 mb-0 gc-amount"><?= $moeda($detalhes[(int)$c['id']]['sangrias']['total'] ?? 0) ?></p><small><?= (int)($detalhes[(int)$c['id']]['sangrias']['quantidade'] ?? 0) ?> retirada(s)</small></div></div>
 </div>
</td></tr>
<?php endforeach; ?>
<?php if(!$caixas): ?><tr><td colspan="10" class="text-center text-muted">Nenhum caixa encontrado no período.</td></tr><?php endif; ?>
</tbody></table><small class="text-muted">Exibindo até 300 caixas no período. Diferença positiva indica sobra; negativa indica falta.</small>
</div></div>

</div>
