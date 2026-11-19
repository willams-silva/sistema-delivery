<?php
@session_start();
$id_empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$id_usuario = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id_empresa || !$id_usuario) { http_response_code(403); exit('Sessão inválida.'); }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método inválido.'); }
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) { http_response_code(400); exit('Conta inválida.'); }
require_once('../../../conexao.php');
require_once('../../buscar_config.php');
require_once __DIR__.'/../../includes/auditoria_financeira.php';
try {
    $pdo->beginTransaction();
    $busca = $pdo->prepare('SELECT * FROM receber WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $busca->execute([':id'=>$id, ':empresa'=>$id_empresa]);
    $conta = $busca->fetch(PDO::FETCH_ASSOC);
    if (!$conta) { $pdo->rollBack(); http_response_code(404); exit('Conta não encontrada neste estabelecimento.'); }
    // Não permitir apagar lançamentos com movimentação financeira ou dependências.
    // O estorno deve recompor o saldo antes de qualquer exclusão.
    if (($conta['pago'] ?? '') === 'Sim' || ($conta['residuo'] ?? '') === 'Sim') {
        throw new RuntimeException('Conta recebida: estorno necessário antes da exclusão.');
    }
    $dependentes = $pdo->prepare("SELECT COUNT(*) FROM receber WHERE empresa=:empresa AND id_ref=:id");
    $dependentes->execute([':empresa'=>$id_empresa, ':id'=>$id]);
    if ((int)$dependentes->fetchColumn() > 0) {
        throw new RuntimeException('Conta com parcelas ou recebimentos vinculados.');
    }
    $exclui = $pdo->prepare('DELETE FROM receber WHERE id=:id AND empresa=:empresa');
    $exclui->execute([':id'=>$id, ':empresa'=>$id_empresa]);
    if ($exclui->rowCount() !== 1) throw new RuntimeException('Exclusão não confirmada.');
    registrarAuditoriaFinanceira($pdo, $id_empresa, 'receber', $id, 'EXCLUIR', $id_usuario, $conta, null);
    // Exclui somente agendamentos pendentes vinculados a esta empresa e conta.
    $limpa = $pdo->prepare("DELETE FROM whatsapp_agendamentos WHERE empresa=:empresa AND referencia='receber' AND id_ref=:id AND status='Pendente'");
    $limpa->execute([':empresa'=>$id_empresa, ':id'=>$id]);
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Excluir conta a receber: '.$e->getMessage());
    http_response_code(409); exit('Não é possível excluir esta conta enquanto houver recebimentos ou parcelas vinculadas.');
}
// Anexos podem ser compartilhados entre parcelas recorrentes; não removê-los sem verificar referências.
echo 'Excluído com Sucesso';
