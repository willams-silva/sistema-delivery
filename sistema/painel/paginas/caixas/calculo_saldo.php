<?php
/**
 * Saldo físico esperado de um caixa: somente movimentações pagas em dinheiro.
 * Não inclui PIX/cartão, que não compõem o numerário da gaveta.
 */
function calcularSaldoCaixa(PDO $pdo, int $empresa, int $caixa, float $abertura): array {
    $totais=[];
    // Soma todas as formas de numerário cadastradas para a empresa, não apenas o primeiro ID.
    foreach (['receber'=>'entradas','pagar'=>'saidas'] as $tabela=>$chave) {
        $st=$pdo->prepare("SELECT COALESCE(SUM(COALESCE(m.subtotal,m.valor)),0)
            FROM $tabela m
            INNER JOIN formas_pgto f ON f.id=m.forma_pgto AND f.empresa=m.empresa
            WHERE m.empresa=:empresa AND m.caixa=:caixa AND m.pago='Sim'
              AND LOWER(TRIM(f.nome)) IN ('dinheiro','espécie','especie','em dinheiro','pagamento em dinheiro')");
        $st->execute([':empresa'=>$empresa,':caixa'=>$caixa]);
        $totais[$chave]=(float)$st->fetchColumn();
    }
    $st=$pdo->prepare('SELECT COALESCE(SUM(s.valor),0) FROM sangrias s INNER JOIN caixas c ON c.id=s.caixa WHERE c.id=:caixa AND c.empresa=:empresa');
    $st->execute([':caixa'=>$caixa,':empresa'=>$empresa]);
    $totais['sangrias']=(float)$st->fetchColumn();
    // Devoluções em espécie somente quando efetivamente concluídas.
    // O caixa de origem não é alterado: a saída pertence ao caixa de estorno.
    // Registros apenas preparados/registrados não movimentam o numerário.
    $st=$pdo->prepare("SELECT COALESCE(SUM(d.valor_devolvido),0)
        FROM estornos_vendas e
        INNER JOIN estornos_vendas_recebimentos d
          ON d.estorno_id=e.id AND d.empresa=e.empresa
        INNER JOIN formas_pgto f
          ON f.id=d.forma_pgto_id AND f.empresa=e.empresa
        WHERE e.empresa=:empresa AND e.caixa_estorno_id=:caixa
          AND e.status='Concluido'
          AND LOWER(TRIM(f.nome)) IN ('dinheiro','espécie','especie','em dinheiro','pagamento em dinheiro')");
    $st->execute([':empresa'=>$empresa,':caixa'=>$caixa]);
    $totais['estornos_dinheiro']=(float)$st->fetchColumn();
    $totais['esperado']=round($abertura+$totais['entradas']-$totais['saidas']-$totais['sangrias']-$totais['estornos_dinheiro'],2);
    return $totais;
}
