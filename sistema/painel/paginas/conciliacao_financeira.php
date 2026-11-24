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
$resumoAuditoriaConc=['ativos'=>0,'cancelados'=>0,'valor_ativo'=>0.0,'valor_cancelado'=>0.0];
$filtroHistoricoConc=(string)($_GET['historico']??'todos');
$buscaLiqConc=trim((string)($_GET['liq_busca']??''));
if(mb_strlen($buscaLiqConc)>120)$buscaLiqConc=mb_substr($buscaLiqConc,0,120);
$porPaginaLiqConc=25;
$paginaLiqConc=filter_var($_GET['liq_pagina']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:1;
$totalLiqConc=0;$paginasLiqConc=1;
if(!in_array($filtroHistoricoConc,['todos','ativos','cancelados'],true))$filtroHistoricoConc='todos';

$auditoriaCancelamentosConc=[];
$totalCancelamentosAudConc=0;
$porPaginaAudConc=25;
$paginaAudConc=filter_var($_GET['cancel_pagina']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:1;
$paginasAudConc=1;
$audInicioConc=(string)($_GET['cancel_inicio']??$inicioConc);
$audFimConc=(string)($_GET['cancel_fim']??$fimConc);
$audUsuarioConc=trim((string)($_GET['cancel_usuario']??''));
if(!$validarConc($audInicioConc)||!$validarConc($audFimConc)||$audInicioConc>$audFimConc){$audInicioConc=$inicioConc;$audFimConc=$fimConc;}
if($audUsuarioConc!==''&&(!ctype_digit($audUsuarioConc)||(int)$audUsuarioConc<1))$audUsuarioConc='';
try {
 $sqlAudCancel="SELECT l.id,l.forma_pgto,fp.nome AS forma,l.data_referencia,l.data_credito,l.valor_creditado,l.identificador,l.cancelado_em,l.cancelado_por,l.motivo_cancelamento
 FROM conciliacao_liquidacoes l
 INNER JOIN formas_pgto fp ON fp.id=l.forma_pgto AND fp.empresa=l.empresa
 WHERE l.empresa=:empresa AND l.cancelado_em >= :inicio AND l.cancelado_em < DATE_ADD(:fim,INTERVAL 1 DAY)";
 $paramsAudCancel=[':empresa'=>$empresaConc,':inicio'=>$audInicioConc,':fim'=>$audFimConc];
 if($audUsuarioConc!==''){$sqlAudCancel.=" AND l.cancelado_por=:usuario";$paramsAudCancel[':usuario']=(int)$audUsuarioConc;}
 $sqlCountAud="SELECT COUNT(*) FROM conciliacao_liquidacoes l WHERE l.empresa=:empresa AND l.cancelado_em >= :inicio AND l.cancelado_em < DATE_ADD(:fim,INTERVAL 1 DAY)";
 if($audUsuarioConc!=='')$sqlCountAud.=" AND l.cancelado_por=:usuario";
 $stCountAud=$pdo->prepare($sqlCountAud);
 $stCountAud->execute($paramsAudCancel);
 $totalCancelamentosAudConc=(int)$stCountAud->fetchColumn();
 $paginasAudConc=max(1,(int)ceil($totalCancelamentosAudConc/$porPaginaAudConc));
 $paginaAudConc=min($paginaAudConc,$paginasAudConc);
 $offsetAudConc=($paginaAudConc-1)*$porPaginaAudConc;
 $sqlAudCancel.=" ORDER BY l.cancelado_em DESC,l.id DESC LIMIT ".$porPaginaAudConc." OFFSET ".$offsetAudConc;
 $stAudCancel=$pdo->prepare($sqlAudCancel);
 $stAudCancel->execute($paramsAudCancel);
 $auditoriaCancelamentosConc=$stAudCancel->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e){error_log('Auditoria de cancelamentos indisponivel: '.$e->getMessage());}
try {
 $filtroSqlLiqConc=$filtroHistoricoConc==='ativos'?' AND l.cancelado_em IS NULL':($filtroHistoricoConc==='cancelados'?' AND l.cancelado_em IS NOT NULL':'');
 $paramsLiqConc=$paramsConc;
 if($buscaLiqConc!==''){$filtroSqlLiqConc.=" AND (l.identificador LIKE :busca ESCAPE '!' OR CAST(l.id AS CHAR) = :idbusca)";$paramsLiqConc[':busca']='%'.str_replace(['!','%','_'],['!!','!%','!_'],$buscaLiqConc).'%';$paramsLiqConc[':idbusca']=$buscaLiqConc;}
 $stCountLiqConc=$pdo->prepare("SELECT COUNT(*) FROM conciliacao_liquidacoes l WHERE l.empresa=:empresa AND l.data_referencia BETWEEN :inicio AND :fim".$filtroSqlLiqConc);
 $stCountLiqConc->execute($paramsLiqConc);
 $totalLiqConc=(int)$stCountLiqConc->fetchColumn();
 $paginasLiqConc=max(1,(int)ceil($totalLiqConc/$porPaginaLiqConc));
 $paginaLiqConc=min($paginaLiqConc,$paginasLiqConc);
 $offsetLiqConc=($paginaLiqConc-1)*$porPaginaLiqConc;
 $sqlLiqConc="SELECT l.id,l.forma_pgto,l.data_referencia,l.data_credito,l.valor_creditado,l.identificador,l.observacao,l.cancelado_em,l.cancelado_por,l.motivo_cancelamento,(SELECT COUNT(*) FROM conciliacao_vinculos_recebimentos v WHERE v.empresa=l.empresa AND v.liquidacao_id=l.id) AS total_vinculos,fp.nome AS forma FROM conciliacao_liquidacoes l INNER JOIN formas_pgto fp ON fp.id=l.forma_pgto AND fp.empresa=l.empresa WHERE l.empresa=:empresa AND l.data_referencia BETWEEN :inicio AND :fim".$filtroSqlLiqConc." ORDER BY l.data_referencia DESC,l.id DESC LIMIT ".$porPaginaLiqConc." OFFSET ".$offsetLiqConc;
 $stL=$pdo->prepare($sqlLiqConc);
 $stL->execute($paramsLiqConc);$liquidacoesConc=$stL->fetchAll(PDO::FETCH_ASSOC);
 $stAud=$pdo->prepare("SELECT
 SUM(CASE WHEN cancelado_em IS NULL THEN 1 ELSE 0 END) AS ativos,
 SUM(CASE WHEN cancelado_em IS NOT NULL THEN 1 ELSE 0 END) AS cancelados,
 COALESCE(SUM(CASE WHEN cancelado_em IS NULL THEN valor_creditado ELSE 0 END),0) AS valor_ativo,
 COALESCE(SUM(CASE WHEN cancelado_em IS NOT NULL THEN valor_creditado ELSE 0 END),0) AS valor_cancelado
 FROM conciliacao_liquidacoes WHERE empresa=:empresa AND data_referencia BETWEEN :inicio AND :fim");
 $stAud->execute($paramsConc);
 $resumoAuditoriaConc=$stAud->fetch(PDO::FETCH_ASSOC)?:$resumoAuditoriaConc;
 $stS=$pdo->prepare("SELECT forma_pgto,SUM(valor_creditado) AS total FROM conciliacao_liquidacoes WHERE empresa=:empresa AND cancelado_em IS NULL AND data_referencia BETWEEN :inicio AND :fim GROUP BY forma_pgto");
 $stS->execute($paramsConc);
 foreach($stS->fetchAll(PDO::FETCH_ASSOC) as $l) $liquidacoesPorFormaConc[(int)$l['forma_pgto']]=(float)$l['total'];
 $liquidacoesDisponiveisConc=true;
} catch(PDOException $e) {error_log('Liquidacoes indisponiveis: '.$e->getMessage());}
$indicadoresExtratoConc=['total'=>0,'vinculados'=>0,'pendentes'=>0,'valor_vinculado'=>0,'valor_pendente'=>0,'divergentes'=>0];$alertasExtratoConc=[];$auditoriaDesvinculosExtratoConc=[];$auditoriaVinculosExtratoConc=[];$extratoLinhasConc=[];$vinculosConc=[];$resumoVinculosConc=[];$auditoriaImportacoesConc=[];$auditoriaDesvinculosConc=[];
$buscaExtratoConc=trim((string)($_GET['extrato_busca']??''));
if(mb_strlen($buscaExtratoConc)>120)$buscaExtratoConc=mb_substr($buscaExtratoConc,0,120);
$situacaoExtratoConc=(string)($_GET['extrato_situacao']??'todos');
if(!in_array($situacaoExtratoConc,['todos','pendentes','vinculados'],true))$situacaoExtratoConc='todos';
$paginaExtratoConc=filter_var($_GET['extrato_pagina']??1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:1;
$totalExtratoConc=0;$paginasExtratoConc=1;$porPaginaExtratoConc=25;
try {
 // Indicadores do extrato por data de movimentação; vínculo não significa aprovação contábil.
 $stIndic=$pdo->prepare("SELECT COUNT(*) AS total,
 COALESCE(SUM(CASE WHEN e.liquidacao_id IS NOT NULL THEN 1 ELSE 0 END),0) AS vinculados,
 COALESCE(SUM(CASE WHEN e.liquidacao_id IS NULL THEN 1 ELSE 0 END),0) AS pendentes,
 COALESCE(SUM(CASE WHEN e.liquidacao_id IS NOT NULL AND e.valor>0 THEN e.valor ELSE 0 END),0) AS valor_vinculado,
 COALESCE(SUM(CASE WHEN e.liquidacao_id IS NULL AND e.valor>0 THEN e.valor ELSE 0 END),0) AS valor_pendente,
 COALESCE(SUM(CASE WHEN e.liquidacao_id IS NOT NULL AND (l.id IS NULL OR l.cancelado_em IS NOT NULL OR e.valor<>l.valor_creditado) THEN 1 ELSE 0 END),0) AS divergentes
 FROM conciliacao_extrato_linhas e LEFT JOIN conciliacao_liquidacoes l ON l.id=e.liquidacao_id AND l.empresa=e.empresa
 WHERE e.empresa=:empresa AND e.data_movimento BETWEEN :inicio AND :fim");
 $stIndic->execute($paramsConc);$indicadoresExtratoConc=$stIndic->fetch(PDO::FETCH_ASSOC)?:$indicadoresExtratoConc;
 // Alertas de conferência: somente leitura, limitados ao período e empresa.
 $stAlertasExt=$pdo->prepare("SELECT
 COALESCE(SUM(CASE WHEN e.valor>0 AND e.liquidacao_id IS NULL AND e.data_movimento<DATE_SUB(CURDATE(),INTERVAL 3 DAY) THEN 1 ELSE 0 END),0) AS creditos_antigos,
 COALESCE(SUM(CASE WHEN e.valor>0 AND e.liquidacao_id IS NULL AND e.data_movimento>=DATE_SUB(CURDATE(),INTERVAL 3 DAY) THEN 1 ELSE 0 END),0) AS creditos_recentes,
 COALESCE(SUM(CASE WHEN e.valor<0 AND e.liquidacao_id IS NULL THEN 1 ELSE 0 END),0) AS debitos_conferencia
 FROM conciliacao_extrato_linhas e WHERE e.empresa=:empresa AND e.data_movimento BETWEEN :inicio AND :fim");
 $stAlertasExt->execute($paramsConc);$alertasExtratoConc=$stAlertasExt->fetch(PDO::FETCH_ASSOC)?:[];
 $whereExtratoConc=" WHERE e.empresa=:empresa AND e.data_movimento BETWEEN :inicio AND :fim";
 $paramsExtratoConc=$paramsConc;
 if($buscaExtratoConc!==''){
  $whereExtratoConc.=" AND (e.identificador LIKE :busca ESCAPE '!' OR e.descricao LIKE :busca2 ESCAPE '!')";
  $buscaSqlExtratoConc='%'.str_replace(['!','%','_'],['!!','!%','!_'],$buscaExtratoConc).'%';
  $paramsExtratoConc[':busca']=$buscaSqlExtratoConc;$paramsExtratoConc[':busca2']=$buscaSqlExtratoConc;
 }
 if($situacaoExtratoConc==='pendentes')$whereExtratoConc.=" AND e.liquidacao_id IS NULL";
 if($situacaoExtratoConc==='vinculados')$whereExtratoConc.=" AND e.liquidacao_id IS NOT NULL";
 $stCountExtratoConc=$pdo->prepare("SELECT COUNT(*) FROM conciliacao_extrato_linhas e".$whereExtratoConc);
 $stCountExtratoConc->execute($paramsExtratoConc);$totalExtratoConc=(int)$stCountExtratoConc->fetchColumn();
 $paginasExtratoConc=max(1,(int)ceil($totalExtratoConc/$porPaginaExtratoConc));
 $paginaExtratoConc=min($paginaExtratoConc,$paginasExtratoConc);
 $offsetExtratoConc=($paginaExtratoConc-1)*$porPaginaExtratoConc;
 $stExt=$pdo->prepare("SELECT e.id,e.data_movimento,e.valor,e.descricao,e.identificador,e.liquidacao_id,i.nome_arquivo FROM conciliacao_extrato_linhas e INNER JOIN conciliacao_extrato_importacoes i ON i.id=e.importacao_id AND i.empresa=e.empresa".$whereExtratoConc." ORDER BY e.data_movimento DESC,e.id DESC LIMIT ".$porPaginaExtratoConc." OFFSET ".$offsetExtratoConc);
 $stExt->execute($paramsExtratoConc);$extratoLinhasConc=$stExt->fetchAll(PDO::FETCH_ASSOC);
 // Sugestões apenas de leitura: crédito positivo, mesmo valor e data de crédito em +/- 3 dias.
 // Não vincula, não altera o caixa e não confirma a conciliação.
 if($extratoLinhasConc){
  $idsExt=array_map('intval',array_column($extratoLinhasConc,'id'));
  $marksExt=implode(',',array_fill(0,count($idsExt),'?'));
  $sqlSug="SELECT e.id AS extrato_id,COUNT(l.id) AS candidatos,MIN(l.id) AS credito_sugerido
   FROM conciliacao_extrato_linhas e
   LEFT JOIN conciliacao_liquidacoes l ON l.empresa=e.empresa
    AND l.cancelado_em IS NULL AND l.valor_creditado=e.valor
    AND NOT EXISTS (SELECT 1 FROM conciliacao_extrato_linhas ocupado WHERE ocupado.empresa=l.empresa AND ocupado.liquidacao_id=l.id)
    AND l.data_credito BETWEEN DATE_SUB(e.data_movimento,INTERVAL 3 DAY) AND DATE_ADD(e.data_movimento,INTERVAL 3 DAY)
   WHERE e.empresa=? AND e.id IN (".$marksExt.") AND e.valor>0 AND e.liquidacao_id IS NULL
   GROUP BY e.id";
  $stSug=$pdo->prepare($sqlSug);$stSug->execute(array_merge([$empresaConc],$idsExt));
  $sugestoesExt=[];
  foreach($stSug->fetchAll(PDO::FETCH_ASSOC) as $s)$sugestoesExt[(int)$s['extrato_id']]=$s;
  foreach($extratoLinhasConc as &$ex){
   $s=$sugestoesExt[(int)$ex['id']]??null;
   $ex['candidatos']=$s?(int)$s['candidatos']:0;
   $ex['credito_sugerido']=$ex['candidatos']===1?(int)$s['credito_sugerido']:null;
  }
  unset($ex);
 }
 $stDesvExt=$pdo->prepare("SELECT extrato_linha_id,liquidacao_id,valor_extrato,usuario_id,motivo,criado_em FROM conciliacao_extrato_desvinculos_auditoria WHERE empresa=:empresa ORDER BY id DESC LIMIT 20");
 $stDesvExt->execute([':empresa'=>$empresaConc]);$auditoriaDesvinculosExtratoConc=$stDesvExt->fetchAll(PDO::FETCH_ASSOC);
 $stAuditExt=$pdo->prepare("SELECT extrato_linha_id,liquidacao_id,usuario_id,valor_extrato,criado_em FROM conciliacao_extrato_vinculos_auditoria WHERE empresa=:empresa ORDER BY id DESC LIMIT 20");
 $stAuditExt->execute([':empresa'=>$empresaConc]);$auditoriaVinculosExtratoConc=$stAuditExt->fetchAll(PDO::FETCH_ASSOC);
 $stImportAud=$pdo->prepare("SELECT i.id,i.nome_arquivo,i.formato,i.usuario_id,i.criado_em,COUNT(e.id) AS linhas_novas FROM conciliacao_extrato_importacoes i LEFT JOIN conciliacao_extrato_linhas e ON e.importacao_id=i.id AND e.empresa=i.empresa WHERE i.empresa=:empresa GROUP BY i.id,i.nome_arquivo,i.formato,i.usuario_id,i.criado_em ORDER BY i.id DESC LIMIT 30");
 $stImportAud->execute([':empresa'=>$empresaConc]);$auditoriaImportacoesConc=$stImportAud->fetchAll(PDO::FETCH_ASSOC);
 $stVin=$pdo->prepare("SELECT v.id,v.receber_id,v.liquidacao_id,v.valor_vinculado,v.criado_em FROM conciliacao_vinculos_recebimentos v INNER JOIN conciliacao_liquidacoes l ON l.id=v.liquidacao_id AND l.empresa=v.empresa WHERE v.empresa=:empresa AND l.data_referencia BETWEEN :inicio AND :fim ORDER BY v.id DESC LIMIT 50");
 $stVin->execute($paramsConc);$vinculosConc=$stVin->fetchAll(PDO::FETCH_ASSOC);
 $stDesvAud=$pdo->prepare("SELECT receber_id,liquidacao_id,valor_vinculado,desvinculado_por,motivo,desvinculado_em FROM conciliacao_vinculos_auditoria WHERE empresa=:empresa ORDER BY id DESC LIMIT 30");
 $stDesvAud->execute([':empresa'=>$empresaConc]);$auditoriaDesvinculosConc=$stDesvAud->fetchAll(PDO::FETCH_ASSOC);
 $stResumoVin=$pdo->prepare("SELECT l.id,l.valor_creditado,COALESCE(SUM(v.valor_vinculado),0) AS bruto_vinculado,COUNT(v.id) AS quantidade FROM conciliacao_liquidacoes l LEFT JOIN conciliacao_vinculos_recebimentos v ON v.liquidacao_id=l.id AND v.empresa=l.empresa WHERE l.empresa=:empresa AND l.cancelado_em IS NULL AND l.data_referencia BETWEEN :inicio AND :fim GROUP BY l.id,l.valor_creditado HAVING COUNT(v.id)>0 ORDER BY l.id DESC LIMIT 50");
 $stResumoVin->execute($paramsConc);$resumoVinculosConc=$stResumoVin->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e){error_log('Extrato/vinculos indisponiveis: '.$e->getMessage());}
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
// Previsão gerencial por baixa: usa dias corridos, não inclui feriados, estornos ou antecipações.
$prazosRepasseConc=[];$prazosDisponiveisConc=false;$previsoesRepasseConc=[];
try {
 $stP=$pdo->prepare('SELECT forma_pgto,prazo_dias FROM prazos_repasse_financeiro WHERE empresa=:empresa');
 $stP->execute([':empresa'=>$empresaConc]);
 foreach($stP->fetchAll(PDO::FETCH_ASSOC) as $p) $prazosRepasseConc[(int)$p['forma_pgto']]=(int)$p['prazo_dias'];
 $prazosDisponiveisConc=true;
} catch(PDOException $e) {error_log('Prazos de repasse indisponiveis: '.$e->getMessage());}
if ($prazosDisponiveisConc) {
 $stPrev=$pdo->prepare("SELECT r.id,r.data_pgto,r.valor,fp.id AS forma_id,fp.nome AS forma, t.taxa_percentual
 FROM receber r
 INNER JOIN formas_pgto fp ON fp.id=r.forma_pgto AND fp.empresa=r.empresa
 LEFT JOIN taxas_formas_pagamento t ON t.forma_pgto=fp.id AND t.empresa=r.empresa
 WHERE r.empresa=:empresa AND r.pago='Sim' AND r.data_pgto BETWEEN :inicio AND :fim
 ORDER BY r.data_pgto DESC,r.id DESC LIMIT 100");
 $stPrev->execute($paramsConc);
 foreach($stPrev->fetchAll(PDO::FETCH_ASSOC) as $movPrev) {
  $formaPrev=(int)$movPrev['forma_id'];
  if (!array_key_exists($formaPrev,$prazosRepasseConc)) continue;
  $prazoPrev=$prazosRepasseConc[$formaPrev];
  $dataPrev=(new DateTimeImmutable($movPrev['data_pgto']))->modify('+'.$prazoPrev.' days')->format('Y-m-d');
  $taxaPrev=$movPrev['taxa_percentual'];
  $liquidoPrev=$taxaPrev===null?null:round((float)$movPrev['valor']*(1-(float)$taxaPrev/100),2);
  $previsoesRepasseConc[]=['id'=>(int)$movPrev['id'],'forma'=>$movPrev['forma'],'data_baixa'=>$movPrev['data_pgto'],'previsao'=>$dataPrev,'liquido'=>$liquidoPrev,'bruto'=>(float)$movPrev['valor'],'prazo'=>$prazoPrev];
 }
}
// Indicadores de repasse potencialmente atrasado: cálculo por forma e dia de baixa.
// Créditos são agregados por data de referência; não existe vínculo transacional.
$alertasPrazoConc=[];$totalPotencialAtrasoConc=0.0;
if ($prazosDisponiveisConc && $liquidacoesDisponiveisConc) {
 try {
  $stDias=$pdo->prepare("SELECT r.forma_pgto AS forma_id, fp.nome AS forma, r.data_pgto AS dia,
    SUM(r.valor) AS bruto, t.taxa_percentual
    FROM receber r
    INNER JOIN formas_pgto fp ON fp.id=r.forma_pgto AND fp.empresa=r.empresa
    LEFT JOIN taxas_formas_pagamento t ON t.empresa=r.empresa AND t.forma_pgto=r.forma_pgto
    WHERE r.empresa=:empresa AND r.pago='Sim' AND r.data_pgto BETWEEN :inicio AND :fim
    GROUP BY r.forma_pgto,fp.nome,r.data_pgto,t.taxa_percentual
    ORDER BY r.data_pgto DESC LIMIT 500");
  $stDias->execute($paramsConc);
  $stCreditosDia=$pdo->prepare("SELECT forma_pgto AS forma_id,data_referencia AS dia,
    SUM(valor_creditado) AS total FROM conciliacao_liquidacoes
    WHERE empresa=:empresa AND cancelado_em IS NULL AND data_referencia BETWEEN :inicio AND :fim
    GROUP BY forma_pgto,data_referencia");
  $stCreditosDia->execute($paramsConc);
  $creditoDiaConc=[];
  foreach($stCreditosDia->fetchAll(PDO::FETCH_ASSOC) as $creditoDia) {
   $creditoDiaConc[(int)$creditoDia['forma_id'].'|'.$creditoDia['dia']]=(float)$creditoDia['total'];
  }
  $hojePrazoConc=new DateTimeImmutable('today');
  foreach($stDias->fetchAll(PDO::FETCH_ASSOC) as $diaConc) {
   $idDiaConc=(int)$diaConc['forma_id'];
   if (!array_key_exists($idDiaConc,$prazosRepasseConc) || $diaConc['taxa_percentual']===null) continue;
   $prevDiaConc=(new DateTimeImmutable($diaConc['dia']))->modify('+'.$prazosRepasseConc[$idDiaConc].' days');
   if ($prevDiaConc >= $hojePrazoConc) continue;
   $liquidoDiaConc=round((float)$diaConc['bruto']*(1-(float)$diaConc['taxa_percentual']/100),2);
   $creditadoDiaConc=$creditoDiaConc[$idDiaConc.'|'.$diaConc['dia']]??0.0;
   $saldoDiaConc=round($liquidoDiaConc-$creditadoDiaConc,2);
   if ($saldoDiaConc<=0.01) continue;
   $totalPotencialAtrasoConc+=$saldoDiaConc;
   $alertasPrazoConc[]=['forma'=>$diaConc['forma'],'referencia'=>$diaConc['dia'],
    'previsao'=>$prevDiaConc->format('Y-m-d'),'saldo'=>$saldoDiaConc,
    'dias'=>(int)$prevDiaConc->diff($hojePrazoConc)->days];
  }
 } catch(PDOException $e) {error_log('Falha nos alertas de repasse: '.$e->getMessage());$alertasPrazoConc=[];}
}
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
.bc-conc-tabs{display:flex;gap:8px;overflow-x:auto;padding:6px 2px 14px;margin-bottom:10px;scrollbar-width:thin}
.bc-conc-tabs button{flex:0 0 auto;border:1px solid #d4dce7;border-radius:10px;background:#fff;color:#334155;font-weight:600;padding:11px 16px;cursor:pointer}
.bc-conc-tabs button[aria-selected="true"]{background:#1d4ed8;border-color:#1d4ed8;color:#fff}
.bc-conc-tabs button:focus-visible{outline:3px solid #93c5fd;outline-offset:2px}
.bc-conc.js-tabs-ready [data-conc-panel]{display:none}
.bc-conc.js-tabs-ready [data-conc-panel].is-active{display:block}
@media(max-width:600px){.bc-conc-card{padding:14px}.bc-conc-tabs button{padding:10px 12px}.bc-conc-filter>div{max-width:100%}}

/* Refinamento visual: indicadores, hierarquia e navegação responsiva */
.bc-conc{--bc-accent:#1d4ed8;--bc-ink:#0f172a;--bc-muted:#64748b;--bc-line:#e2e8f0}
.bc-conc>.bc-conc-card:first-of-type{background:linear-gradient(135deg,#f8fbff,#fff);border-color:#dbeafe}
.bc-conc h3{font-weight:750;letter-spacing:-.025em;color:var(--bc-ink);margin-bottom:9px}
.bc-conc h5{font-weight:700;color:var(--bc-ink);margin-bottom:12px}
.bc-conc .bc-conc-card{box-shadow:0 3px 16px rgba(15,23,42,.035);transition:box-shadow .15s ease}
.bc-conc .bc-conc-card:focus-within{box-shadow:0 4px 20px rgba(29,78,216,.09)}
.bc-conc [data-conc-panel]>.bc-conc-grid>.bc-conc-card{margin-bottom:0;min-height:106px;display:flex;flex-direction:column;justify-content:center;gap:8px;border-left:3px solid #60a5fa}
.bc-conc [data-conc-panel]>.bc-conc-grid{margin-bottom:18px}
.bc-conc [data-conc-panel]>.bc-conc-grid>.bc-conc-card strong{font-size:clamp(19px,2vw,27px);font-weight:750;line-height:1.2;color:var(--bc-ink);overflow-wrap:anywhere;font-variant-numeric:tabular-nums}
.bc-conc-tabs{position:relative;gap:6px;padding:8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:13px;margin-bottom:18px}
.bc-conc-tabs button{border:0;background:transparent;border-radius:9px;transition:background .15s,color .15s,box-shadow .15s}
.bc-conc-tabs button[aria-selected="true"]{background:#fff;color:#1d4ed8;box-shadow:0 1px 5px rgba(15,23,42,.13)}
.bc-conc-tabs button:hover:not([aria-selected="true"]){background:#e2e8f0}
.bc-conc-filter{row-gap:12px}
.bc-conc-filter input,.bc-conc-filter select{min-height:39px}
.bc-conc-table{border:1px solid #e2e8f0;border-radius:10px}
.bc-conc-table th{font-size:12px;text-transform:uppercase;letter-spacing:.035em;white-space:nowrap;color:#475569}
.bc-conc-table tbody tr:hover{background:#f8fafc}
.bc-conc-table td{font-variant-numeric:tabular-nums}
.bc-conc [data-conc-panel]::before{content:attr(aria-label);display:block;font-size:18px;font-weight:750;color:#0f172a;margin:4px 0 15px}
@media(max-width:600px){
.bc-conc{padding:10px 0}
.bc-conc-tabs{margin:0 -4px 16px}
.bc-conc-tabs button{font-size:13px}
.bc-conc-grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,155px),1fr));gap:10px}
.bc-conc [data-conc-panel]>.bc-conc-grid>.bc-conc-card{min-height:94px}
.bc-conc-filter>div{width:100%}
.bc-conc-filter input,.bc-conc-filter select{width:100%;max-width:100%}
.bc-conc-table th,.bc-conc-table td{padding:9px}
}
@media(prefers-reduced-motion:reduce){.bc-conc .bc-conc-card,.bc-conc-tabs button{transition:none}}



/* Painéis recolhíveis: melhoria progressiva, sem depender de JavaScript para exibir dados. */
.bc-conc-card.bc-conc-foldable > h5{display:flex;align-items:center;justify-content:space-between;gap:12px}
.bc-conc-fold-toggle{font:inherit;font-size:13px;font-weight:600;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;color:#334155;padding:7px 11px;cursor:pointer;white-space:nowrap}
.bc-conc-fold-toggle:focus-visible{outline:3px solid #2563eb;outline-offset:2px}
.bc-conc-fold-body[hidden]{display:none!important}

html body .bc-conc{--bc-accent:#914a29;--bc-ink:#4e2d20;--bc-muted:#795038;--bc-line:#e5d4c6}
html body .bc-conc .bc-conc-card{background:#fff!important;border-color:#e5d4c6!important;box-shadow:0 3px 12px rgba(78,45,32,.06)!important;color:#4e2d20}
html body .bc-conc [data-conc-panel]>.bc-conc-grid>.bc-conc-card{border-left:3px solid #914a29!important}
html body .bc-conc h5,html body .bc-conc h6,html body .bc-conc [data-conc-panel]::before{color:#4e2d20!important}
html body .bc-conc .bc-conc-note,html body .bc-conc .bc-conc-card small{color:#795038!important}
html body .bc-conc .bc-conc-tabs{background:#f4e9df!important;border-color:#e5d4c6!important}
html body .bc-conc .bc-conc-tabs button{color:#63351e!important}
html body .bc-conc .bc-conc-tabs button[aria-selected="true"]{background:#914a29!important;color:#fff!important}
html body .bc-conc .bc-conc-tabs button:hover:not([aria-selected="true"]){background:#ead8c9!important}
html body .bc-conc .bc-conc-tabs button:focus-visible,html body .bc-conc .bc-conc-fold-toggle:focus-visible{outline-color:#914a29!important}
html body .bc-conc input:not([type=hidden]):not([type=checkbox]):not([type=radio]),html body .bc-conc select,html body .bc-conc textarea{border:1px solid #d7c5b7!important;border-radius:9px!important;background:#fff!important;color:#4e2d20!important}
html body .bc-conc input:focus,html body .bc-conc select:focus,html body .bc-conc textarea:focus{border-color:#914a29!important;box-shadow:0 0 0 3px rgba(145,74,41,.12)!important}
html body .bc-conc .btn-primary{background:#914a29!important;border-color:#914a29!important;color:#fff!important}
html body .bc-conc .btn-outline-primary,html body .bc-conc .bc-conc-fold-toggle{background:#fff!important;border:1px solid #d7c5b7!important;color:#63351e!important}
html body .bc-conc .btn-outline-primary:hover,html body .bc-conc .bc-conc-fold-toggle:hover{background:#914a29!important;color:#fff!important}
html body .bc-conc .bc-conc-table,html body .bc-conc .bc-conc-table td,html body .bc-conc .bc-conc-table th{border-color:#eaded4!important}
html body .bc-conc .bc-conc-table th{background:#f4e9df!important;color:#63351e!important}
html body .bc-conc .bc-conc-table tbody tr:nth-child(even),html body .bc-conc .bc-conc-table tbody tr:hover{background:#fcf8f4!important}
html body .bc-conc .alert-light,html body .bc-conc .alert-info{background:#f8efe6!important;color:#63351e!important;border-color:#e5d4c6!important}
html body .bc-conc .alert-warning{background:#fff4dc!important;color:#855900!important;border-color:#ecd5a0!important}
html body .bc-conc .alert-danger{background:#fdebed!important;color:#b42332!important;border-color:#efc4ca!important}
html body .bc-conc .alert-success{background:#e8f5ed!important;color:#13745a!important;border-color:#c5e4d1!important}
html body .bc-conc input[type="number"][name^="prazos["]{text-align:center!important;text-indent:0!important;padding-inline:0!important;appearance:textfield!important;-moz-appearance:textfield!important}
html body .bc-conc input[type="number"][name^="prazos["]::-webkit-inner-spin-button,html body .bc-conc input[type="number"][name^="prazos["]::-webkit-outer-spin-button{-webkit-appearance:none!important;margin:0!important}
html body .bc-conc input[type="number"][name^="taxas["]{text-align:center!important;text-indent:0!important;padding-inline:0!important;appearance:textfield!important;-moz-appearance:textfield!important}
html body .bc-conc input[type="number"][name^="taxas["]::-webkit-inner-spin-button,html body .bc-conc input[type="number"][name^="taxas["]::-webkit-outer-spin-button{-webkit-appearance:none!important;margin:0!important}
</style>
<div class="bc-conc">
<div class="card mb-3 bc-conc-heading" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:14px"><div class="card-body d-flex align-items-center flex-wrap gap-3"><h2 class="mb-0" style="font-size:21px;font-weight:600;color:#4e2d20"><i class="fa-solid fa-check-double me-2"></i>Conciliação Financeira</h2><span style="font-size:12px;color:#795038">Conferência de contas a receber baixadas por forma de pagamento. Os valores representam baixas registradas no sistema, não depósitos bancários confirmados.</span></div></div>
<nav class="bc-conc-tabs" aria-label="Seções da conciliação financeira" id="bc-conc-tabs">
<button type="button" data-conc-tab="geral">Visão Geral</button>
<button type="button" data-conc-tab="liquidacoes">Liquidações</button>
<button type="button" data-conc-tab="repasses">Repasses</button>
<button type="button" data-conc-tab="relatorios">Relatórios</button>
<button type="button" data-conc-tab="configuracoes">Configurações</button>
</nav>
<div class="bc-conc-tab-panels" id="bc-conc-panels">
<section data-conc-panel="geral" aria-label="Visão Geral"></section>
<section data-conc-panel="liquidacoes" aria-label="Liquidações"></section>
<section data-conc-panel="repasses" aria-label="Repasses"></section>
<section data-conc-panel="relatorios" aria-label="Relatórios"></section>
<section data-conc-panel="configuracoes" aria-label="Configurações"></section>
</div>
<div class="bc-conc-card">
<h5>Consultar período</h5>
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
<p class="bc-conc-note">Informe a taxa percentual contratada para cada forma. O cálculo é apenas uma estimativa sobre recebimentos baixados; não confirma depósitos. Campos em branco não entram no total líquido estimado. As taxas podem ser salvas para uso em consultas futuras.</p>
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
<h5><i class="fa fa-calendar"></i> Prazos e previsão de repasses</h5>
<p class="bc-conc-note">Configure o prazo em dias corridos para cada forma de pagamento. A previsão é calculada a partir da data da baixa e não representa confirmação de crédito.</p>
<?php if (!$prazosDisponiveisConc): ?>
<div class="alert alert-warning">Execute o SQL prazos_repasse_financeiro.sql para habilitar este recurso.</div>
<?php else: ?>
<?php if(isset($_GET['prazos_salvos'])): ?><div class="alert alert-success">Prazos de repasse salvos.</div><?php endif; ?>
<form method="post" action="paginas/salvar_prazos_repasse.php">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<div class="bc-conc-table"><table><thead><tr><th>Forma de pagamento</th><th>Prazo (dias corridos)</th></tr></thead><tbody>
<?php
$formasPrazosConc=$pdo->prepare('SELECT id,nome FROM formas_pgto WHERE empresa=:empresa ORDER BY nome');
$formasPrazosConc->execute([':empresa'=>$empresaConc]);
foreach($formasPrazosConc->fetchAll(PDO::FETCH_ASSOC) as $formaPrazoConc): ?>
<tr><td><?php echo $escConc($formaPrazoConc['nome']); ?></td><td>
<input type="number" min="0" max="365" step="1" name="prazos[<?php echo (int)$formaPrazoConc['id']; ?>]" value="<?php echo isset($prazosRepasseConc[(int)$formaPrazoConc['id']]) ? (int)$prazosRepasseConc[(int)$formaPrazoConc['id']] : ''; ?>" placeholder="Não configurado" aria-label="Prazo de <?php echo $escConc($formaPrazoConc['nome']); ?>">
</td></tr>
<?php endforeach; ?>
</tbody></table></div>
<button class="btn btn-primary mt-3" type="submit">Salvar prazos</button>
</form>
<h6 class="mt-4">Previsões de recebimento — até 100 baixas do período</h6>
<div class="bc-conc-table"><table><thead><tr><th>Baixa</th><th>Forma</th><th>Prazo</th><th>Previsão</th><th>Bruto</th><th>Líquido estimado</th></tr></thead><tbody>
<?php foreach($previsoesRepasseConc as $prevConc): ?>
<tr><td>#<?php echo $prevConc['id']; ?> — <?php echo $escConc(date('d/m/Y',strtotime($prevConc['data_baixa']))); ?></td>
<td><?php echo $escConc($prevConc['forma']); ?></td><td><?php echo (int)$prevConc['prazo']; ?> dias</td>
<td><?php echo $escConc(date('d/m/Y',strtotime($prevConc['previsao']))); ?></td>
<td><?php echo $moedaConc($prevConc['bruto']); ?></td>
<td><?php echo $prevConc['liquido']===null?'Taxa não configurada':$moedaConc($prevConc['liquido']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$previsoesRepasseConc): ?><tr><td colspan="6">Sem baixas com prazo configurado neste período.</td></tr><?php endif; ?>
</tbody></table></div>
<p class="bc-conc-note">Datas previstas não são vinculadas automaticamente aos créditos registrados. A conferência continua agrupada por forma e data de referência.</p>
<?php endif; ?>
</div>
<div class="bc-conc-card">
<h5><i class="fa fa-download"></i> Exportar créditos informados</h5>
<p class="bc-conc-note">Baixe um CSV com todas as liquidações cadastradas no período selecionado (data de referência), incluindo forma, valor, data do crédito e identificador. O arquivo não é um extrato bancário.</p>
<a class="btn btn-outline-primary" href="paginas/exportar_liquidacoes_conciliacao.php?inicio=<?php echo urlencode($inicioConc); ?>&amp;fim=<?php echo urlencode($fimConc); ?>"><i class="fa fa-file-excel-o"></i> Exportar liquidações (CSV)</a>
</div>
<div class="bc-conc-card">
<h5><i class="fa fa-file-excel-o"></i> Exportar extrato e situação dos vínculos</h5>
<p class="bc-conc-note">CSV de todas as movimentações importadas no período, sem limite de paginação. Inclui crédito vinculado, divergências e arquivo de origem. Um vínculo não representa aprovação financeira.</p>
<a class="btn btn-outline-primary" href="paginas/exportar_extrato_conciliacao.php?inicio=<?php echo urlencode($inicioConc); ?>&amp;fim=<?php echo urlencode($fimConc); ?>&amp;situacao=todos">Extrato completo (CSV)</a>
<a class="btn btn-outline-secondary" href="paginas/exportar_extrato_conciliacao.php?inicio=<?php echo urlencode($inicioConc); ?>&amp;fim=<?php echo urlencode($fimConc); ?>&amp;situacao=pendentes">Somente sem vínculo (CSV)</a>
<a class="btn btn-outline-secondary" href="paginas/exportar_extrato_conciliacao.php?inicio=<?php echo urlencode($inicioConc); ?>&amp;fim=<?php echo urlencode($fimConc); ?>&amp;situacao=vinculados">Somente vinculados (CSV)</a>
</div>
<div class="bc-conc-card">
<h5><i class="fa fa-clock-o"></i> Repasses potencialmente atrasados</h5>
<p class="bc-conc-note">Verificação por forma e dia de referência, usando prazos em dias corridos. Só considera datas previstas anteriores a hoje, taxas cadastradas e créditos informados. Não comprova atraso contratual nem identifica depósitos não cadastrados.</p>
<?php if (!$prazosDisponiveisConc || !$liquidacoesDisponiveisConc): ?>
<div class="alert alert-warning">Configure a tabela de prazos e as liquidações para consultar possíveis atrasos.</div>
<?php else: ?>
<div class="bc-conc-grid">
<div class="bc-conc-card"><small>Saldo potencialmente atrasado no período</small><strong><?php echo $moedaConc($totalPotencialAtrasoConc); ?></strong></div>
<div class="bc-conc-card"><small>Dias e formas com divergência</small><strong><?php echo count($alertasPrazoConc); ?></strong></div>
</div>
<div class="bc-conc-table"><table><thead><tr><th>Forma</th><th>Referência</th><th>Previsão</th><th>Dias após previsão</th><th>Saldo a conferir</th></tr></thead><tbody>
<?php foreach($alertasPrazoConc as $alertaPrazoConc): ?>
<tr><td><?php echo $escConc($alertaPrazoConc['forma']); ?></td>
<td><?php echo $escConc(date('d/m/Y',strtotime($alertaPrazoConc['referencia']))); ?></td>
<td><?php echo $escConc(date('d/m/Y',strtotime($alertaPrazoConc['previsao']))); ?></td>
<td><?php echo (int)$alertaPrazoConc['dias']; ?></td>
<td><?php echo $moedaConc($alertaPrazoConc['saldo']); ?></td></tr>
<?php endforeach; ?>
<?php if (!$alertasPrazoConc): ?><tr><td colspan="5">Nenhum possível atraso encontrado entre os registros analisados.</td></tr><?php endif; ?>
</tbody></table></div>
<p class="bc-conc-note">A consulta examina até 500 combinações de forma e dia no período. Se houver mais registros, reduza o intervalo. Crédito parcial ou antecipado pode exigir conferência manual.</p>
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
<?php if(isset($_GET['liquidacao_cancelada'])): ?><div class="alert alert-success">Crédito cancelado e preservado no histórico.</div><?php endif; ?>
<?php if(isset($_GET['liquidacao_duplicada'])): ?><div class="alert alert-warning" role="alert"><strong>Possível crédito duplicado.</strong> Já existe um lançamento com o mesmo identificador para esta forma de pagamento, ou com a mesma forma, datas e valor. Nenhum novo crédito foi registrado. Confira o histórico antes de tentar novamente.</div><?php endif; ?>
<?php if(isset($_GET['extrato_importado'])): ?><div class="alert alert-info"><?php echo (int)$_GET['extrato_importado']; ?> movimentação(ões) nova(s) importada(s) para conferência. Nenhum crédito foi criado automaticamente.</div><?php endif; ?>
<?php if(isset($_GET['vinculo_salvo'])): ?><div class="alert alert-success">Recebimento vinculado ao crédito para conferência manual. O vínculo não confirma igualdade de valores líquidos.</div><?php endif; ?>
<?php if(($_GET['extrato_vinculado']??'')==='1'): ?><div class="alert alert-success" role="status">Movimentação bancária vinculada ao crédito. Confira o histórico e os valores; o vínculo não equivale à aprovação da conciliação.</div><?php endif; ?>
<?php if(($_GET['extrato_desvinculado']??'')==='1'): ?><div class="alert alert-warning" role="status">Vínculo bancário desfeito. A justificativa foi registrada no histórico de auditoria; a movimentação e o crédito foram preservados.</div><?php endif; ?>
<div class="bc-conc-card">
<h5>Indicadores de vínculos bancários</h5>
<p class="bc-conc-note">Período selecionado: <?php echo $escConc($inicioConc); ?> a <?php echo $escConc($fimConc); ?>. Os indicadores são de vínculos entre extrato e créditos, não de conciliação contábil aprovada. Movimentações negativas entram na contagem, mas não nos totais de créditos.</p>
<div class="bc-conc-table"><table><thead><tr><th>Movimentações</th><th>Vinculadas</th><th>Sem vínculo</th><th>Valor positivo vinculado</th><th>Valor positivo pendente</th><th>Divergências em vínculos</th><th>Taxa de vínculo</th></tr></thead><tbody>
<tr><td><?php echo (int)$indicadoresExtratoConc['total']; ?></td><td><?php echo (int)$indicadoresExtratoConc['vinculados']; ?></td><td><?php echo (int)$indicadoresExtratoConc['pendentes']; ?></td>
<td><?php echo $moedaConc($indicadoresExtratoConc['valor_vinculado']); ?></td><td><?php echo $moedaConc($indicadoresExtratoConc['valor_pendente']); ?></td>
<td><?php echo (int)$indicadoresExtratoConc['divergentes']; ?></td><td><?php echo (int)$indicadoresExtratoConc['total']>0?number_format(100*(int)$indicadoresExtratoConc['vinculados']/(int)$indicadoresExtratoConc['total'],1,',','.').'%':'0,0%'; ?></td></tr>
</tbody></table></div>
<p class="bc-conc-note">Divergência: crédito cancelado, inexistente ou com valor diferente. A ausência de divergência não significa que o vínculo foi conferido ou aprovado.</p>
</div>
<div class="bc-conc-card">
<h5>Alertas de conferência bancária</h5>
<p class="bc-conc-note">Avisos informativos calculados a partir do extrato importado no período. Não alteram valores, vínculos ou situação de conciliação.</p>
<?php if((int)$indicadoresExtratoConc['total']===0): ?>
<div class="alert alert-info">Nenhuma movimentação importada no período. Importe um extrato para visualizar os alertas.</div>
<?php else: ?>
<?php if((int)($alertasExtratoConc['creditos_antigos']??0)>0): ?><div class="alert alert-warning"><strong>Atenção:</strong> <?php echo (int)$alertasExtratoConc['creditos_antigos']; ?> crédito(s) bancário(s) positivo(s) sem vínculo há mais de 3 dias. Confira os lançamentos.</div><?php endif; ?>
<?php if((int)($alertasExtratoConc['creditos_recentes']??0)>0): ?><div class="alert alert-info"><?php echo (int)$alertasExtratoConc['creditos_recentes']; ?> crédito(s) positivo(s) recente(s) ainda sem vínculo.</div><?php endif; ?>
<?php if((int)$indicadoresExtratoConc['divergentes']>0): ?><div class="alert alert-danger"><strong>Divergência:</strong> <?php echo (int)$indicadoresExtratoConc['divergentes']; ?> vínculo(s) com valor diferente, crédito cancelado ou inexistente. Exige revisão manual.</div><?php endif; ?>
<?php if((int)($alertasExtratoConc['debitos_conferencia']??0)>0): ?><div class="alert alert-secondary"><?php echo (int)$alertasExtratoConc['debitos_conferencia']; ?> débito(s) bancário(s) sem vínculo, disponíveis para conferência manual.</div><?php endif; ?>
<?php if((int)($alertasExtratoConc['creditos_antigos']??0)===0&&(int)($alertasExtratoConc['creditos_recentes']??0)===0&&(int)$indicadoresExtratoConc['divergentes']===0&&(int)($alertasExtratoConc['debitos_conferencia']??0)===0): ?><div class="alert alert-success">Nenhuma pendência identificada pelas regras automáticas neste período. Isso não substitui a conferência financeira.</div><?php endif; ?>
<?php endif; ?>
</div>
<div class="bc-conc-card">
<h5>Importar extrato bancário (CSV / OFX)</h5>
<p class="bc-conc-note">Importação em área separada, sem alterar caixa, recebimentos ou créditos. CSV: colunas data;valor;descricao;identificador (opcional), com cabeçalho. OFX: movimentações STMTTRN. Até 2 MB e 5.000 linhas por arquivo. Confira as linhas antes de conciliar.</p>
<form method="post" action="paginas/importar_extrato_conciliacao.php" enctype="multipart/form-data">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<input type="file" name="arquivo" accept=".csv,.ofx" required>
<button type="submit" class="btn btn-primary">Importar para conferência</button>
</form>
<form class="bc-conc-filter mt-3" method="get" action="index.php">
<input type="hidden" name="pagina" value="conciliacao_financeira"><input type="hidden" name="aba" value="liquidacoes">
<input type="hidden" name="inicio" value="<?php echo $escConc($inicioConc); ?>"><input type="hidden" name="fim" value="<?php echo $escConc($fimConc); ?>">
<div><label for="extrato-busca">Identificador ou descrição</label><input id="extrato-busca" class="form-control" name="extrato_busca" maxlength="120" value="<?php echo $escConc($buscaExtratoConc); ?>"></div>
<div><label for="extrato-situacao">Situação do extrato</label><select id="extrato-situacao" class="form-control" name="extrato_situacao">
<option value="todos" <?php echo $situacaoExtratoConc==='todos'?'selected':''; ?>>Todas</option>
<option value="pendentes" <?php echo $situacaoExtratoConc==='pendentes'?'selected':''; ?>>Sem vínculo</option>
<option value="vinculados" <?php echo $situacaoExtratoConc==='vinculados'?'selected':''; ?>>Com vínculo</option>
</select></div><button type="submit" class="btn btn-primary">Filtrar extrato</button>
</form>
<p class="bc-conc-note mt-3">Sugestões de conferência: o sistema procura créditos ativos com o mesmo valor e data de crédito até 3 dias antes ou depois da movimentação. Um único candidato é apenas uma possível correspondência, não uma conciliação confirmada.</p>
<div class="bc-conc-table mt-3"><table><thead><tr><th>Data</th><th>Valor</th><th>Identificador</th><th>Descrição</th><th>Arquivo</th><th>Conferência sugerida</th></tr></thead><tbody>
<?php foreach($extratoLinhasConc as $ex): ?><tr><td><?php echo $escConc($ex['data_movimento']); ?></td><td><?php echo $moedaConc($ex['valor']); ?></td><td><?php echo $escConc($ex['identificador']); ?></td><td><?php echo $escConc($ex['descricao']); ?></td><td><?php echo $escConc($ex['nome_arquivo']); ?></td><td>
<?php if($ex['liquidacao_id']!==null): ?>Vinculado ao crédito #<?php echo (int)$ex['liquidacao_id']; ?>
<details class="mt-2"><summary class="btn btn-sm btn-outline-danger">Desvincular</summary>
<form method="post" action="paginas/desvincular_extrato_conciliacao.php">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<input type="hidden" name="extrato_id" value="<?php echo (int)$ex['id']; ?>">
<label>Justificativa (10 a 500 caracteres)</label><textarea class="form-control" name="motivo" minlength="10" maxlength="500" required></textarea>
<button type="submit" class="btn btn-sm btn-outline-danger mt-2">Confirmar desvínculo</button></form></details>
<?php elseif((float)$ex['valor']<=0): ?>Débito — conferência manual
<?php elseif((int)($ex['candidatos']??0)===1): ?>Possível crédito #<?php echo (int)$ex['credito_sugerido']; ?> — conferir
<?php elseif((int)($ex['candidatos']??0)>1): ?><?php echo (int)$ex['candidatos']; ?> créditos possíveis — conferir manualmente
<?php else: ?>Sem correspondência por valor/data
<?php endif; ?>
<?php if($ex['liquidacao_id']===null && (float)$ex['valor']>0): ?>
<details class="mt-2"><summary class="btn btn-sm btn-outline-primary">Vincular crédito</summary>
<form method="post" action="paginas/vincular_extrato_conciliacao.php">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<input type="hidden" name="extrato_id" value="<?php echo (int)$ex['id']; ?>">
<label>Crédito (ID)</label><input type="number" min="1" class="form-control" name="liquidacao_id" value="<?php echo (int)($ex['credito_sugerido']??0)?:''; ?>" required>
<p class="bc-conc-note">Confirme o valor antes de vincular. Somente valores idênticos são aceitos.</p>
<button class="btn btn-sm btn-primary" type="submit">Confirmar vínculo</button></form></details>
<?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$extratoLinhasConc): ?><tr><td colspan="6">Nenhuma movimentação encontrada para os filtros.</td></tr><?php endif; ?>
</tbody></table></div>
<?php $queryExtratoConc=['pagina'=>'conciliacao_financeira','aba'=>'liquidacoes','inicio'=>$inicioConc,'fim'=>$fimConc,'extrato_busca'=>$buscaExtratoConc,'extrato_situacao'=>$situacaoExtratoConc]; ?>
<div class="bc-conc-filter mt-3" role="navigation" aria-label="Paginação do extrato">
<span class="bc-conc-note">Página <?php echo $paginaExtratoConc; ?> de <?php echo $paginasExtratoConc; ?> — <?php echo $totalExtratoConc; ?> movimentação(ões).</span>
<?php if($paginaExtratoConc>1): $queryExtratoConc['extrato_pagina']=$paginaExtratoConc-1; ?><a class="btn btn-outline-primary btn-sm" href="index.php?<?php echo $escConc(http_build_query($queryExtratoConc)); ?>">Anterior</a><?php endif; ?>
<?php if($paginaExtratoConc<$paginasExtratoConc): $queryExtratoConc['extrato_pagina']=$paginaExtratoConc+1; ?><a class="btn btn-outline-primary btn-sm" href="index.php?<?php echo $escConc(http_build_query($queryExtratoConc)); ?>">Próxima</a><?php endif; ?>
</div>
</div>
<div class="bc-conc-card">
<h5>Histórico de desvínculos bancários</h5>
<p class="bc-conc-note">Últimas 20 correções, preservadas para auditoria.</p>
<div class="bc-conc-table"><table><thead><tr><th>Movimentação</th><th>Crédito</th><th>Valor</th><th>Usuário</th><th>Data</th><th>Motivo</th></tr></thead><tbody>
<?php foreach($auditoriaDesvinculosExtratoConc as $a): ?><tr><td>#<?php echo (int)$a['extrato_linha_id']; ?></td><td>#<?php echo (int)$a['liquidacao_id']; ?></td><td><?php echo $moedaConc($a['valor_extrato']); ?></td><td>#<?php echo (int)$a['usuario_id']; ?></td><td><?php echo $escConc($a['criado_em']); ?></td><td><?php echo $escConc($a['motivo']); ?></td></tr><?php endforeach; ?>
<?php if(!$auditoriaDesvinculosExtratoConc): ?><tr><td colspan="6">Nenhum desvínculo bancário registrado.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="bc-conc-card">
<h5>Auditoria dos vínculos do extrato</h5>
<p class="bc-conc-note">Últimos 20 vínculos confirmados manualmente. Não movimentam o caixa.</p>
<div class="bc-conc-table"><table><thead><tr><th>Movimentação</th><th>Crédito</th><th>Valor</th><th>Usuário</th><th>Data</th></tr></thead><tbody>
<?php foreach($auditoriaVinculosExtratoConc as $a): ?><tr><td>#<?php echo (int)$a['extrato_linha_id']; ?></td><td>#<?php echo (int)$a['liquidacao_id']; ?></td><td><?php echo $moedaConc($a['valor_extrato']); ?></td><td>#<?php echo (int)$a['usuario_id']; ?></td><td><?php echo $escConc($a['criado_em']); ?></td></tr><?php endforeach; ?>
<?php if(!$auditoriaVinculosExtratoConc): ?><tr><td colspan="5">Nenhum vínculo de extrato registrado.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="bc-conc-card">
<h5>Auditoria de importações bancárias</h5>
<p class="bc-conc-note">Últimas 30 importações da empresa, com usuário, horário e quantidade de linhas novas efetivamente armazenadas. Arquivos duplicados não geram nova importação.</p>
<div class="bc-conc-table"><table><thead><tr><th>ID</th><th>Arquivo</th><th>Formato</th><th>Usuário</th><th>Importado em</th><th>Linhas novas</th></tr></thead><tbody>
<?php foreach($auditoriaImportacoesConc as $imp): ?><tr>
<td>#<?php echo (int)$imp['id']; ?></td><td><?php echo $escConc($imp['nome_arquivo']); ?></td>
<td><?php echo $escConc(strtoupper($imp['formato'])); ?></td><td>#<?php echo (int)$imp['usuario_id']; ?></td>
<td><?php echo $escConc($imp['criado_em']); ?></td><td><?php echo (int)$imp['linhas_novas']; ?></td>
</tr><?php endforeach; ?>
<?php if(!$auditoriaImportacoesConc): ?><tr><td colspan="6">Nenhuma importação registrada.</td></tr><?php endif; ?>
</tbody></table></div>
</div>
<div class="bc-conc-card">
<h5>Vincular recebimento a crédito</h5>
<p class="bc-conc-note">Informe IDs existentes. O sistema valida a mesma empresa, forma de pagamento, recebimento pago e crédito ativo. Um recebimento só pode ser vinculado uma vez. O vínculo não constitui confirmação automática do valor líquido.</p>
<form method="post" action="paginas/vincular_recebimento_conciliacao.php" class="bc-conc-filter">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<div><label for="vinc-receber">ID do recebimento</label><input id="vinc-receber" type="number" min="1" name="receber_id" required></div>
<div><label for="vinc-credito">ID do crédito</label><input id="vinc-credito" type="number" min="1" name="liquidacao_id" required></div>
<button class="btn btn-primary" type="submit">Vincular para conferência</button>
</form>
<div class="bc-conc-table mt-3"><table><thead><tr><th>Recebimento</th><th>Crédito</th><th>Valor bruto</th><th>Vinculado em</th><th>Correção</th></tr></thead><tbody>
<?php foreach($vinculosConc as $v): ?><tr><td>#<?php echo (int)$v['receber_id']; ?></td><td>#<?php echo (int)$v['liquidacao_id']; ?></td><td><?php echo $moedaConc($v['valor_vinculado']); ?></td><td><?php echo $escConc($v['criado_em']); ?></td><td>
<details><summary class="btn btn-sm btn-outline-danger">Desvincular</summary>
<form method="post" action="paginas/desvincular_recebimento_conciliacao.php">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<input type="hidden" name="vinculo_id" value="<?php echo (int)$v['id']; ?>">
<label>Justificativa (10 a 500 caracteres)</label><textarea class="form-control" name="motivo" minlength="10" maxlength="500" required></textarea>
<button type="submit" class="btn btn-sm btn-outline-danger mt-2">Confirmar desvínculo</button></form></details></td></tr><?php endforeach; ?>
<?php if(!$vinculosConc): ?><tr><td colspan="5">Nenhum vínculo registrado no período.</td></tr><?php endif; ?>
</tbody></table></div>
<h6 class="mt-4">Histórico de desvínculos</h6>
<p class="bc-conc-note">Operações desfeitas permanecem registradas com motivo, responsável e data. O recebimento volta a ficar disponível para vínculo.</p>
<div class="bc-conc-table"><table><thead><tr><th>Recebimento</th><th>Crédito</th><th>Valor</th><th>Responsável</th><th>Data</th><th>Motivo</th></tr></thead><tbody>
<?php foreach($auditoriaDesvinculosConc as $a): ?><tr><td>#<?php echo (int)$a['receber_id']; ?></td><td>#<?php echo (int)$a['liquidacao_id']; ?></td><td><?php echo $moedaConc($a['valor_vinculado']); ?></td><td>#<?php echo (int)$a['desvinculado_por']; ?></td><td><?php echo $escConc($a['desvinculado_em']); ?></td><td><?php echo $escConc($a['motivo']); ?></td></tr><?php endforeach; ?>
<?php if(!$auditoriaDesvinculosConc): ?><tr><td colspan="6">Nenhum desvínculo registrado.</td></tr><?php endif; ?>
</tbody></table></div>
<h6 class="mt-4">Conferência de valores vinculados</h6>
<p class="bc-conc-note">O total vinculado corresponde ao valor bruto dos recebimentos. A diferença abaixo é apenas indicativa: taxas e repasses agrupados podem explicar divergências. Nenhum crédito é marcado automaticamente como conciliado.</p>
<div class="bc-conc-table"><table><thead><tr><th>Crédito</th><th>Valor creditado</th><th>Bruto vinculado</th><th>Diferença indicativa</th><th>Recebimentos</th></tr></thead><tbody>
<?php foreach($resumoVinculosConc as $v): ?><tr><td>#<?php echo (int)$v['id']; ?></td><td><?php echo $moedaConc($v['valor_creditado']); ?></td><td><?php echo $moedaConc($v['bruto_vinculado']); ?></td><td><?php echo $moedaConc((float)$v['valor_creditado']-(float)$v['bruto_vinculado']); ?></td><td><?php echo (int)$v['quantidade']; ?></td></tr><?php endforeach; ?>
<?php if(!$resumoVinculosConc): ?><tr><td colspan="5">Nenhum crédito com recebimentos vinculados no período.</td></tr><?php endif; ?>
</tbody></table></div>
</div>
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
<div class="bc-conc-card" data-conc-audit="cancelamentos">
<h5><i class="fa fa-history"></i> Histórico de cancelamentos</h5>
<p class="bc-conc-note">Consulta por data efetiva do cancelamento e ID do usuário. Diferente do filtro principal, que utiliza a data de referência do crédito. Exibe 25 registros por página, com navegação pelo histórico completo do período.</p>
<form method="get" action="index.php" class="bc-conc-filter">
<input type="hidden" name="pagina" value="conciliacao_financeira">
<input type="hidden" name="aba" value="liquidacoes">
<input type="hidden" name="inicio" value="<?php echo $escConc($inicioConc); ?>">
<input type="hidden" name="fim" value="<?php echo $escConc($fimConc); ?>">
<div><label for="aud-cancel-inicio">Cancelados de</label><input id="aud-cancel-inicio" type="date" name="cancel_inicio" value="<?php echo $escConc($audInicioConc); ?>" required></div>
<div><label for="aud-cancel-fim">Até</label><input id="aud-cancel-fim" type="date" name="cancel_fim" value="<?php echo $escConc($audFimConc); ?>" required></div>
<div><label for="aud-cancel-usuario">ID do usuário (opcional)</label><input id="aud-cancel-usuario" type="number" min="1" step="1" name="cancel_usuario" value="<?php echo $escConc($audUsuarioConc); ?>" placeholder="Todos"></div>
<button class="btn btn-primary" type="submit">Consultar cancelamentos</button>
</form>
<div class="bc-conc-table mt-3"><table><thead><tr><th>Cancelado em</th><th>Crédito</th><th>Forma</th><th>Valor</th><th>Usuário</th><th>Justificativa</th></tr></thead><tbody>
<?php foreach($auditoriaCancelamentosConc as $audCancelConc): ?><tr>
<td><?php echo $escConc($audCancelConc['cancelado_em']); ?></td>
<td>#<?php echo (int)$audCancelConc['id']; ?> — <?php echo $escConc($audCancelConc['data_referencia']); ?></td>
<td><?php echo $escConc($audCancelConc['forma']); ?></td>
<td><?php echo $moedaConc($audCancelConc['valor_creditado']); ?></td>
<td>#<?php echo (int)$audCancelConc['cancelado_por']; ?></td>
<td><?php echo $escConc($audCancelConc['motivo_cancelamento']); ?></td>
</tr><?php endforeach; ?>
<?php if(!$auditoriaCancelamentosConc): ?><tr><td colspan="6">Nenhum cancelamento encontrado para os filtros.</td></tr><?php endif; ?>
</tbody></table></div>
<?php
$queryAudConc=['pagina'=>'conciliacao_financeira','aba'=>'liquidacoes','inicio'=>$inicioConc,'fim'=>$fimConc,'cancel_inicio'=>$audInicioConc,'cancel_fim'=>$audFimConc];
if($audUsuarioConc!=='')$queryAudConc['cancel_usuario']=$audUsuarioConc;
?>
<div class="bc-conc-filter mt-3" role="navigation" aria-label="Paginação de cancelamentos">
<span class="bc-conc-note">Página <?php echo $paginaAudConc; ?> de <?php echo $paginasAudConc; ?> — <?php echo $totalCancelamentosAudConc; ?> cancelamento(s) encontrado(s).</span>
<?php if($paginaAudConc>1): $queryAudConc['cancel_pagina']=$paginaAudConc-1; ?>
<a class="btn btn-outline-primary btn-sm" href="index.php?<?php echo $escConc(http_build_query($queryAudConc)); ?>">Anterior</a>
<?php endif; ?>
<?php if($paginaAudConc<$paginasAudConc): $queryAudConc['cancel_pagina']=$paginaAudConc+1; ?>
<a class="btn btn-outline-primary btn-sm" href="index.php?<?php echo $escConc(http_build_query($queryAudConc)); ?>">Próxima</a>
<?php endif; ?>
</div>
</div>
<h6 class="mt-4">Auditoria de créditos e cancelamentos</h6>
<div class="bc-conc-grid">
<div class="bc-conc-card"><small>Créditos ativos</small><strong><?php echo (int)$resumoAuditoriaConc['ativos']; ?></strong><span class="bc-conc-note"><?php echo $moedaConc($resumoAuditoriaConc['valor_ativo']); ?></span></div>
<div class="bc-conc-card"><small>Créditos cancelados</small><strong><?php echo (int)$resumoAuditoriaConc['cancelados']; ?></strong><span class="bc-conc-note"><?php echo $moedaConc($resumoAuditoriaConc['valor_cancelado']); ?> fora dos saldos conciliados</span></div>
</div>
<form method="get" action="index.php" class="bc-conc-filter mb-3">
<input type="hidden" name="pagina" value="conciliacao_financeira">
<input type="hidden" name="aba" value="liquidacoes">
<input type="hidden" name="inicio" value="<?php echo $escConc($inicioConc); ?>">
<input type="hidden" name="fim" value="<?php echo $escConc($fimConc); ?>">
<div><label for="conc-liq-busca">NSU / Identificador / ID</label><input id="conc-liq-busca" name="liq_busca" class="form-control" maxlength="120" value="<?php echo $escConc($buscaLiqConc); ?>" placeholder="Pesquisar crédito"></div>
<div><label for="conc-historico">Exibir histórico</label><select id="conc-historico" name="historico" class="form-control">
<option value="todos" <?php echo $filtroHistoricoConc==='todos'?'selected':''; ?>>Todos</option>
<option value="ativos" <?php echo $filtroHistoricoConc==='ativos'?'selected':''; ?>>Somente ativos</option>
<option value="cancelados" <?php echo $filtroHistoricoConc==='cancelados'?'selected':''; ?>>Somente cancelados</option>
</select></div><button class="btn btn-primary" type="submit">Filtrar histórico</button>
</form>
<h6 class="mt-4">Histórico de liquidações</h6><p class="bc-conc-note">25 registros por página. Filtro aplicado em todo o período selecionado; o CSV exporta o período completo.</p>
<div class="bc-conc-table"><table><thead><tr><th>Referência</th><th>Crédito</th><th>Forma</th><th>Valor</th><th>Identificador</th><th>Situação / Ação</th></tr></thead><tbody>
<?php foreach($liquidacoesConc as $l): ?><tr>
<td><?php echo $escConc($l['data_referencia']); ?></td><td><?php echo $escConc($l['data_credito']); ?></td><td><?php echo $escConc($l['forma']); ?></td><td><?php echo $moedaConc($l['valor_creditado']); ?></td><td><?php echo $escConc($l['identificador']??''); ?></td>
<td><?php if(!empty($l['cancelado_em'])): ?>
<span class="text-danger">Cancelado em <?php echo $escConc($l['cancelado_em']); ?></span>
<div class="bc-conc-note">Motivo: <?php echo $escConc($l['motivo_cancelamento']??''); ?> | Usuário #<?php echo (int)$l['cancelado_por']; ?></div>
<?php elseif((int)($l['total_vinculos']??0)>0): ?>
<span class="bc-conc-note">Crédito com <?php echo (int)$l['total_vinculos']; ?> recebimento(s) vinculado(s). Revise os vínculos antes de cancelar.</span>
<?php else: ?>
<details><summary class="btn btn-sm btn-outline-danger">Cancelar</summary>
<form method="post" action="paginas/cancelar_liquidacao_conciliacao.php" onsubmit="return confirm('Confirma o cancelamento deste crédito?');">
<input type="hidden" name="csrf_conc" value="<?php echo $escConc($_SESSION['csrf_conc']); ?>">
<input type="hidden" name="id" value="<?php echo (int)$l['id']; ?>">
<label for="motivo-conc-<?php echo (int)$l['id']; ?>">Justificativa (mín. 10 caracteres)</label>
<textarea id="motivo-conc-<?php echo (int)$l['id']; ?>" name="motivo" minlength="10" maxlength="500" required class="form-control" rows="2"></textarea>
<button type="submit" class="btn btn-danger btn-sm mt-2">Confirmar cancelamento</button>
</form></details>
<?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$liquidacoesConc): ?><tr><td colspan="6">Nenhum crédito encontrado para o filtro.</td></tr><?php endif; ?>
</tbody></table></div>
<?php $queryLiqConc=['pagina'=>'conciliacao_financeira','aba'=>'liquidacoes','inicio'=>$inicioConc,'fim'=>$fimConc,'historico'=>$filtroHistoricoConc,'liq_busca'=>$buscaLiqConc]; ?>
<div class="bc-conc-filter mt-3" role="navigation" aria-label="Paginação do histórico de liquidações">
<span class="bc-conc-note">Página <?php echo $paginaLiqConc; ?> de <?php echo $paginasLiqConc; ?> — <?php echo $totalLiqConc; ?> crédito(s).</span>
<?php if($paginaLiqConc>1): $queryLiqConc['liq_pagina']=$paginaLiqConc-1; ?>
<a class="btn btn-outline-primary btn-sm" href="index.php?<?php echo $escConc(http_build_query($queryLiqConc)); ?>">Anterior</a>
<?php endif; ?>
<?php if($paginaLiqConc<$paginasLiqConc): $queryLiqConc['liq_pagina']=$paginaLiqConc+1; ?>
<a class="btn btn-outline-primary btn-sm" href="index.php?<?php echo $escConc(http_build_query($queryLiqConc)); ?>">Próxima</a>
<?php endif; ?>
</div>
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
<p class="bc-conc-note">A simulação de taxas acima não substitui a conciliação bancária. A importação automática de extratos e a conciliação bancária transacional ainda não estão disponíveis.</p>
</div></div>
<script>
(function(){
 const root=document.querySelector('.bc-conc');if(!root)return;
 const targets=new Set([
  'Importar extrato bancário (CSV / OFX)',
  'Histórico de desvínculos bancários',
  'Auditoria dos vínculos do extrato',
  'Auditoria de importações bancárias'
 ]);
 let seq=0;
 root.querySelectorAll('.bc-conc-card').forEach(card=>{
  const heading=Array.from(card.children).find(el=>el.tagName==='H5');
  if(!heading||!targets.has(heading.textContent.trim()))return;
  const body=document.createElement('div');body.className='bc-conc-fold-body';
  while(heading.nextSibling)body.appendChild(heading.nextSibling);
  card.appendChild(body);card.classList.add('bc-conc-foldable');
  const btn=document.createElement('button');btn.type='button';btn.className='bc-conc-fold-toggle';
  btn.setAttribute('aria-controls','bc-conc-fold-'+(++seq));body.id='bc-conc-fold-'+seq;
  function toggle(open){body.hidden=!open;btn.setAttribute('aria-expanded',String(open));btn.textContent=open?'Recolher':'Mostrar detalhes';}
  btn.addEventListener('click',()=>toggle(body.hidden));heading.appendChild(btn);toggle(false);
 });
})();
</script>
<script>
(function(){
 const root=document.querySelector('.bc-conc');
 if(!root)return;
 const nav=root.querySelector('#bc-conc-tabs');
 const panels=root.querySelector('#bc-conc-panels');
 if(!nav||!panels)return;
 const panel=(name)=>panels.querySelector('[data-conc-panel="'+name+'"]');
 const tabs=['geral','liquidacoes','repasses','relatorios','configuracoes'];
 const classify=(heading)=>{
  const t=(heading||'').toLocaleLowerCase('pt-BR');
  if(t.includes('simulador de taxas'))return 'configuracoes';
  if(t.includes('prazos e previsão'))return 'repasses';
  if(t.includes('repasses potencialmente'))return 'repasses';
  if(t.includes('acompanhamento de liquidações'))return 'liquidacoes';
  if(t.includes('liquidações informadas'))return 'liquidacoes';
  if(t.includes('exportar créditos')||t.includes('últimos 100 recebimentos'))return 'relatorios';
  return 'geral';
 };
 // Mover apenas blocos diretos: formulários e listeners permanecem intactos.
 Array.from(root.children).forEach(el=>{
  if(el===nav||el===panels||el.tagName==='SCRIPT')return;
  if(el.classList.contains('bc-conc-grid')){panel('geral').appendChild(el);return;}
  if(el.classList.contains('bc-conc-card')){
   const h=el.querySelector('h3,h5');
   if(h&&(h.tagName==='H3'||h.textContent.trim()==='Consultar período')){root.insertBefore(el,nav);return;}
   panel(classify(h&&h.textContent)).appendChild(el);
   return;
  }
  // Avisos globais ficam visíveis acima das abas.
  if(el.classList.contains('alert'))root.insertBefore(el,nav);
 });
 function activate(name,update){
  if(!tabs.includes(name))name='geral';
  nav.querySelectorAll('[data-conc-tab]').forEach(b=>{
   const on=b.getAttribute('data-conc-tab')===name;
   b.setAttribute('aria-selected',on?'true':'false');
   b.setAttribute('aria-controls','bc-conc-panel-'+b.getAttribute('data-conc-tab'));
  });
  tabs.forEach(t=>{const el=panel(t);el.id='bc-conc-panel-'+t;el.classList.toggle('is-active',t===name);});
  if(update){const u=new URL(window.location.href);u.searchParams.set('aba',name);history.replaceState(null,'',u.toString());}
 }
 nav.querySelectorAll('button[data-conc-tab]').forEach(b=>b.addEventListener('click',()=>activate(b.getAttribute('data-conc-tab'),true)));
 root.classList.add('js-tabs-ready');
 const requested=new URLSearchParams(window.location.search).get('aba');
 const initial=requested||((new URLSearchParams(window.location.search).has('liquidacao_salva'))?'liquidacoes':(new URLSearchParams(window.location.search).has('prazos_salvos')?'repasses':'geral'));
 activate(initial,false);
})();
</script>
