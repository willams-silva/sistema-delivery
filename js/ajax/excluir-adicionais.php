<?php
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
$sessao = (string)($_SESSION['sessao_usuario'] ?? '');
$id_empresa = (int)$id_empresa;
$id_temp = (int) (@$_POST['id'] ?? 0);
$id_carrinho = (int) (@$_POST['carrinho'] ?? 0);

if ($id_empresa <= 0 || $id_temp <= 0 || $id_carrinho <= 0 || $sessao === '') {
    echo 'Dados inválidos';
    exit();
}

// O total_item do carrinho representa o preco de UMA unidade.
// A quantidade e aplicada somente ao montar o resumo do pedido.
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT t.valor_item, t.quantidade
                           FROM temp t
                           INNER JOIN carrinho c ON c.id = t.carrinho
                           WHERE t.id = ? AND t.carrinho = ? AND t.tabela = 'adicionais'
                             AND t.empresa = ? AND c.empresa = ? AND c.sessao = ?
                           LIMIT 1 FOR UPDATE");
    $stmt->execute([$id_temp, $id_carrinho, $id_empresa, $id_empresa, $sessao]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dados) {
        $pdo->rollBack();
        echo 'Adicional não encontrado neste carrinho';
        exit();
    }

    $valor_por_unidade = max(0, (float)$dados['valor_item'] * (float)$dados['quantidade']);
    $stmt = $pdo->prepare("DELETE FROM temp WHERE id = ? AND carrinho = ? AND empresa = ? AND tabela = 'adicionais'");
    $stmt->execute([$id_temp, $id_carrinho, $id_empresa]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Não foi possível remover o adicional');
    }

    $stmt = $pdo->prepare("UPDATE carrinho SET total_item = GREATEST(0, total_item - ?) WHERE id = ? AND empresa = ? AND sessao = ?");
    $stmt->execute([$valor_por_unidade, $id_carrinho, $id_empresa, $sessao]);
    $pdo->commit();
    echo 'Excluido com Sucesso';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Falha ao remover adicional do carrinho: ' . $e->getMessage());
    http_response_code(500);
    echo 'Não foi possível remover o adicional. Tente novamente.';
}
