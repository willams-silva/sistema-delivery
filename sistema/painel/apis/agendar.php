<?php

// Espera existir no contexto:
// $id_empresa, $telefone_envio, $mensagem_whatsapp, $data_agd

$hash = ''; // vai voltar para seu UPDATE $tabela SET hash = '$hash'

if(@$referencia_agendamento == ""){
	$referencia_agendamento = 'receber';
}

if(@$ultimo_id == ""){
	$ultimo_id = @$id_ult_registro;
}

try {

    // normaliza telefone (garante só números)
    $telefone = preg_replace('/\D+/', '', (string)$telefone_envio);

    // normaliza mensagem (mantém padrão que você já usa)
    $mensagem = (string)$mensagem_whatsapp;

    // valida data agendada (precisa vir YYYY-mm-dd HH:ii:ss)
    $data_agendada = (string)$data_agd;

   

    if ($telefone == '') {
        throw new Exception('Telefone inválido para agendamento.');
    }

    if (trim($mensagem) == '') {
        throw new Exception('Mensagem vazia.');
    }

    if (trim($data_agendada) == '') {
        throw new Exception('Data agendada vazia.');
    }

    // Para contas a pagar, agenda o lembrete com antecedência por empresa.
    // A data recebida dos chamadores continua sendo a data do vencimento.
    if ($referencia_agendamento === 'pagar') {
        $dias = 7;
        try {
            $config = $pdo->prepare('SELECT dias_antecedencia FROM alertas_vencimento_pagar WHERE empresa=:empresa');
            $config->execute([':empresa'=>(int)$id_empresa]);
            $preferencia = $config->fetchColumn();
            if ($preferencia !== false) $dias = max(1, min(30, (int)$preferencia));
        } catch (Throwable $e) {
            error_log('Agendar lembrete pagar - configuração: '.$e->getMessage());
        }
        $vencimentoAgendamento = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $data_agendada);
        if (!$vencimentoAgendamento || $vencimentoAgendamento->format('Y-m-d H:i:s') !== $data_agendada) {
            throw new RuntimeException('Data de vencimento inválida para lembrete.');
        }
        $antecipado = $vencimentoAgendamento->modify('-'.$dias.' days');
        // Não cria tarefas retroativas: evita disparos inesperados ao cadastrar contas antigas.
        if ($antecipado <= new DateTimeImmutable('now')) {
            $hash = '';
            return;
        }
        $data_agendada = $antecipado->format('Y-m-d H:i:s');
        $mensagem = str_replace(
            ['_Queremos lembrar que sua Conta Vencendo Hoje_', '_Queremos lembrar que sua Conta Vencendo Hoje_'],
            '_Lembrete: sua conta vencerá em '.$dias.' dia(s)_',
            $mensagem
        );
    }

    // Evita repetir agendamentos pendentes para a mesma conta e vencimento.
    // Restringe a proteção ao Contas a Pagar para preservar outros fluxos.
    if ($referencia_agendamento === 'pagar' && (int)$ultimo_id > 0) {
        $verifica = $pdo->prepare("SELECT id FROM whatsapp_agendamentos
            WHERE empresa=:empresa AND referencia='pagar' AND id_ref=:id_ref
              AND data_agendada=:data AND status='Pendente'
            ORDER BY id DESC LIMIT 1");
        $verifica->execute([
            ':empresa'=>(int)$id_empresa,
            ':id_ref'=>(int)$ultimo_id,
            ':data'=>$data_agendada
        ]);
        $existente = $verifica->fetchColumn();
        if ($existente !== false) {
            $hash = (string)$existente;
            return;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO whatsapp_agendamentos
        (empresa, telefone, mensagem, data_agendada, status, tentativas, referencia, id_ref)
        VALUES
        (:empresa, :telefone, :mensagem, :data_agendada, 'Pendente', 0, :referencia, :id_ref)
    ");

    $stmt->execute([
        ':empresa'       => (int)$id_empresa,
        ':telefone'      => $telefone,
        ':mensagem'      => $mensagem,
        ':data_agendada' => $data_agendada,
        ':referencia' => $referencia_agendamento,
        ':id_ref' => $ultimo_id
    ]);

    // usa o ID como hash (você já grava esse campo na tarefa)
    $hash = (string)$pdo->lastInsertId();

} catch (Exception $e) {
    // se quiser, você pode gravar isso em log; por enquanto só retorna hash vazio
    $hash = '';
}