<?php
/**
 * Prévia de estorno financeiro, somente leitura.
 * Nenhum recebimento, caixa, estoque ou venda é alterado aqui.
 */
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST') { http_response_code(405); exit(json_encode(['erro'=>'Método inválido.'])); }
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$vendaId=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario || !$vendaId) { http_response_code(403); exit(json_encode(['erro'=>'Sessão ou venda inválida.'])); }
require_once('../../../conexao.php');
try {
    $st=$pdo->prepare('SELECT id,valor,status,pago,caixa,cliente FROM vendas WHERE id=:venda AND empresa=:empresa LIMIT 1');
    $st->execute([':venda'=>$vendaId,':empresa'=>$empresa]);
    $venda=$st->fetch(PDO::FETCH_ASSOC);
    if (!$venda) { http_response_code(404); exit(json_encode(['erro'=>'Venda não encontrada.'])); }
    $st=$pdo->prepare('SELECT id,valor,subtotal,pago,residuo,id_ref,caixa,forma_pgto,referencia FROM receber WHERE empresa=:empresa AND venda_id=:venda ORDER BY id');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $parcelas=$st->fetchAll(PDO::FETCH_ASSOC);
    $recebido=0.0; $pendente=0.0; $caixas=[];
    foreach ($parcelas as $parcela) {
        if ($parcela['pago']==='Sim') {
            $recebido+=(float)($parcela['subtotal']??$parcela['valor']);
            $caixas[(int)$parcela['caixa']]=true;
        } else $pendente+=(float)$parcela['valor'];
    }
    $caixasOriginais=[];
    $consultaCaixa=$pdo->prepare('SELECT id,data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa');
    foreach (array_keys($caixas) as $caixaId) {
        $consultaCaixa->execute([':id'=>$caixaId,':empresa'=>$empresa]);
        $caixa=$consultaCaixa->fetch(PDO::FETCH_ASSOC);
        $caixasOriginais[]=['id'=>$caixaId,'encontrado'=>(bool)$caixa,
            'fechado'=>$caixa && $caixa['data_fechamento']!==null];
    }
    $st=$pdo->prepare('SELECT id,status,criado_em FROM estornos_vendas WHERE empresa=:empresa AND venda_id=:venda LIMIT 1');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $estornoExistente=$st->fetch(PDO::FETCH_ASSOC);
    $st=$pdo->prepare('SELECT COUNT(*) FROM receber filho INNER JOIN receber origem ON origem.id=filho.id_ref AND origem.empresa=filho.empresa WHERE origem.empresa=:empresa AND origem.venda_id=:venda AND (filho.venda_id IS NULL OR filho.venda_id<>:venda2)');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId,':venda2'=>$vendaId]);
    $parciaisLegados=(int)$st->fetchColumn();
    // Um estorno deve sair de um caixa atualmente aberto do operador,
    // sem alterar o saldo histórico dos caixas de recebimento.
    $st=$pdo->prepare("SELECT id FROM caixas WHERE empresa=:empresa AND operador=:operador
        AND data_fechamento IS NULL ORDER BY id DESC LIMIT 1");
    $st->execute([':empresa'=>$empresa,':operador'=>$usuario]);
    $caixaAtual=(int)($st->fetchColumn() ?: 0);
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber r
        LEFT JOIN formas_pgto f ON f.id=r.forma_pgto AND f.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.pago='Sim'
          AND (f.id IS NULL OR r.caixa IS NULL OR r.caixa=0)");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $pagamentosSemRastreio=(int)$st->fetchColumn();
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber r
        LEFT JOIN receber origem ON origem.id=r.id_ref AND origem.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.residuo='Sim'
          AND (origem.id IS NULL OR origem.venda_id IS NULL OR origem.venda_id<>r.venda_id)");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $residuosSemOrigem=(int)$st->fetchColumn();
    $formasRecebidas=[];
    $st=$pdo->prepare("SELECT f.nome,COUNT(*) quantidade,
        COALESCE(SUM(COALESCE(r.subtotal,r.valor)),0) total
        FROM receber r INNER JOIN formas_pgto f ON f.id=r.forma_pgto AND f.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.pago='Sim'
        GROUP BY f.id,f.nome");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $formasRecebidas=$st->fetchAll(PDO::FETCH_ASSOC);
    $divergencia=round((float)$venda['valor']-$recebido-$pendente,2);
    echo json_encode([
      'venda_id'=>$vendaId,'valor_venda'=>(float)$venda['valor'],
      'total_recebido'=>round($recebido,2),'total_pendente'=>round($pendente,2),
      'diferenca_conciliacao'=>$divergencia,
      'caixas_originais'=>$caixasOriginais,
      'caixa_atual_para_devolucao'=>$caixaAtual ?: null,
      'pagamentos_sem_rastreabilidade'=>$pagamentosSemRastreio,
      'residuos_sem_origem'=>$residuosSemOrigem,
      'formas_de_pagamento_recebidas'=>$formasRecebidas,
      'recebimentos_parciais_sem_vinculo'=>$parciaisLegados,
      'estorno_existente'=>$estornoExistente?:null,
      'requer_revisao'=>!$parcelas || abs($divergencia)>0.009 || $parciaisLegados>0 || $residuosSemOrigem>0 || $pagamentosSemRastreio>0 || !$caixaAtual || (bool)$estornoExistente,
      'estorno_automatico_habilitado'=>false,
      'aviso'=>'Prévia informativa. Nenhuma devolução foi realizada.'
    ],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Previa estorno: '.$e->getMessage());
    http_response_code(500); echo json_encode(['erro'=>'Não foi possível preparar a prévia do estorno.']);
}
