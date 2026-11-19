<?php
@session_start();
require_once('../../../conexao.php');
require_once('../../buscar_config.php');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405); exit('Método inválido.');
}
$empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$usuario = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$informado = str_replace(',', '.', (string)($_POST['total_caixa_fechar'] ?? ''));
if (!$empresa || !$usuario || !$id) { http_response_code(403); exit('Sessão ou caixa inválido.'); }
if (!is_numeric($informado) || !is_finite((float)$informado) || (float)$informado < 0) {
    http_response_code(400); exit('Valor de fechamento inválido.');
}
$informado = round((float)$informado, 2);
try {
    $pdo->beginTransaction();
    $st = $pdo->prepare('SELECT id, operador, valor_abertura, data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $st->execute([':id'=>$id, ':empresa'=>$empresa]);
    $caixa = $st->fetch(PDO::FETCH_ASSOC);
    if (!$caixa) throw new RuntimeException('Caixa não encontrado.');
    if (!empty($caixa['data_fechamento'])) throw new RuntimeException('Caixa já fechado.');

    $nivel = $_SESSION['nivel'] ?? '';
    if ((int)$caixa['operador'] !== $usuario && !in_array($nivel, ['Administrador','Gerente'], true)) {
        throw new RuntimeException('Operador não autorizado para este caixa.');
    }
    $dinheiro = $pdo->prepare("SELECT id FROM formas_pgto WHERE empresa=:empresa AND nome LIKE '%Dinheiro%' ORDER BY id LIMIT 1");
    $dinheiro->execute([':empresa'=>$empresa]);
    $forma = $dinheiro->fetchColumn();
    if (!$forma) throw new RuntimeException('Forma de pagamento Dinheiro não cadastrada.');

    $total = function($tabela) use ($pdo,$empresa,$id,$forma) {
        $st = $pdo->prepare("SELECT COALESCE(SUM(COALESCE(subtotal,valor)),0) FROM $tabela WHERE empresa=:empresa AND caixa=:caixa AND forma_pgto=:forma AND pago='Sim'");
        $st->execute([':empresa'=>$empresa,':caixa'=>$id,':forma'=>$forma]);
        return (float)$st->fetchColumn();
    };
    $entradas = $total('receber');
    $saidas = $total('pagar');
    $sangrias = $pdo->prepare('SELECT COALESCE(SUM(valor),0) FROM sangrias WHERE empresa=:empresa AND caixa=:caixa');
    $sangrias->execute([':empresa'=>$empresa,':caixa'=>$id]);
    $retiradas = (float)$sangrias->fetchColumn();
    $esperado = round((float)$caixa['valor_abertura'] + $entradas - $saidas - $retiradas, 2);
    $quebra = round($informado - $esperado, 2);

    $atualiza = $pdo->prepare('UPDATE caixas SET data_fechamento=CURDATE(), valor_fechamento=:valor, quebra=:quebra, usuario_fechamento=:usuario WHERE id=:id AND empresa=:empresa AND data_fechamento IS NULL');
    $atualiza->execute([':valor'=>$informado,':quebra'=>$quebra,':usuario'=>$usuario,':id'=>$id,':empresa'=>$empresa]);
    if ($atualiza->rowCount() !== 1) throw new RuntimeException('Fechamento não confirmado.');
    $pdo->commit();
    echo 'Salvo com Sucesso';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Fechamento de caixa: '.$e->getMessage());
    http_response_code(409);
    echo 'Não foi possível fechar o caixa. Verifique o status, o operador e as movimentações.';
}
