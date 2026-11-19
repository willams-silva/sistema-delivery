<?php
/**
 * Saldo físico esperado de um caixa: somente movimentações pagas em dinheiro.
 * Não inclui PIX/cartão, que não compõem o numerário da gaveta.
 */
function calcularSaldoCaixa(PDO $pdo, int $empresa, int $caixa, float $abertura): array {
    $st=$pdo->prepare("SELECT id FROM formas_pgto WHERE empresa=:empresa AND nome LIKE '%Dinheiro%' ORDER BY id LIMIT 1");
    $st->execute([':empresa'=>$empresa]);
    $forma=$st->fetchColumn();
    if (!$forma) throw new RuntimeException('Forma de pagamento Dinheiro não cadastrada.');
    $totais=[];
    foreach (['receber'=>'entradas','pagar'=>'saidas'] as $tabela=>$chave) {
        $st=$pdo->prepare("SELECT COALESCE(SUM(COALESCE(subtotal,valor)),0) FROM $tabela WHERE empresa=:empresa AND caixa=:caixa AND forma_pgto=:forma AND pago='Sim'");
        $st->execute([':empresa'=>$empresa,':caixa'=>$caixa,':forma'=>$forma]);
        $totais[$chave]=(float)$st->fetchColumn();
    }
    $st=$pdo->prepare('SELECT COALESCE(SUM(s.valor),0) FROM sangrias s INNER JOIN caixas c ON c.id=s.caixa WHERE c.id=:caixa AND c.empresa=:empresa');
    $st->execute([':caixa'=>$caixa,':empresa'=>$empresa]);
    $totais['sangrias']=(float)$st->fetchColumn();
    $totais['esperado']=round($abertura+$totais['entradas']-$totais['saidas']-$totais['sangrias'],2);
    return $totais;
}
