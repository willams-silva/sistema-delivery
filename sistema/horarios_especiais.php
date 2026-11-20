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

/**
 * Funcionamento online: pausa/manual > fechamento integral do dia atual
 * > horário especial de hoje > janela noturna herdada de ontem
 * > fechamento semanal de hoje > horário normal de hoje.
 * O fechamento integral do dia atual impede pedidos mesmo durante a madrugada.
 */
function bcEstadoDelivery(PDO $pdo, int $empresa, string $data, string $hora, string $pausa, string $statusManual, string $abertura, string $fechamento): array
{
    if ($pausa === 'Sim') return ['aberto'=>false, 'motivo'=>'pausado'];
    if ($statusManual === 'Fechado') return ['aberto'=>false, 'motivo'=>'manual'];

    $q = $pdo->prepare('SELECT 1 FROM fechamentos_excepcionais WHERE empresa = :empresa AND data_fechamento = :data LIMIT 1');
    $q->execute([':empresa'=>$empresa, ':data'=>$data]);
    if ($q->fetchColumn()) return ['aberto'=>false, 'motivo'=>'fechamento_integral'];

    $especial = bcHorarioEspecial($pdo, $empresa, $data);
    if ($especial !== null) {
        $aberto = bcHorarioEspecialAberto($especial, $hora);
        return ['aberto'=>$aberto, 'motivo'=>$aberto?'horario_especial':'fora_horario_especial'];
    }

    $dias = ['Domingo','Segunda-Feira','Terça-Feira','Quarta-Feira','Quinta-Feira','Sexta-Feira','Sábado'];
    $diaFechado = static function(string $diaData) use ($pdo, $empresa, $dias): bool {
        $dia = $dias[(int)date('w', strtotime($diaData))];
        $q = $pdo->prepare('SELECT 1 FROM dias WHERE empresa = :empresa AND dia = :dia LIMIT 1');
        $q->execute([':empresa'=>$empresa, ':dia'=>$dia]);
        return (bool)$q->fetchColumn();
    };
    $inicio = substr($abertura,0,5);
    $fim = substr($fechamento,0,5);
    $agora = substr($hora,0,5);

    // A janela após meia-noite pertence ao expediente do dia anterior.
    if ($fim < $inicio && $agora <= $fim) {
        $ontem = date('Y-m-d', strtotime($data . ' -1 day'));
        $q = $pdo->prepare('SELECT 1 FROM fechamentos_excepcionais WHERE empresa = :empresa AND data_fechamento = :data LIMIT 1');
        $q->execute([':empresa'=>$empresa, ':data'=>$ontem]);
        $ontemFechado = (bool)$q->fetchColumn();
        // Se ontem teve um horário especial diurno, não herdar o expediente padrão.
        $ontemEspecial = bcHorarioEspecial($pdo, $empresa, $ontem);
        if (!$ontemFechado && $ontemEspecial === null && !$diaFechado($ontem)) {
            return ['aberto'=>true, 'motivo'=>'continuidade_madrugada'];
        }
    }

    if ($diaFechado($data)) return ['aberto'=>false, 'motivo'=>'fechamento_semanal'];
    $aberto = $fim < $inicio ? $agora >= $inicio : ($agora >= $inicio && $agora <= $fim);
    return ['aberto'=>$aberto, 'motivo'=>$aberto?'horario_normal':'fora_horario_normal'];
}
