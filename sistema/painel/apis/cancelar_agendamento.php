<?php
// Cancela somente lembretes pendentes da empresa e do registro informados.
// Mantém histórico de mensagens já processadas.
$empresaAgendamento = filter_var($id_empresa ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$idAgendamento = filter_var($id ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$origemAgendamento = (string)($referencia_agendamento ?? 'receber');
if (!$empresaAgendamento || !$idAgendamento || !in_array($origemAgendamento, ['pagar','receber'], true)) {
    throw new InvalidArgumentException('Identificação inválida para cancelamento de agendamento.');
}
$delAgd = $pdo->prepare("DELETE FROM whatsapp_agendamentos
    WHERE empresa = :empresa AND referencia = :referencia
      AND id_ref = :id_ref AND status = 'Pendente'");
$delAgd->execute([
    ':empresa' => $empresaAgendamento,
    ':referencia' => $origemAgendamento,
    ':id_ref' => $idAgendamento
]);
