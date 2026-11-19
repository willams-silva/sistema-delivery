<?php
// Consulta somente leitura da trilha financeira, isolada por estabelecimento.
if (!in_array($_SESSION['nivel'] ?? '', ['Administrador', 'Gerente'], true)) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Acesso restrito à auditoria financeira.</div>';
    return;
}
$empresaAuditoria = (int)($id_empresa ?? 0);
if ($empresaAuditoria <= 0) { http_response_code(403); return; }
$inicioAuditoria = (string)($_GET['inicio'] ?? date('Y-m-01'));
$fimAuditoria = (string)($_GET['fim'] ?? date('Y-m-d'));
$validarDataAuditoria = static function ($v) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) &&
        checkdate((int)substr($v,5,2),(int)substr($v,8,2),(int)substr($v,0,4));
};
if (!$validarDataAuditoria($inicioAuditoria) || !$validarDataAuditoria($fimAuditoria) || $inicioAuditoria > $fimAuditoria) {
    $inicioAuditoria = date('Y-m-01'); $fimAuditoria = date('Y-m-d');
}
$origemAuditoria = (string)($_GET['origem'] ?? '');
$acaoAuditoria = (string)($_GET['acao'] ?? '');
if (!in_array($origemAuditoria, ['', 'pagar', 'receber'], true)) $origemAuditoria = '';
if (!in_array($acaoAuditoria, ['', 'CRIAR', 'ALTERAR', 'BAIXAR', 'ESTORNAR', 'EXCLUIR'], true)) $acaoAuditoria = '';
$paginaAuditoria = max(1, min(10000, (int)($_GET['pagina_auditoria'] ?? 1)));
$limiteAuditoria = 30;
$offsetAuditoria = ($paginaAuditoria - 1) * $limiteAuditoria;
$paramAuditoria = [':empresa'=>$empresaAuditoria, ':inicio'=>$inicioAuditoria.' 00:00:00', ':fim'=>$fimAuditoria.' 23:59:59'];
$filtroAuditoria = ' WHERE a.empresa=:empresa AND a.ocorrido_em BETWEEN :inicio AND :fim';
if ($origemAuditoria !== '') { $filtroAuditoria .= ' AND a.tabela_origem=:origem'; $paramAuditoria[':origem']=$origemAuditoria; }
if ($acaoAuditoria !== '') { $filtroAuditoria .= ' AND a.acao=:acao'; $paramAuditoria[':acao']=$acaoAuditoria; }
$registrosAuditoria = [];
$totalAuditoria = 0;
$erroAuditoria = '';
try {
    $st = $pdo->prepare('SELECT COUNT(*) FROM auditoria_financeira a'.$filtroAuditoria);
    $st->execute($paramAuditoria);
    $totalAuditoria = (int)$st->fetchColumn();
    $st = $pdo->prepare('SELECT a.id,a.empresa,a.tabela_origem,a.registro_id,a.acao,a.usuario_id,a.ocorrido_em,a.dados_anteriores,a.dados_novos, u.nome AS usuario_nome
        FROM auditoria_financeira a LEFT JOIN usuarios u ON u.id=a.usuario_id AND u.empresa=a.empresa
        '.$filtroAuditoria.' ORDER BY a.ocorrido_em DESC,a.id DESC LIMIT '.$limiteAuditoria.' OFFSET '.$offsetAuditoria);
    $st->execute($paramAuditoria);
    $registrosAuditoria = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Falha ao consultar auditoria financeira: '.$e->getMessage());
    $erroAuditoria = 'Não foi possível consultar a auditoria. Verifique se a tabela foi criada no banco correto.';
}
$escAuditoria = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$detalheAuditoria = static function ($json) use ($escAuditoria) {
    if (!$json) return '—';
    $dados = json_decode($json, true);
    if (!is_array($dados)) return 'Indisponível';
    $rotulos = ['descricao'=>'Descrição','valor'=>'Valor','vencimento'=>'Vencimento','data_pgto'=>'Pagamento','forma_pgto'=>'Forma de pagamento','pago'=>'Pago','subtotal'=>'Subtotal','frequencia'=>'Frequência'];
    $partes = [];
    foreach ($rotulos as $chave=>$rotulo) {
        if (array_key_exists($chave,$dados)) $partes[] = $rotulo.': '.$dados[$chave];
    }
    return $escAuditoria(implode(' | ', $partes));
};
?>
<style>
.bc-audit-wrap{padding:18px 0}.bc-audit-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:18px}
.bc-audit-filter{display:flex;gap:12px;flex-wrap:wrap;align-items:end}.bc-audit-filter label{display:block;font-weight:600;font-size:13px;margin-bottom:5px}
.bc-audit-filter input,.bc-audit-filter select{min-height:38px;border:1px solid #cbd5e1;border-radius:7px;padding:6px 9px;max-width:100%}
.bc-audit-table{overflow-x:auto}.bc-audit-table table{width:100%;border-collapse:collapse;font-size:13px}
.bc-audit-table th,.bc-audit-table td{padding:11px;border-bottom:1px solid #e5e7eb;vertical-align:top;text-align:left}
.bc-audit-table th{background:#f8fafc;white-space:nowrap}.bc-audit-details{max-width:320px;overflow-wrap:anywhere}
.bc-audit-details summary{cursor:pointer;color:#2563eb}.bc-audit-pages{display:flex;gap:12px;align-items:center;margin-top:14px}
</style>
<div class="bc-audit-wrap">
<div class="bc-audit-card">
<h3><i class="fa fa-history"></i> Auditoria Financeira</h3>
<p>Histórico de operações financeiras registradas automaticamente. Consulta somente leitura, restrita ao estabelecimento atual.</p>
<form method="get" action="index.php" class="bc-audit-filter">
<input type="hidden" name="pagina" value="auditoria_financeira">
<div><label for="aud-inicio">De</label><input id="aud-inicio" type="date" name="inicio" value="<?php echo $escAuditoria($inicioAuditoria); ?>"></div>
<div><label for="aud-fim">Até</label><input id="aud-fim" type="date" name="fim" value="<?php echo $escAuditoria($fimAuditoria); ?>"></div>
<div><label for="aud-origem">Tipo</label><select id="aud-origem" name="origem">
<option value="">Todos</option><option value="pagar" <?php echo $origemAuditoria==='pagar'?'selected':''; ?>>Contas a Pagar</option><option value="receber" <?php echo $origemAuditoria==='receber'?'selected':''; ?>>Contas a Receber</option>
</select></div>
<div><label for="aud-acao">Operação</label><select id="aud-acao" name="acao">
<option value="">Todas</option>
<?php foreach (['CRIAR','ALTERAR','BAIXAR','ESTORNAR','EXCLUIR'] as $op): ?><option value="<?php echo $op; ?>" <?php echo $acaoAuditoria===$op?'selected':''; ?>><?php echo $op; ?></option><?php endforeach; ?>
</select></div>
<div><button type="submit" class="btn btn-primary">Filtrar</button></div>
</form>
</div>
<div class="bc-audit-card">
<?php if ($erroAuditoria): ?><div class="alert alert-warning"><?php echo $escAuditoria($erroAuditoria); ?></div><?php else: ?>
<p><strong><?php echo number_format($totalAuditoria,0,',','.'); ?></strong> evento(s) encontrado(s). Exibindo até 30 por página.</p>
<div class="bc-audit-table"><table><thead><tr><th>Data/hora</th><th>Operação</th><th>Origem</th><th>Lançamento</th><th>Usuário</th><th>Antes</th><th>Depois</th></tr></thead><tbody>
<?php foreach ($registrosAuditoria as $evento): ?>
<tr>
<td><?php echo $escAuditoria(date('d/m/Y H:i:s',strtotime($evento['ocorrido_em']))); ?></td>
<td><?php echo $escAuditoria($evento['acao']); ?></td>
<td><?php echo $evento['tabela_origem']==='pagar'?'A pagar':'A receber'; ?></td>
<td>#<?php echo (int)$evento['registro_id']; ?></td>
<td><?php echo $escAuditoria($evento['usuario_nome'] ?: ('ID '.$evento['usuario_id'])); ?></td>
<td class="bc-audit-details"><details><summary>Ver estado anterior</summary><?php echo $detalheAuditoria($evento['dados_anteriores']); ?></details></td>
<td class="bc-audit-details"><details><summary>Ver estado novo</summary><?php echo $detalheAuditoria($evento['dados_novos']); ?></details></td>
</tr>
<?php endforeach; ?>
<?php if (!$registrosAuditoria): ?><tr><td colspan="7">Nenhum evento registrado para os filtros selecionados.</td></tr><?php endif; ?>
</tbody></table></div>
<?php
$paramLinks = ['pagina'=>'auditoria_financeira','inicio'=>$inicioAuditoria,'fim'=>$fimAuditoria,'origem'=>$origemAuditoria,'acao'=>$acaoAuditoria];
?>
<div class="bc-audit-pages">
<?php if ($paginaAuditoria>1): ?><a class="btn btn-outline-secondary btn-sm" href="index.php?<?php echo $escAuditoria(http_build_query($paramLinks+['pagina_auditoria'=>$paginaAuditoria-1])); ?>">Anterior</a><?php endif; ?>
<span>Página <?php echo $paginaAuditoria; ?> de <?php echo max(1,(int)ceil($totalAuditoria/$limiteAuditoria)); ?></span>
<?php if ($paginaAuditoria*$limiteAuditoria<$totalAuditoria): ?><a class="btn btn-outline-secondary btn-sm" href="index.php?<?php echo $escAuditoria(http_build_query($paramLinks+['pagina_auditoria'=>$paginaAuditoria+1])); ?>">Próxima</a><?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
