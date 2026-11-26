<?php
/**
 * Automacao de pedidos, para execucao via cron PHP CLI a cada minuto.
 * Nao acessivel por HTTP. Execute automacao_schema.sql antes.
 * Nao imprime: somente enfileira cupom para um agente de impressao.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../../../conexao.php';
date_default_timezone_set('America/Recife');
$agora = date('Y-m-d H:i:s');
$cfgs = $pdo->query("SELECT empresa, aceitar_segundos, preparar_segundos, imprimir_ao_aceitar FROM automacao_pedidos_config WHERE ativo = 1")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cfgs as $cfg) {
    $empresa = (int)$cfg['empresa'];
    // Recupera itens de pedidos liberados, inclusive os aceitos manualmente.
    // PIX sem baixa fica fora por precaucao; nunca reinicia itens em producao.
    try {
        $sync = $pdo->prepare("UPDATE carrinho c INNER JOIN vendas v ON v.id=c.pedido AND v.empresa=c.empresa INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa INNER JOIN config cfg ON cfg.empresa=c.empresa SET c.status='Aguardando' WHERE c.empresa=? AND cfg.delivery_monitor='Sim' AND v.status IN ('Aceito','Preparando') AND v.data >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND p.preparado IN ('Cozinha','Bar','Outro') AND (c.status IS NULL OR c.status IN ('','Iniciado')) AND (v.pago='Sim' OR (v.tipo_pgto IS NOT NULL AND TRIM(v.tipo_pgto)<>'' AND LOWER(v.tipo_pgto) NOT LIKE '%pix%'))");
        $sync->execute([$empresa]);
    } catch (Throwable $e) {
        error_log('Sincronizacao producao empresa ' . $empresa . ': ' . $e->getMessage());
    }
    $aceitar = max(30, (int)$cfg['aceitar_segundos']);
    $preparar = max(30, (int)$cfg['preparar_segundos']);
    // Um pedido nunca e avancado se um operador ja mudou seu status.
    $q = $pdo->prepare("SELECT id, status, data, hora FROM vendas WHERE empresa = ? AND status IN ('Iniciado','Aceito') AND data >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) ORDER BY id ASC LIMIT 500");
    $q->execute(array($empresa));
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $id = (int)$v['id'];
        if ($v['status'] === 'Iniciado') {
            $criado = strtotime($v['data'] . ' ' . $v['hora']);
            if (!$criado || $criado > time() || time() - $criado < $aceitar) continue;
            try {
                $pdo->beginTransaction();
                $u = $pdo->prepare("UPDATE vendas SET status = 'Aceito' WHERE id = ? AND empresa = ? AND status = 'Iniciado'");
                $u->execute(array($id,$empresa));
                if ($u->rowCount() === 1) {
                    // Não reabrir a fila se o pedido já foi confirmado como impresso.
                    $impresso = $pdo->prepare("SELECT impressao FROM vendas WHERE id=? AND empresa=?");
                    $impresso->execute(array($id,$empresa));
                    $fila = ($cfg['imprimir_ao_aceitar'] && $impresso->fetchColumn() !== 'Sim') ? 1 : 0;
                    $s = $pdo->prepare("INSERT INTO automacao_pedidos_estado (empresa,venda_id,aceito_em,imprimir_pendente) VALUES (?,?,NOW(),?) ON DUPLICATE KEY UPDATE aceito_em=COALESCE(automacao_pedidos_estado.aceito_em,VALUES(aceito_em)),imprimir_pendente=IF(automacao_pedidos_estado.impresso_em IS NOT NULL,0,VALUES(imprimir_pendente))");
                    $s->execute(array($empresa,$id,$fila));
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('Automacao pedido ' . $id . ': ' . $e->getMessage());
            }
        } elseif ($v['status'] === 'Aceito') {
            $s = $pdo->prepare("SELECT aceito_em FROM automacao_pedidos_estado WHERE empresa = ? AND venda_id = ?");
            $s->execute(array($empresa,$id));
            $aceitoEm = $s->fetchColumn();
            // Aceitacao manual sem timestamp: nunca inventar momento de aceite.
            if (!$aceitoEm || time() - strtotime($aceitoEm) < $preparar) continue;
            $u = $pdo->prepare("UPDATE vendas SET status = 'Preparando' WHERE id = ? AND empresa = ? AND status = 'Aceito'");
            $u->execute(array($id,$empresa));
        }
    }
}
