<?php
/**
 * Pré-validação somente leitura: nenhuma venda, parcela ou estoque é alterado.
 * Responde JSON para futura interface de cancelamento auditável.
 */
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405); echo json_encode(['erro'=>'Método inválido.']); exit;
}
$empresa=filter_var($_SESSION['empresa'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario || !$id) {
    http_response_code(403); echo json_encode(['erro'=>'Sessão ou venda inválida.']); exit;
}
require_once('../../../conexao.php');
try {
    $v=$pdo->prepare('SELECT id,status,pago,valor,caixa FROM vendas WHERE id=:id AND empresa=:empresa LIMIT 1');
    $v->execute([':id'=>$id,':empresa'=>$empresa]);
    $venda=$v->fetch(PDO::FETCH_ASSOC);
    if (!$venda) { http_response_code(404); echo json_encode(['erro'=>'Venda não encontrada.']); exit; }
    $st=$pdo->prepare("SELECT COUNT(*) quantidade,
        COALESCE(SUM(CASE WHEN pago='Sim' AND (residuo IS NULL OR residuo<>'Sim') THEN COALESCE(subtotal,valor) ELSE 0 END),0) pago_direto,
        COALESCE(SUM(CASE WHEN pago='Sim' AND residuo='Sim' THEN COALESCE(subtotal,valor) ELSE 0 END),0) pago_parcial,
        COALESCE(SUM(CASE WHEN pago<>'Sim' OR pago IS NULL THEN valor ELSE 0 END),0) pendente
        FROM receber WHERE empresa=:empresa AND venda_id=:venda");
    $st->execute([':empresa'=>$empresa,':venda'=>$id]);
    $financeiro=$st->fetch(PDO::FETCH_ASSOC);
    $st=$pdo->prepare('SELECT data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa LIMIT 1');
    $st->execute([':id'=>(int)$venda['caixa'],':empresa'=>$empresa]);
    $fechamento=$st->fetchColumn();
    // Uma venda pode ter pagamentos registrados em caixas diferentes.
    // O caixa original sozinho não é suficiente para decidir se há estorno possível.
    $st=$pdo->prepare("SELECT COUNT(*) AS parcelas_em_caixa_fechado,
        COALESCE(SUM(COALESCE(r.subtotal,r.valor)),0) AS recebido_em_caixa_fechado
        FROM receber r
        INNER JOIN caixas c ON c.id=r.caixa AND c.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda
          AND r.pago='Sim' AND c.data_fechamento IS NOT NULL");
    $st->execute([':empresa'=>$empresa,':venda'=>$id]);
    $caixasFechados=$st->fetch(PDO::FETCH_ASSOC);
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber
        WHERE empresa=:empresa AND venda_id=:venda
          AND pago='Sim' AND (caixa IS NULL OR caixa=0)");
    $st->execute([':empresa'=>$empresa,':venda'=>$id]);
    $pagosSemCaixa=(int)$st->fetchColumn();
    // Conferência de integridade: recebimentos parciais precisam apontar
    // para uma parcela-mãe da mesma empresa e da mesma venda.
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber r
        LEFT JOIN receber origem ON origem.id=r.id_ref AND origem.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.residuo='Sim'
          AND (r.id_ref IS NULL OR r.id_ref=0 OR origem.id IS NULL
               OR origem.venda_id IS NULL OR origem.venda_id<>r.venda_id)");
    $st->execute([':empresa'=>$empresa,':venda'=>$id]);
    $parciaisSemOrigem=(int)$st->fetchColumn();
    // Pagamentos vinculados a caixas inexistentes não podem ser estornados
    // por um fluxo automático sem conciliação manual.
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber r
        LEFT JOIN caixas c ON c.id=r.caixa AND c.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.pago='Sim'
          AND c.id IS NULL");
    $st->execute([':empresa'=>$empresa,':venda'=>$id]);
    $pagosSemCaixaValido=(int)$st->fetchColumn();
    // Conciliação: parcelas quitadas, resíduos e saldos abertos precisam
    // somar exatamente o valor da venda, antes de qualquer estorno.
    $totalConciliado=round(
        (float)$financeiro['pago_direto']
        +(float)$financeiro['pago_parcial']
        +(float)$financeiro['pendente'], 2
    );
    $diferencaConciliacao=round((float)$venda['valor']-$totalConciliado,2);
    $conciliacaoOk=abs($diferencaConciliacao)<0.01;
    $legado=(int)$financeiro['quantidade']===0;
    $cancelada=$venda['status']==='Cancelado';
    echo json_encode([
        'venda_id'=>$id,
        'status'=>$venda['status'],
        'valor_venda'=>(float)$venda['valor'],
        'parcelas_vinculadas'=>(int)$financeiro['quantidade'],
        'total_recebido'=>round((float)$financeiro['pago_direto']+(float)$financeiro['pago_parcial'],2),
        'total_pendente'=>round((float)$financeiro['pendente'],2),
        'caixa_fechado'=>$fechamento!==false && $fechamento!==null && $fechamento!=='',
        'parcelas_pagas_em_caixas_fechados'=>(int)$caixasFechados['parcelas_em_caixa_fechado'],
        'recebido_em_caixas_fechados'=>round((float)$caixasFechados['recebido_em_caixa_fechado'],2),
        'parcelas_pagas_sem_caixa'=>$pagosSemCaixa,
        'parcelas_pagas_sem_caixa_valido'=>$pagosSemCaixaValido,
        'recebimentos_parciais_sem_origem_valida'=>$parciaisSemOrigem,
        'total_financeiro_conciliado'=>$totalConciliado,
        'diferenca_conciliacao'=>$diferencaConciliacao,
        'conciliacao_valores_ok'=>$conciliacaoOk,
        'venda_legada_sem_vinculo'=>$legado,
        'cancelamento_liberado'=>false,
        'motivo'=>$cancelada?'Venda já cancelada.':($legado?'Venda sem parcelas vinculadas: requer conciliação manual.':($parciaisSemOrigem || $pagosSemCaixaValido || !$conciliacaoOk ? 'Inconsistência financeira: conciliação manual necessária.' : 'Estorno transacional ainda não habilitado.'))
    ],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Pré-validação cancelamento: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['erro'=>'Não foi possível verificar o cancelamento.']);
}
