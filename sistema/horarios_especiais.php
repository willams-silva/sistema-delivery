<?php
/**
 * Horário excepcional de uma empresa em uma data (YYYY-MM-DD).
 * Retorna null quando não há exceção. A configuração semanal permanece intacta.
 */
function bcHorarioEspecial(PDO $pdo, int $empresa, string $data): ?array
{
    $q = $pdo->prepare('SELECT TIME_FORMAT(hora_inicio, "%H:%i:%s") AS inicio, TIME_FORMAT(hora_fim, "%H:%i:%s") AS fim FROM horarios_especiais WHERE empresa = :empresa AND data_especial = :data LIMIT 1');
    $q->execute([':empresa' => $empresa, ':data' => $data]);
    $horario = $q->fetch(PDO::FETCH_ASSOC);
    return $horario ?: null;
}

/** A regra excepcional só substitui a regra semanal na data cadastrada. */
function bcHorarioEspecialAberto(array $horario, string $agora): bool
{
    return $agora >= $horario['inicio'] && $agora <= $horario['fim'];
}
