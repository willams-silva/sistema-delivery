<?php
/**
 * Histórico de estornos da empresa, somente leitura.
 * Não altera caixa, recebimentos, vendas ou estoque.
 */
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    exit(json_encode(['erro'=>'Método inválido.']));
}
$empresa=filter_var($_SESSION['empresa'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario) {
    http_response_code(403);
    exit(json_encode(['erro'=>'Sessão inválida.']));
}
require_once('../../../conexao.php');
try {
    $pagina=filter_var($_GET['pagina'] ?? 1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 1;
    $pagina=min($pagina,10000);
    $limite=20;
    $offset=($pagina-1)*$limite;
    $vendaRaw=trim((string)($_GET['venda_id'] ?? ''));
    $venda=$vendaRaw==='' ? null : filter_var($vendaRaw,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if ($vendaRaw!=='' && $venda===false) {
        http_response_code(422);
        exit(json_encode(['erro'=>'Número da venda inválido.']));
    }
    $status=trim((string)($_GET['status'] ?? ''));
    $statusPermitidos=['Registrado','Concluido','Cancelado'];
    if ($status!=='' && !in_array($status,$statusPermitidos,true)) {
        http_response_code(422);
        exit(json_encode(['erro'=>'Filtro de status inválido.']));
    }
    $filtro=$venda ? ' AND e.venda_id=:venda' : '';
    if ($status!=='') $filtro.=' AND e.status=:status';
    $parametros=[':empresa'=>(int)$empresa];
    if ($status!=='') $parametros[':status']=$status;
    if ($venda) $parametros[':venda']=$venda;
    $contagem=$pdo->prepare("SELECT COUNT(*) FROM estornos_vendas e WHERE e.empresa=:empresa $filtro");
    $contagem->execute($parametros);
    $total=(int)$contagem->fetchColumn();
    // Corrige páginas fora do intervalo após alteração dos filtros.
    $totalPaginas=(int)ceil($total/$limite);
    if ($totalPaginas>0 && $pagina>$totalPaginas) {
        $pagina=$totalPaginas;
        $offset=($pagina-1)*$limite;
    }
    // Total efetivamente concluído, considerando todos os registros filtrados,
    // não apenas a página visível. Evita contar estornos pendentes/cancelados.
    $resumo=$pdo->prepare("SELECT COALESCE(SUM(er.valor_devolvido),0)
        FROM estornos_vendas e
        INNER JOIN estornos_vendas_recebimentos er
          ON er.estorno_id=e.id AND er.empresa=e.empresa
        WHERE e.empresa=:empresa AND e.status='Concluido' $filtro");
    $resumo->execute($parametros);
    $totalDevolvidoConcluido=round((float)$resumo->fetchColumn(),2);
    // Resumo de pendências: não confundir estorno registrado com devolução concluída.
    $pendencias=$pdo->prepare("SELECT COUNT(*) FROM estornos_vendas e
        WHERE e.empresa=:empresa AND e.status='Registrado' $filtro");
    $pendencias->execute($parametros);
    $totalPendentes=(int)$pendencias->fetchColumn();
    $consulta=$pdo->prepare("SELECT e.id,e.venda_id,e.caixa_estorno_id,e.usuario_id,
        e.motivo,e.valor_venda,e.valor_recebido,e.valor_pendente,e.status,e.criado_em,
        COALESCE(SUM(er.valor_devolvido),0) AS valor_devolvido_registrado,
        COUNT(er.id) AS quantidade_recebimentos
        FROM estornos_vendas e
        LEFT JOIN estornos_vendas_recebimentos er ON er.estorno_id=e.id AND er.empresa=e.empresa
        WHERE e.empresa=:empresa $filtro
        GROUP BY e.id,e.venda_id,e.caixa_estorno_id,e.usuario_id,e.motivo,
                 e.valor_venda,e.valor_recebido,e.valor_pendente,e.status,e.criado_em
        ORDER BY e.criado_em DESC,e.id DESC LIMIT $limite OFFSET $offset");
    $consulta->execute($parametros);
    echo json_encode([
        'pagina'=>$pagina,'por_pagina'=>$limite,'total'=>$total,
        'filtros'=>['venda_id'=>$venda,'status'=>$status],
        'total_paginas'=>$totalPaginas,
        'total_devolvido_concluido'=>$totalDevolvidoConcluido,
        'estornos_pendentes'=>$totalPendentes,
        'estornos'=>$consulta->fetchAll(PDO::FETCH_ASSOC),
        'somente_leitura'=>true
    ],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('Historico estornos: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['erro'=>'Não foi possível consultar o histórico de estornos.']);
}
