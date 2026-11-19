<?php
@session_start();
require_once('../../../conexao.php');
$empresa=filter_var($_SESSION['empresa'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST') {http_response_code(405);exit('Método inválido.');}
if (!$empresa || !$usuario || !$id) {http_response_code(403);exit('Sessão ou pedido inválido.');}
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    $st=$pdo->prepare('SELECT id,status,pago,caixa FROM vendas WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $st->execute([':id'=>$id,':empresa'=>$empresa]);
    $venda=$st->fetch(PDO::FETCH_ASSOC);
    if (!$venda) throw new RuntimeException('Pedido não encontrado nesta empresa.');
    if ($venda['status']==='Cancelado') throw new RuntimeException('Este pedido já foi cancelado.');
    // Excluir pedido sem conciliar recebimentos deixa receita órfã e permite
    // devoluções repetidas de estoque e pontos de fidelidade.
    throw new RuntimeException('Exclusão temporariamente bloqueada: pedido com fluxo financeiro requer cancelamento e estorno conciliados.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(409);
    echo $e->getMessage();
}
