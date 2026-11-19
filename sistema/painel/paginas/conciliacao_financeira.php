<?php
// Conciliação gerencial somente leitura. Não equivale a extrato bancário conciliado.
if (!in_array($_SESSION['nivel'] ?? '', ['Administrador','Gerente'], true)) {
 http_response_code(403); echo '<div class="alert alert-danger">Acesso restrito ao financeiro.</div>'; return;
}
$empresaConc = (int)($id_empresa ?? 0);
if ($empresaConc <= 0) { http_response_code(403); return; }
$inicioConc = (string)($_GET['inicio'] ?? date('Y-m-01'));
$fimConc = (string)($_GET['fim'] ?? date('Y-m-d'));
$validarConc = static function($d) {
 return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && checkdate((int)substr($d,5,2),(int)substr($d,8,2),(int)substr($d,0,4));
};
if (!$validarConc($inicioConc) || !$validarConc($fimConc) || $inicioConc > $fimConc) {
 $inicioConc=date('Y-m-01'); $fimConc=date('Y-m-d');
}
$paramsConc=[':empresa'=>$empresaConc,':inicio'=>$inicioConc,':fim'=>$fimConc];
$consultaConc = static function($pdo,$sql,$params) {
 $st=$pdo->prepare($sql); $st->execute($params); return $st->fetchAll(PDO::FETCH_ASSOC);
};
$gruposConc=$consultaConc($pdo, "SELECT COALESCE(fp.nome,'Não identificada') AS forma, fp.id AS forma_id,
 COUNT(r.id) AS quantidade, COALESCE(SUM(r.valor),0) AS total
 FROM receber r LEFT JOIN formas_pgto fp ON fp.id=r.forma_pgto AND fp.empresa=r.empresa
 WHERE r.empresa=:empresa AND r.pago='Sim' AND r.data_pgto BETWEEN :inicio AND :fim
 GROUP BY fp.id,fp.nome ORDER BY total DESC", $paramsConc);
$pendentesConc=$consultaConc($pdo, "SELECT COALESCE(fp.nome,'Não identificada') AS forma,
 COUNT(r.id) AS quantidade, COALESCE(SUM(r.valor),0) AS total
 FROM receber r LEFT JOIN formas_pgto fp ON fp.id=r.forma_pgto AND fp.empresa=r.empresa
 WHERE r.empresa=:empresa AND r.pago='Não' AND r.vencimento BETWEEN :inicio AND :fim
 GROUP BY fp.id,fp.nome ORDER BY total DESC", $paramsConc);
$movimentosConc=$consultaConc($pdo, "SELECT r.id,r.descricao,r.valor,r.data_pgto,
 COALESCE(fp.nome,'Não identificada') AS forma
 FROM receber r LEFT JOIN formas_pgto fp ON fp.id=r.forma_pgto AND fp.empresa=r.empresa
 WHERE r.empresa=:empresa AND r.pago='Sim' AND r.data_pgto BETWEEN :inicio AND :fim
 ORDER BY r.data_pgto DESC,r.id DESC LIMIT 100", $paramsConc);
$somarConc=static function($lista) {return array_sum(array_map(static function($r){return (float)$r['total'];},$lista));};
// Taxas são simulações informadas pelo operador, não taxas contratadas nem liquidações.
// Carrega taxas persistidas somente quando a migração estiver disponível.
$taxasPersistidasConc=[];
$tabelaTaxasDisponivelConc=false;
try {
 $stTaxas=$pdo->prepare('SELECT forma_pgto,taxa_percentual FROM taxas_formas_pagamento WHERE empresa=:empresa');
 $stTaxas->execute([':empresa'=>$empresaConc]);
 foreach ($stTaxas->fetchAll(PDO::FETCH_ASSOC) as $linhaTaxa) $taxasPersistidasConc[(int)$linhaTaxa['forma_pgto']]=(float)$linhaTaxa['taxa_percentual'];
 $tabelaTaxasDisponivelConc=true;
} catch (PDOException $e) {
 error_log('Taxas de conciliação indisponíveis: '.$e->getMessage());
}
if (empty($_SESSION['csrf_conc'])) $_SESSION['csrf_conc']=bin2hex(random_bytes(32));
$taxasConc = [];
$entradaTaxas = $_GET['taxas'] ?? [];
if (is_array($entradaTaxas)) {
 foreach ($entradaTaxas as $chave=>$valor) {
  if (!ctype_digit((string)$chave) || !is_scalar($valor)) continue;
  $texto = str_replace(',', '.', trim((string)$valor));
  if (!preg_match('/^\d{1,3}(?:\.\d{1,4})?$/', $texto)) continue;
  $taxa = (float)$texto;
  if ($taxa >= 0 && $taxa <= 100) $taxasConc[(int)$chave]=$taxa;
 }
}
$taxasConc = array_replace($taxasPersistidasConc, $taxasConc);
$totalTaxasEstimadasConc=0.0;
$totalLiquidoEstimadoConc=0.0;
$formasSimuladasConc=0;
foreach ($gruposConc as &$grupoConc) {
 $idFormaConc = (int)($grupoConc['forma_id'] ?? 0);
 $grupoConc['taxa_informada'] = $idFormaConc > 0 && array_key_exists($idFormaConc,$taxasConc) ? $taxasConc[$idFormaConc] : null;
 $grupoConc['desconto_estimado'] = $grupoConc['taxa_informada'] === null ? null : round((float)$grupoConc['total']*$grupoConc['taxa_informada']/100,2);
 $grupoConc['liquido_estimado'] = $grupoConc['desconto_estimado'] === null ? null : round((float)$grupoConc['total']-$grupoConc['desconto_estimado'],2);
 if ($grupoConc['desconto_estimado'] !== null) {
  $formasSimuladasConc++;
  $totalTaxasEstimadasConc += $grupoConc['desconto_estimado'];
  $totalLiquidoEstimadoConc += $grupoConc['liquido_estimado'];
 }
}
unset($grupoConc);
$liquidacoesConc=[];$liquidacoesPorFormaConc=[];$liquidacoesDisponiveisConc=false;
try {
 $stL=$pdo->prepare("SELECT l.id,l.forma_pgto,l.data_referencia,l.data_credito,l.valor_creditado,l.identificador,l.observacao,fp.nome AS forma FROM conciliacao_liquidacoes l INNER JOIN formas_pgto fp ON fp.id=l.forma_pgto AND fp.empresa=l.empresa WHERE l.empresa=:empresa AND l.data_referencia BETWEEN :inicio AND :fim ORDER BY l.data_referencia DESC,l.id DESC LIMIT 100");
 $stL->execute($paramsConc);$liquidacoesConc=$stL->fetchAll(PDO::FETCH_ASSOC);
 $stS=$pdo->prepare("SELECT forma_pgto,SUM(valor_creditado) AS total FROM conciliacao_liquidacoes WHERE empresa=:empresa AND data_referencia BETWEEN :inicio AND :fim GROUP BY forma_pgto");
 $stS->execute($paramsConc);
 foreach($stS->fetchAll(PDO::FETCH_ASSOC) as $l) $liquidacoesPorFormaConc[(int)$l['forma_pgto']]=(float)$l['total'];
 $liquidacoesDisponiveisConc=true;
} catch(PDOException $e) {error_log('Liquidacoes indisponiveis: '.$e->getMessage());}
// Alertas gerenciais: diferença por forma e falta de crédito informado.
$alertasConc=[];$totalCreditoConc=0.0;$totalEsperadoComparavelConc=0.0;
$limiarConc=0.01;
if ($liquidacoesDisponiveisConc) {
 foreach ($gruposConc as $linhaAlerta) {
  $idAlerta=(int)($linhaAlerta['forma_id']??0);
  $nomeAlerta=(string)$linhaAlerta['forma'];
  $creditoAlerta=(float)($liquidacoesPorFormaConc[$idAlerta]??0);
  $totalCreditoConc+=$creditoAlerta;
  if ($linhaAlerta['liquido_estimado']===null) {
   $alertasConc[]=['tipo'=>'warning','texto'=>'Taxa não configurada para '.$nomeAlerta.'. Não é possível calcular a diferença líquida estimada.'];
   continue;
  }
  $esperadoAlerta=(float)$linhaAlerta['liquido_estimado'];
  $totalEsperadoComparavelConc+=$esperadoAlerta;
  $diferencaAlerta=round($creditoAlerta-$esperadoAlerta,2);
  if ($creditoAlerta<=0 && $esperadoAlerta>0) {
   $alertasConc[]=['tipo'=>'warning','texto'=>'Nenhum crédito informado para '.$nomeAlerta.' no período. Pode haver repasse futuro.'];
  } elseif ($diferencaAlerta < -$limiarConc) {
   $alertasConc[]=['tipo'=>'danger','texto'=>'Crédito informado abaixo do líquido estimado para '.$nomeAlerta.' (diferença: R$ '.number_format($diferencaAlerta,2,',','.').').'];
  } elseif ($diferencaAlerta > $limiarConc) {
   $alertasConc[]=['tipo'=>'warning','texto'=>'Crédito informado acima do líquido estimado para '.$nomeAlerta.' (diferença: R$ '.number_format($diferencaAlerta,2,',','.').').'];
  }
 }
}
// Pendências de conciliação por forma, sem presumir prazo de repasse.
$pendenciasLiquidacaoConc=[];
$totalPendenteLiquidacaoConc=0.0;
$qtdePendenciasLiquidacaoConc=0;
if ($liquidacoesDisponiveisConc) {
 foreach ($gruposConc as $grupoPendenciaConc) {
  $idFormaPendenciaConc=(int)($grupoPendenciaConc['forma_id']??0);
  $esperadoPendenciaConc=$grupoPendenciaConc['liquido_estimado'];
  $creditoPendenciaConc=(float)($liquidacoesPorFormaConc[$idFormaPendenciaConc]??0);
  $diferencaPendenciaConc=$esperadoPendenciaConc===null ? null : round((float)$esperadoPendenciaConc-$creditoPendenciaConc,2);
  $situacaoPendenciaConc=$esperadoPendenciaConc===null?'Sem taxa':($diferencaPendenciaConc>0.01?'Pendente':($diferencaPendenciaConc < -0.01?'Crédito acima':'Conferido'));
  if ($situacaoPendenciaConc==='Pendente') {
   $qtdePendenciasLiquidacaoConc++;
   $totalPendenteLiquidacaoConc+=$diferencaPendenciaConc;
  }
  $pendenciasLiquidacaoConc[]=[
   'forma'=>(string)$grupoPendenciaConc['forma'],
   'bruto'=>(float)$grupoPendenciaConc['total'],
   'esperado'=>$esperadoPendenciaConc,
   'credito'=>$creditoPendenciaConc,
   'diferenca'=>$diferencaPendenciaConc,
   'situacao'=>$situacaoPendenciaConc
  ];
 }
}
$filtroPendenciasConc=(string)($_GET['situacao_liquidacao']??'todas');
if (!in_array($filtroPendenciasConc,['todas','Pendente','Conferido','Crédito acima','Sem taxa'],true)) $filtroPendenciasConc='todas';
$totalConc=$somarConc($gruposConc); $totalPendenteConc=$somarConc($pendentesConc);
$moedaConc=static function($v){return 'R$ '.number_format((float)$v,2,',','.');};
$escConc=static function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
?>
<style>
.bc-conc{padding:18px 0}.bc-conc-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-bottom:18px}
.bc-conc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:15px}
.bc-conc-card small{display:block;color:#64748b}.bc-conc-card strong{font-size:24px}
.bc-conc-filter{display:flex;gap:12px;align-items:end;flex-wrap:wrap}.bc-conc-filter label{display:block;font-weight:600}
.bc-conc-filter input{padding:7px;border:1px solid #cbd5e1;border-radius:7px}
.bc-conc-table{overflow-x:auto}.bc-conc-table table{width:100%;border-collapse:collapse}
.bc-conc-table th,.bc-conc-table td{padding:11px;border-bottom:1px solid #e2e8f0;text-align:left}
.bc-conc-table th{background:#f8fafc}.bc-conc-note{font-size:13px;color:#64748b}
</style>
<div class="bc-conc">
<div class="bc-conc-card">
<h3><i class="fa fa-check-square-o"></i> Conciliação Financeira</h3>
<p class="bc-conc-note">Conferência de contas a receber baixadas por forma de pagamento. Os valores representam baixas registradas no sistema, não depósitos bancários confirmados.</p>
<form class="bc-conc-filter" method="get" action="index.php">
<input type="hidden" name="pagina" value="conciliacao_financeira">
<div><label for="conc-inicio">Início</label><input id="conc-inicio" type="date" name="inicio" value="<?php echo $escConc($inicioConc); ?>"></div>
<div><label for="conc-fim">Fim</label><input id="conc-fim" type="date" name="fim" value="<?php echo $escConc($fimConc); ?>"></div>
<button class="btn btn-primary" type="submit">Consultar</button>
</form></div>
<div class="bc-conc-grid">
<div class="bc-conc-card"><small>Recebimentos baixados no período</small><strong><?php echo $moedaConc($totalConc); ?></strong></div>
<div class="bc-conc-card"><small>Recebimentos pendentes com vencimento no período</small><strong><?php echo $moedaConc($totalPendenteConc); ?></strong></div>
<div class="bc-conc-card"><small>Registros de recebimento baixados</small><strong><?php echo array_sum(array_column($gruposConc,'quantidade')); ?></strong></div>
</div>
<div class="bc-conc-card"><h5>Recebimentos por forma de pagamento</h5>
<div class="bc-conc-table"><table><thead><tr><th>Forma</th><th>Quantidade</th><th>Valor baixado</th></tr></thead><tbody>
<?php foreach($gruposConc as $linha): ?><tr><td><?php echo $escConc($linha['forma']); ?></td><td><?php echo (int)$linha['quantidade']; ?></td><td><?php echo $moedaConc($linha['total']); ?></td></tr><?php endforeach; ?>
<?php if (!$gruposConc): ?><tr><td colspan="3">Sem recebimentos baixados no período.</td></tr><?php endif; ?>
</tbody><tfoot><tr><th>Total</th><th></th><th><?php echo $moedaConc($totalConc); ?></th></tr></tfoot></table></div></div>
<?php if (isset($_GET['taxas_salvas'])): ?><div class="alert alert-success">Taxas salvas para este estabelecimento.</div><?php endif; ?>
<?php if (!$tabelaTaxasDisponivelConc): ?><div class="alert alert-warning">As taxas ainda não podem ser salvas. Execute a migração SQL taxas_formas_pagamento.sql no banco da Hostinger.</div><?php endif; ?>
<div class="bc-conc-card">
<h5>Simulador de taxas por forma de pagamento</h5>
<p class="bc-conc-note">Informe a taxa percentual contratada para cada forma. O cálculo é apenas uma estimativa sobre recebimentos baixados; não confirma depósitos. Campos em branco não entram no total líquido estimado. As taxas não são salvas no banco.</p>
<form method="get" action="index.php">
<input type="hidden" name="pagina" value="conciliacao_financeira">
<input type="hidden" name="inicio" value="<?php echo $escConc($inicioConc); ?>">
<input type="hidden" name="fim" value="<?php echo $escConc($fimConc); ?>">
<div class="bc-conc-table"><table>
<thead><tr><th>Forma</th><th>Bruto</th><th>Taxa (%)</th><th>Taxa estimada</th><th>Líquido estimado</th></tr></thead><tbody>
<?php foreach($gruposConc as $linha): ?>
<tr><td><?php echo $escConc($linha['forma']); ?></td><td><?php echo $moedaConc($linha['total']); ?></td>
<td><?php if ((int)$linha['forma_id']>0): ?>
<input style="width:105px" aria-label="Taxa de <?php echo $escConc($linha['forma']); ?>" type="number" min="0" max="100" step="0.0001" name="taxas[<?php echo (int)$linha['forma_id']; ?>]" value="<?php echo $linha['taxa_informada'] === null ? '' : $escConc($linha['taxa_informada']); ?>" placeholder="Não informada">
<?php else: ?>Não identificada<?php endif; ?></td>
<td><?php echo $linha['desconto_estimado'] === null ? '—' : $moedaConc($linha['desconto_estimado']); ?></td>
<td><?php echo $linha['liquido_estimado'] === null ? '—' : $moedaConc($linha['liquido_estimado']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$gruposConc): ?><tr><td colspan="5">Sem recebimentos para simular.</td></tr><?php endif; ?>
</tbody></table></div>
<button class="btn btn-primary mt-3" type="submit">Calcular estimativa</button>
<?php if ($tabelaTaxasDisponivelConc): ?>
<button type="submit" class="btn btn-outline-primary mt-3" form="salvar-taxas-conc">Salvar taxas do estabelecimento</button>
<?php endif; ?>
<p class="bc-conc-note mt-2">Formas calculadas: <?php echo $formasSimuladasConc; ?> de <?php echo count($gruposConc); ?>.
Taxas estimadas nas formas preenchidas: <strong><?php echo $moedaConc($totalTaxasEstimadasConc); ?></strong>.
Líquido estimado apenas dessas formas: <strong><?php echo $moedaConc($totalLiquidoEstimadoConc); ?></strong>.</p>
</form>
<?php if ($tabelaTaxasDisponivelConc): ?>
<form id="salvar-taxas-conc" method="post" action="paginas/salvar_taxas_conciliacao.php">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<?php foreach ($gruposConc as $linha): if ((int)$linha['forma_id'] > 0): ?>
<input type="hidden" name="taxas[<?php echo (int)$linha['forma_id']; ?>]" value="<?php echo $linha['taxa_informada'] === null ? '' : $escConc($linha['taxa_informada']); ?>">
<?php endif; endforeach; ?>
</form>
<p class="bc-conc-note">Para salvar, primeiro clique em Calcular estimativa e depois em Salvar taxas do estabelecimento. As taxas salvas serão usadas em futuras consultas.</p>
<?php endif; ?>
</div>
<div class="bc-conc-card">
<h5><i class="fa fa-hourglass-half"></i> Acompanhamento de liquidações pendentes</h5>
<p class="bc-conc-note">Compara o líquido estimado das baixas com os créditos informados para a mesma data de referência. Não representa cobrança vencida: prazos de repasse, estornos e antecipações não são calculados.</p>
<?php if (!$liquidacoesDisponiveisConc): ?>
<div class="alert alert-warning">A consulta de liquidações está indisponível.</div>
<?php else: ?>
<div class="bc-conc-grid">
<div class="bc-conc-card"><small>Saldo ainda não conciliado (estimado)</small><strong><?php echo $moedaConc($totalPendenteLiquidacaoConc); ?></strong></div>
<div class="bc-conc-card"><small>Formas com diferença a conferir</small><strong><?php echo $qtdePendenciasLiquidacaoConc; ?></strong></div>
</div>
<form method="get" action="index.php" class="bc-conc-filter">
<input type="hidden" name="pagina" value="conciliacao_financeira">
<input type="hidden" name="inicio" value="<?php echo $escConc($inicioConc); ?>">
<input type="hidden" name="fim" value="<?php echo $escConc($fimConc); ?>">
<div><label for="conc-situacao">Situação</label>
<select id="conc-situacao" name="situacao_liquidacao" class="form-control">
<?php foreach(['todas'=>'Todas','Pendente'=>'Pendente','Conferido'=>'Conferido','Crédito acima'=>'Crédito acima','Sem taxa'=>'Sem taxa'] as $valorSituacaoConc=>$rotuloSituacaoConc): ?>
<option value="<?php echo $escConc($valorSituacaoConc); ?>" <?php echo $filtroPendenciasConc===$valorSituacaoConc?'selected':''; ?>><?php echo $escConc($rotuloSituacaoConc); ?></option>
<?php endforeach; ?>
</select></div>
<button type="submit" class="btn btn-primary">Filtrar</button>
</form>
<div class="bc-conc-table mt-3"><table><thead><tr><th>Forma</th><th>Líquido estimado</th><th>Crédito informado</th><th>Saldo a conferir</th><th>Situação</th></tr></thead><tbody>
<?php $linhasPendenciaExibidasConc=0; foreach($pendenciasLiquidacaoConc as $pendenciaConc):
if ($filtroPendenciasConc!=='todas' && $filtroPendenciasConc!==$pendenciaConc['situacao']) continue;
$linhasPendenciaExibidasConc++; ?>
<tr><td><?php echo $escConc($pendenciaConc['forma']); ?></td>
<td><?php echo $pendenciaConc['esperado']===null?'—':$moedaConc($pendenciaConc['esperado']); ?></td>
<td><?php echo $moedaConc($pendenciaConc['credito']); ?></td>
<td><?php echo $pendenciaConc['diferenca']===null?'—':$moedaConc($pendenciaConc['diferenca']); ?></td>
<td><?php echo $escConc($pendenciaConc['situacao']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$linhasPendenciaExibidasConc): ?><tr><td colspan="5">Nenhum registro para o filtro selecionado.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endif; ?>
</div>
<div class="bc-conc-card">
<h5><i class="fa fa-exclamation-triangle"></i> Alertas de conferência</h5>
<p class="bc-conc-note">Alertas baseados nos recebimentos baixados e créditos informados no período selecionado. Não significam automaticamente perda, fraude ou atraso: repasses podem ocorrer em datas posteriores.</p>
<?php if (!$liquidacoesDisponiveisConc): ?>
<div class="alert alert-warning">Conferência indisponível enquanto a tabela de liquidações não estiver acessível.</div>
<?php elseif (!$gruposConc): ?>
<div class="alert alert-info">Sem recebimentos baixados no período para comparar.</div>
<?php elseif (!$alertasConc): ?>
<div class="alert alert-success">Nenhuma divergência detectada nas formas comparáveis neste período. Confirme também os extratos e repasses futuros.</div>
<?php else: ?>
<?php foreach ($alertasConc as $alertaConc): ?>
<div class="alert alert-<?php echo $escConc($alertaConc['tipo']); ?>"><?php echo $escConc($alertaConc['texto']); ?></div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<div class="bc-conc-card">
<h5>Liquidações informadas pelo estabelecimento</h5>
<p class="bc-conc-note">Registre créditos conferidos no extrato bancário. A data de referência associa o crédito ao período das vendas; a data do crédito é a data em que o valor entrou na conta. Estes registros não alteram caixa ou contas a receber.</p>
<?php if (!$liquidacoesDisponiveisConc): ?>
<div class="alert alert-warning">Execute a migração SQL conciliacao_liquidacoes.sql para ativar este recurso.</div>
<?php else: ?>
<?php if(isset($_GET['liquidacao_salva'])): ?><div class="alert alert-success">Crédito registrado com sucesso.</div><?php endif; ?>
<form method="post" action="paginas/salvar_liquidacao_conciliacao.php">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<div class="bc-conc-filter">
<div><label>Forma de pagamento</label><select name="forma_pgto" class="form-control" required>
<option value="">Selecione</option>
<?php
$formasCadastroConc=$pdo->prepare('SELECT id,nome FROM formas_pgto WHERE empresa=:empresa ORDER BY nome');
$formasCadastroConc->execute([':empresa'=>$empresaConc]);
foreach($formasCadastroConc->fetchAll(PDO::FETCH_ASSOC) as $fpConc): ?>
<option value="<?php echo (int)$fpConc['id']; ?>"><?php echo $escConc($fpConc['nome']); ?></option>
<?php endforeach; ?>
</select></div>
<div><label>Data de referência</label><input required type="date" name="data_referencia" value="<?php echo $escConc($fimConc); ?>"></div>
<div><label>Data do crédito</label><input required type="date" name="data_credito" value="<?php echo date('Y-m-d'); ?>"></div>
<div><label>Valor creditado (R$)</label><input required name="valor_creditado" inputmode="decimal" placeholder="0,00"></div>
<div><label>Identificador (opcional)</label><input maxlength="120" name="identificador" placeholder="NSU ou referência"></div>
<div><label>Observação</label><input maxlength="500" name="observacao"></div>
<button class="btn btn-primary" type="submit">Registrar crédito</button>
</div></form>
<h6 class="mt-4">Comparação por forma de pagamento no período</h6>
<div class="bc-conc-table"><table><thead><tr><th>Forma</th><th>Líquido estimado</th><th>Crédito informado</th><th>Diferença</th></tr></thead><tbody>
<?php foreach($gruposConc as $linha):
$idForma=(int)$linha['forma_id'];
$credito=(float)($liquidacoesPorFormaConc[$idForma]??0);
$liquido=$linha['liquido_estimado'];
?>
<tr><td><?php echo $escConc($linha['forma']); ?></td>
<td><?php echo $liquido===null?'Taxa não informada':$moedaConc($liquido); ?></td>
<td><?php echo $moedaConc($credito); ?></td>
<td><?php echo $liquido===null?'—':$moedaConc($credito-(float)$liquido); ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<p class="bc-conc-note">Diferenças podem resultar de prazos de repasse, antecipações, estornos ou taxas. A comparação é gerencial e não comprova conciliação bancária automática.</p>
<h6 class="mt-4">Últimos 100 créditos informados no período</h6>
<div class="bc-conc-table"><table><thead><tr><th>Referência</th><th>Crédito</th><th>Forma</th><th>Valor</th><th>Identificador</th></tr></thead><tbody>
<?php foreach($liquidacoesConc as $l): ?><tr>
<td><?php echo $escConc($l['data_referencia']); ?></td><td><?php echo $escConc($l['data_credito']); ?></td><td><?php echo $escConc($l['forma']); ?></td><td><?php echo $moedaConc($l['valor_creditado']); ?></td><td><?php echo $escConc($l['identificador']??''); ?></td></tr><?php endforeach; ?>
<?php if(!$liquidacoesConc): ?><tr><td colspan="5">Nenhum crédito informado.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endif; ?>
</div>
<div class="bc-conc-card"><h5>Pendências por forma de pagamento</h5>
<div class="bc-conc-table"><table><thead><tr><th>Forma</th><th>Quantidade</th><th>Valor pendente</th></tr></thead><tbody>
<?php foreach($pendentesConc as $linha): ?><tr><td><?php echo $escConc($linha['forma']); ?></td><td><?php echo (int)$linha['quantidade']; ?></td><td><?php echo $moedaConc($linha['total']); ?></td></tr><?php endforeach; ?>
<?php if (!$pendentesConc): ?><tr><td colspan="3">Sem pendências com vencimento no período.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="bc-conc-card"><h5>Últimos 100 recebimentos baixados do período</h5>
<div class="bc-conc-table"><table><thead><tr><th>Data</th><th>Lançamento</th><th>Descrição</th><th>Forma</th><th>Valor</th></tr></thead><tbody>
<?php foreach($movimentosConc as $m): ?><tr>
<td><?php echo $escConc(date('d/m/Y',strtotime($m['data_pgto']))); ?></td><td>#<?php echo (int)$m['id']; ?></td>
<td><?php echo $escConc($m['descricao']); ?></td><td><?php echo $escConc($m['forma']); ?></td><td><?php echo $moedaConc($m['valor']); ?></td>
</tr><?php endforeach; ?>
<?php if (!$movimentosConc): ?><tr><td colspan="5">Nenhuma baixa encontrada.</td></tr><?php endif; ?>
</tbody></table></div>
<p class="bc-conc-note">A simulação de taxas acima não substitui a conciliação bancária. A integração com extratos, liquidações e taxas persistidas continua pendente.</p>
</div></div>
