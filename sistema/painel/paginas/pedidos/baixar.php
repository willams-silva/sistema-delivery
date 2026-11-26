<?php
@session_start();
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, private');
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$vendaId=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$forma=trim((string)($_POST['pgto']??''));
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(405);exit('Método inválido');}
if (!$empresa || !$usuario || !$vendaId) {http_response_code(403);exit('Sessão ou venda inválida');}
if ($forma==='' || strlen($forma)>100) {http_response_code(422);exit('Forma de pagamento inválida');}
require_once('../../../conexao.php');
try {
    $pdo->beginTransaction();
    $st=$pdo->prepare('SELECT id,valor,entrega,cliente,custo,pago,ref_api FROM vendas WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $st->execute([':id'=>$vendaId,':empresa'=>$empresa]);
    $venda=$st->fetch(PDO::FETCH_ASSOC);
    if (!$venda) {throw new DomainException('Pedido não encontrado nesta empresa');}
    if ($venda['pago']==='Sim') {throw new DomainException('Pedido já está pago; nenhuma baixa duplicada foi criada');}
    // Pedidos PIX com referência externa devem ser conciliados pelo provedor,
    // nunca quitados manualmente como outra forma.
    if (trim((string)($venda['ref_api']??''))!=='') {
        throw new DomainException('Pedido PIX pendente: confirme pelo Mercado Pago');
    }
    if (!is_numeric($venda['valor']) || (float)$venda['valor']<=0) {
        throw new DomainException('Valor do pedido inválido');
    }
    // PIX estático (Nubank ou outro banco) não possui confirmação automática.
    // A confirmação manual exige declaração expressa de conferência no extrato.
    $pixManual = preg_match('/\bpix\b/i', $forma) === 1;
    if ($pixManual && ($_POST['pix_conferido'] ?? '') !== '1') {
        throw new DomainException('Confira o crédito no extrato bancário e marque a confirmação do PIX');
    }
    $st=$pdo->prepare('SELECT id FROM formas_pgto WHERE empresa=:empresa AND nome=:nome LIMIT 1');
    $st->execute([':empresa'=>$empresa,':nome'=>$forma]);
    $formaId=(int)$st->fetchColumn();
    if (!$formaId) {throw new DomainException('Forma de pagamento não cadastrada');}
    $st=$pdo->prepare('SELECT id FROM caixas WHERE empresa=:empresa AND operador=:usuario AND data_fechamento IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE');
    $st->execute([':empresa'=>$empresa,':usuario'=>$usuario]);
    $caixa=(int)$st->fetchColumn();
    if (!$caixa) {throw new DomainException('Nenhum caixa aberto para o operador #'.$usuario.' na empresa #'.$empresa.'. Confira o operador conectado e abra seu caixa.');}
    $st=$pdo->prepare('SELECT COUNT(*) FROM receber WHERE empresa=:empresa AND venda_id=:venda');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    if ((int)$st->fetchColumn()>0) {
        throw new DomainException('Já existem recebimentos vinculados: revise a venda antes da baixa');
    }
    $st=$pdo->prepare("INSERT INTO receber
        (descricao,cliente,valor,subtotal,data_lanc,hora,pago,usuario_pgto,vencimento,data_pgto,
         foto,arquivo,forma_pgto,caixa,referencia,custo,empresa,venda_id)
        VALUES (:descricao,:cliente,:valor,:subtotal,CURDATE(),CURTIME(),'Sim',:usuario,
                CURDATE(),CURDATE(),'sem-foto.png','sem-foto.png',:forma,:caixa,:referencia,:custo,:empresa,:venda)");
    $st->execute([
        ':descricao'=>$venda['entrega'],':cliente'=>$venda['cliente'],
        ':valor'=>$venda['valor'],':subtotal'=>$venda['valor'],':usuario'=>$usuario,
        ':forma'=>$formaId,':caixa'=>$caixa,':referencia'=>$venda['entrega'],
        ':custo'=>$venda['custo'],':empresa'=>$empresa,':venda'=>$vendaId
    ]);
    $st=$pdo->prepare("UPDATE vendas SET pago='Sim',usuario_baixa=:usuario,tipo_pgto=:forma
        WHERE id=:venda AND empresa=:empresa AND pago<>'Sim'");
    $st->execute([':usuario'=>$usuario,':forma'=>$forma,':venda'=>$vendaId,':empresa'=>$empresa]);
    if ($st->rowCount()!==1) throw new RuntimeException('A venda foi alterada durante a baixa');
    $pdo->commit();
    echo 'Baixado com Sucesso';
} catch (DomainException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(409);
    echo $e->getMessage();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Baixa de pedido: '.$e->getMessage());
    http_response_code(500);
    echo 'Não foi possível registrar o pagamento';
}
