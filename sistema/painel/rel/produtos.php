<?php
// Relatório de produtos: layout compatível com Dompdf e impressão A4.
$esc = static function ($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
};
$produtos_ativos = 0;
$produtos_inativos = 0;
$estoque_baixo = 0;
$total_compra = 0;
$total_venda = 0;
$consulta = $pdo->prepare("SELECT p.*, c.nome AS nome_categoria FROM produtos p LEFT JOIN categorias c ON c.id = p.categoria WHERE p.empresa = :empresa ORDER BY p.nome ASC");
$consulta->execute([':empresa' => $id_empresa]);
$produtos = $consulta->fetchAll(PDO::FETCH_ASSOC);
$total_reg = count($produtos);
foreach ($produtos as $produto) {
    $ativo = ($produto['ativo'] ?? '') === 'Sim';
    if ($ativo) { $produtos_ativos++; } else { $produtos_inativos++; }
    $estoque = (float)($produto['estoque'] ?? 0);
    $nivel = (float)($produto['nivel_estoque'] ?? 0);
    if ($ativo && $estoque < $nivel) { $estoque_baixo++; }
    $total_compra += (float)($produto['valor_compra'] ?? 0) * $estoque;
    $total_venda += (float)($produto['valor_venda'] ?? 0) * $estoque;
}
$moeda = static function ($valor) { return 'R$ ' . number_format((float)$valor, 2, ',', '.'); };
$logo_url = rtrim((string)$url_sistema, '/') . '/sistema/img/' . rawurlencode((string)$logo_sistema);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
@page { size: A4 portrait; margin: 18mm 12mm 17mm 12mm; }
* { box-sizing: border-box; }
body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #263238; }
.header { border-bottom: 2px solid #334155; padding-bottom: 10px; margin-bottom: 14px; }
.header-table { width: 100%; border-collapse: collapse; }
.header-table td { vertical-align: middle; }
.logo-cell { width: 19%; text-align: center; }
.logo { max-width: 95px; max-height: 62px; width: auto; height: auto; }
.company-cell { width: 46%; padding: 0 10px; line-height: 1.6; overflow-wrap: break-word; }
.company-name { font-size: 11px; font-weight: bold; color: #172b4d; }
.report-cell { width: 35%; text-align: right; line-height: 1.7; }
.report-title { font-size: 13px; font-weight: bold; color: #172b4d; }
.report-date { font-size: 9px; color: #64748b; }
.report-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.report-table thead { display: table-header-group; }
.report-table th { background: #e9eef5; border-bottom: 1px solid #94a3b8; padding: 8px 4px; font-size: 7px; text-align: left; }
.report-table td { border-bottom: 1px solid #e2e8f0; padding: 7px 4px; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
.report-table tr { page-break-inside: avoid; }
.report-table tbody tr:nth-child(even) { background: #f8fafc; }
.numeric { text-align: right !important; white-space: nowrap; }
.center { text-align: center !important; }
.inactive { color: #94a3b8; }
.low { color: #b91c1c; font-weight: bold; }
.summary { margin-top: 16px; page-break-inside: avoid; }
.summary-title { font-size: 10px; font-weight: bold; margin-bottom: 7px; }
.summary-table { width: 100%; border-collapse: collapse; }
.summary-table td { width: 25%; padding: 8px 6px; border: 1px solid #e2e8f0; }
.summary-label { font-size: 7px; color: #64748b; }
.summary-value { font-size: 11px; font-weight: bold; padding-top: 4px; }
.footer { position: fixed; bottom: -11mm; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 5px; color: #64748b; font-size: 7px; }
.watermark { position: fixed; top: 36%; left: 18%; width: 64%; opacity: 0.07; z-index: -1; }
</style>
</head>
<body>
<?php if (($marca_dagua ?? '') === 'Sim' && ($tipo_rel ?? '') !== 'HTML'): ?>
<img class="watermark" src="<?php echo $esc($logo_url); ?>" alt="">
<?php endif; ?>
<div class="header">
<table class="header-table"><tr>
<td class="logo-cell"><img class="logo" src="<?php echo $esc($logo_url); ?>" alt="Logo"></td>
<td class="company-cell">
<div class="company-name"><?php echo $esc(mb_strtoupper((string)$nome_sistema)); ?></div>
<?php if (!empty($cnpj_sistema)): ?><div><strong>CNPJ:</strong> <?php echo $esc($cnpj_sistema); ?></div><?php endif; ?>
<?php if (!empty($instagram_sistema)): ?><div><strong>Instagram:</strong> <?php echo $esc($instagram_sistema); ?></div><?php endif; ?>
<?php if (!empty($endereco_sistema)): ?><div><?php echo $esc($endereco_sistema); ?></div><?php endif; ?>
</td>
<td class="report-cell"><div class="report-title">RELATÓRIO DE PRODUTOS</div><div class="report-date"><?php echo $esc($data_hoje ?? date('d/m/Y')); ?></div></td>
</tr></table>
</div>
<table class="report-table">
<colgroup><col style="width:27%"><col style="width:17%"><col style="width:13%"><col style="width:13%"><col style="width:9%"><col style="width:12%"><col style="width:9%"></colgroup>
<thead><tr><th>PRODUTO</th><th>CATEGORIA</th><th class="numeric">VALOR COMPRA</th><th class="numeric">VALOR VENDA</th><th class="center">ESTOQUE</th><th class="center">NÍVEL MÍN.</th><th class="center">ATIVO</th></tr></thead>
<tbody>
<?php foreach ($produtos as $produto):
$ativo = ($produto['ativo'] ?? '') === 'Sim';
$baixo = $ativo && (float)($produto['estoque'] ?? 0) < (float)($produto['nivel_estoque'] ?? 0);
?>
<tr class="<?php echo $ativo ? '' : 'inactive'; ?>">
<td><?php echo $esc($produto['nome'] ?? ''); ?></td>
<td><?php echo $esc($produto['nome_categoria'] ?? ''); ?></td>
<td class="numeric"><?php echo $moeda($produto['valor_compra'] ?? 0); ?></td>
<td class="numeric"><?php echo $moeda($produto['valor_venda'] ?? 0); ?></td>
<td class="center <?php echo $baixo ? 'low' : ''; ?>"><?php echo $esc($produto['estoque'] ?? 0); ?></td>
<td class="center"><?php echo $esc($produto['nivel_estoque'] ?? 0); ?></td>
<td class="center"><?php echo $ativo ? 'Sim' : 'Não'; ?></td>
</tr>
<?php endforeach; ?>
<?php if (!$total_reg): ?><tr><td colspan="7" class="center">Nenhum produto cadastrado.</td></tr><?php endif; ?>
</tbody></table>
<div class="summary">
<div class="summary-title">RESUMO DO RELATÓRIO</div>
<table class="summary-table"><tr>
<td><div class="summary-label">PRODUTOS ATIVOS</div><div class="summary-value"><?php echo $produtos_ativos; ?></div></td>
<td><div class="summary-label">PRODUTOS INATIVOS</div><div class="summary-value"><?php echo $produtos_inativos; ?></div></td>
<td><div class="summary-label">ESTOQUE BAIXO</div><div class="summary-value"><?php echo $estoque_baixo; ?></div></td>
<td><div class="summary-label">TOTAL DE PRODUTOS</div><div class="summary-value"><?php echo $total_reg; ?></div></td>
</tr><tr>
<td colspan="2"><div class="summary-label">VALOR TOTAL DE CUSTO EM ESTOQUE</div><div class="summary-value"><?php echo $moeda($total_compra); ?></div></td>
<td colspan="2"><div class="summary-label">VALOR TOTAL DE VENDA EM ESTOQUE</div><div class="summary-value"><?php echo $moeda($total_venda); ?></div></td>
</tr></table>
</div>
<div class="footer"><?php echo $esc($nome_sistema); ?><?php if (!empty($telefone_sistema)): ?> · Telefone: <?php echo $esc($telefone_sistema); ?><?php endif; ?></div>
</body></html>