<?php
/**
 * Cancelamento transacional restrito a vendas PDV totalmente pendentes,
 * sem fidelidade e com parcelas vinculadas e conciliadas.
 * Vendas pagas/legadas seguem bloqueadas até o fluxo de estorno.
 */
@session_start();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método inválido.'); }
$empresa=filter_var($_SESSION['empresa'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario || !$id) { http_response_code(403); exit('Sessão ou venda inválida.'); }
require_once('../../../conexao.php');
require_once __DIR__.'/../../includes/auditoria_financeira.php';
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    $st=$pdo->prepare('SELECT * FROM vendas WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $st->execute([':id'=>$id,':empresa'=>$empresa]);
    $venda=$st->fetch(PDO::FETCH_ASSOC);
    if (!$venda) throw new RuntimeException('Venda não encontrada nesta empresa.');
    if ($venda['status']==='Cancelado') throw new RuntimeException('Venda já cancelada.');
    // Evita cancelar pela rotina antiga uma venda que já entrou no fluxo de estorno.
    $st=$pdo->prepare('SELECT id,status FROM estornos_vendas WHERE empresa=:empresa AND venda_id=:venda LIMIT 1 FOR UPDATE');
    $st->execute([':empresa'=>$empresa,':venda'=>$id]);
    if ($st->fetch(PDO::FETCH_ASSOC)) {
        throw new RuntimeException('Esta venda possui registro de estorno. Utilize a conciliação financeira.');
    }

    // Não mexer em cartões de fidelidade sem histórico do saldo anterior.
    if ((int)($venda['cliente'] ?? 0)>0) throw new RuntimeException('Venda com fidelidade: cancelamento requer conciliação manual.');
    if (($venda['pago'] ?? '')==='Sim') throw new RuntimeException('Venda recebida: estorno financeiro obrigatório.');
    $st=$pdo->prepare('SELECT * FROM receber WHERE empresa=:empresa AND venda_id=:id ORDER BY id FOR UPDATE');
    $st->execute([':empresa'=>$empresa,':id'=>$id]);
    $parcelas=$st->fetchAll(PDO::FETCH_ASSOC);
    if (!$parcelas) throw new RuntimeException('Venda sem vínculo financeiro: conciliação manual necessária.');
    $total=0.0;
    foreach ($parcelas as $parcela) {
        if (($parcela['referencia'] ?? '')!=='Venda' || ($parcela['pago'] ?? '')!=='Não' ||
            ($parcela['residuo'] ?? '')==='Sim' || (int)($parcela['id_ref'] ?? 0)>0) {
            throw new RuntimeException('Existem recebimentos ou vínculos que exigem estorno manual.');
        }
        $total+=(float)$parcela['valor'];
    }
    if (abs(round($total,2)-round((float)$venda['valor'],2))>0.009) {
        throw new RuntimeException('Valor das parcelas diferente do total da venda.');
    }
    // Impede excluir parcelas-mãe com pagamentos parciais não vinculados a venda_id.
    $st=$pdo->prepare('SELECT COUNT(*) FROM receber WHERE empresa=:empresa AND id_ref=:id');
    foreach ($parcelas as $parcela) {
        $st->execute([':empresa'=>$empresa,':id'=>(int)$parcela['id']]);
        if ((int)$st->fetchColumn()>0) throw new RuntimeException('Parcela com dependências financeiras.');
    }
    $st=$pdo->prepare('SELECT produto,quantidade FROM carrinho WHERE empresa=:empresa AND pedido=:id FOR UPDATE');
    $st->execute([':empresa'=>$empresa,':id'=>$id]);
    $itens=$st->fetchAll(PDO::FETCH_ASSOC);
    if (!$itens) throw new RuntimeException('Venda sem itens de estoque rastreáveis.');
    $buscarProduto=$pdo->prepare('SELECT id,tem_estoque FROM produtos WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $devolver=$pdo->prepare('UPDATE produtos SET estoque=estoque+:quantidade WHERE id=:id AND empresa=:empresa');
    foreach ($itens as $item) {
        if (!is_numeric($item['quantidade']) || (float)$item['quantidade']<=0) throw new RuntimeException('Quantidade de item inválida.');
        $buscarProduto->execute([':id'=>(int)$item['produto'],':empresa'=>$empresa]);
        $produto=$buscarProduto->fetch(PDO::FETCH_ASSOC);
        if (!$produto) throw new RuntimeException('Produto da venda não encontrado.');
        if ($produto['tem_estoque']==='Sim') {
            $devolver->execute([':quantidade'=>(float)$item['quantidade'],':id'=>(int)$item['produto'],':empresa'=>$empresa]);
            if ($devolver->rowCount()!==1) throw new RuntimeException('Falha ao devolver estoque.');
        }
    }
    $excluir=$pdo->prepare("DELETE FROM receber WHERE id=:id AND empresa=:empresa AND pago='Não' AND venda_id=:venda");
    foreach ($parcelas as $parcela) {
        // Histórico preservado na auditoria antes de remover obrigação pendente.
        registrarAuditoriaFinanceira($pdo,$empresa,'receber',(int)$parcela['id'],'EXCLUIR',$usuario,$parcela,null);
        $excluir->execute([':id'=>(int)$parcela['id'],':empresa'=>$empresa,':venda'=>$id]);
        if ($excluir->rowCount()!==1) throw new RuntimeException('Falha ao cancelar parcela pendente.');
    }
    $st=$pdo->prepare("UPDATE vendas SET status='Cancelado' WHERE id=:id AND empresa=:empresa AND status<>'Cancelado'");
    $st->execute([':id'=>$id,':empresa'=>$empresa]);
    if ($st->rowCount()!==1) throw new RuntimeException('Falha ao cancelar venda.');
    $pdo->commit();
    echo 'Cancelado com Sucesso';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Cancelamento restrito venda '.$id.': '.$e->getMessage());
    http_response_code(409);
    echo 'Cancelamento não autorizado: '.$e->getMessage();
}
