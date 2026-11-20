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

/** Retorna verdadeiro para a janela iniciada na própria data. */
function bcHorarioEspecialAberto(array $horario, string $agora): bool
{
    $inicio = substr($horario['inicio'], 0, 5);
    $fim = substr($horario['fim'], 0, 5);
    $hora = substr($agora, 0, 5);
    return $fim < $inicio ? $hora >= $inicio : ($hora >= $inicio && $hora <= $fim);
}

/**
 * Prioridade: pausa/manual > fechamento integral do dia corrente
 * > continuidade de expediente especial de ontem > horário especial de hoje
 * > continuidade normal de ontem > fechamento semanal > expediente normal.
 * Um horário especial hoje só começa na sua hora de abertura, não apaga
 * uma continuidade válida de ontem. Fechamento integral hoje bloqueia ambos.
 */
function bcEstadoDelivery(PDO $pdo, int $empresa, string $data, string $hora, string $pausa, string $statusManual, string $abertura, string $fechamento): array
{
    if ($pausa === 'Sim') return ['aberto'=>false, 'motivo'=>'pausado'];
    if ($statusManual === 'Fechado') return ['aberto'=>false, 'motivo'=>'manual'];

    $fechadoIntegral = static function(string $dia) use ($pdo, $empresa): bool {
        $q = $pdo->prepare('SELECT 1 FROM fechamentos_excepcionais WHERE empresa = :empresa AND data_fechamento = :data LIMIT 1');
        $q->execute([':empresa'=>$empresa, ':data'=>$dia]);
        return (bool)$q->fetchColumn();
    };
    if ($fechadoIntegral($data)) return ['aberto'=>false, 'motivo'=>'fechamento_integral'];

    $dias = ['Domingo','Segunda-Feira','Terça-Feira','Quarta-Feira','Quinta-Feira','Sexta-Feira','Sábado'];
    $diaFechado = static function(string $diaData) use ($pdo, $empresa, $dias): bool {
        $dia = $dias[(int)date('w', strtotime($diaData))];
        $q = $pdo->prepare('SELECT 1 FROM dias WHERE empresa = :empresa AND dia = :dia LIMIT 1');
        $q->execute([':empresa'=>$empresa, ':dia'=>$dia]);
        return (bool)$q->fetchColumn();
    };

    $agora = substr($hora, 0, 5);
    $ontem = date('Y-m-d', strtotime($data . ' -1 day'));
    $ontemEspecial = bcHorarioEspecial($pdo, $empresa, $ontem);
    if (!$fechadoIntegral($ontem)) {
        if ($ontemEspecial !== null) {
            $inicioOntem = substr($ontemEspecial['inicio'], 0, 5);
            $fimOntem = substr($ontemEspecial['fim'], 0, 5);
            if ($fimOntem < $inicioOntem && $agora <= $fimOntem) {
                return ['aberto'=>true, 'motivo'=>'continuidade_especial'];
            }
        } else {
            $inicioNormal = substr($abertura, 0, 5);
            $fimNormal = substr($fechamento, 0, 5);
            if ($fimNormal < $inicioNormal && $agora <= $fimNormal && !$diaFechado($ontem)) {
                return ['aberto'=>true, 'motivo'=>'continuidade_madrugada'];
            }
        }
    }

    $especial = bcHorarioEspecial($pdo, $empresa, $data);
    if ($especial !== null) {
        $aberto = bcHorarioEspecialAberto($especial, $hora);
        return ['aberto'=>$aberto, 'motivo'=>$aberto?'horario_especial':'fora_horario_especial'];
    }
    if ($diaFechado($data)) return ['aberto'=>false, 'motivo'=>'fechamento_semanal'];
    $inicio = substr($abertura, 0, 5);
    $fim = substr($fechamento, 0, 5);
    $aberto = $fim < $inicio ? $agora >= $inicio : ($agora >= $inicio && $agora <= $fim);
    return ['aberto'=>$aberto, 'motivo'=>$aberto?'horario_normal':'fora_horario_normal'];
}
