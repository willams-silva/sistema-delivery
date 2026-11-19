<?php
/**
 * Prévia de estorno financeiro, somente leitura.
 * Nenhum recebimento, caixa, estoque ou venda é alterado aqui.
 */
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST') { http_response_code(405); exit(json_encode(['erro'=>'Método inválido.'])); }
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$vendaId=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if (!$empresa || !$usuario || !$vendaId) { http_response_code(403); exit(json_encode(['erro'=>'Sessão ou venda inválida.'])); }
require_once('../../../conexao.php');
try {
    $st=$pdo->prepare('SELECT id,valor,status,pago,caixa,cliente FROM vendas WHERE id=:venda AND empresa=:empresa LIMIT 1');
    $st->execute([':venda'=>$vendaId,':empresa'=>$empresa]);
    $venda=$st->fetch(PDO::FETCH_ASSOC);
    if (!$venda) { http_response_code(404); exit(json_encode(['erro'=>'Venda não encontrada.'])); }
    $st=$pdo->prepare('SELECT id,valor,subtotal,pago,residuo,id_ref,caixa,forma_pgto,referencia FROM receber WHERE empresa=:empresa AND venda_id=:venda ORDER BY id');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $parcelas=$st->fetchAll(PDO::FETCH_ASSOC);
    $recebido=0.0; $pendente=0.0; $caixas=[];
    foreach ($parcelas as $parcela) {
        if ($parcela['pago']==='Sim') {
            $recebido+=(float)($parcela['subtotal']??$parcela['valor']);
            $caixas[(int)$parcela['caixa']]=true;
        } else $pendente+=(float)$parcela['valor'];
    }
    $caixasOriginais=[];
    $consultaCaixa=$pdo->prepare('SELECT id,data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa');
    foreach (array_keys($caixas) as $caixaId) {
        $consultaCaixa->execute([':id'=>$caixaId,':empresa'=>$empresa]);
        $caixa=$consultaCaixa->fetch(PDO::FETCH_ASSOC);
        $caixasOriginais[]=['id'=>$caixaId,'encontrado'=>(bool)$caixa,
            'fechado'=>$caixa && $caixa['data_fechamento']!==null];
    }
    $st=$pdo->prepare('SELECT id,status,criado_em FROM estornos_vendas WHERE empresa=:empresa AND venda_id=:venda LIMIT 1');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $estornoExistente=$st->fetch(PDO::FETCH_ASSOC);
    $st=$pdo->prepare('SELECT COUNT(*) FROM receber filho INNER JOIN receber origem ON origem.id=filho.id_ref AND origem.empresa=filho.empresa WHERE origem.empresa=:empresa AND origem.venda_id=:venda AND (filho.venda_id IS NULL OR filho.venda_id<>:venda2)');
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId,':venda2'=>$vendaId]);
    $parciaisLegados=(int)$st->fetchColumn();
    // Um estorno deve sair de um caixa atualmente aberto do operador,
    // sem alterar o saldo histórico dos caixas de recebimento.
    $st=$pdo->prepare("SELECT id FROM caixas WHERE empresa=:empresa AND operador=:operador
        AND data_fechamento IS NULL ORDER BY id DESC LIMIT 1");
    $st->execute([':empresa'=>$empresa,':operador'=>$usuario]);
    $caixaAtual=(int)($st->fetchColumn() ?: 0);
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber r
        LEFT JOIN formas_pgto f ON f.id=r.forma_pgto AND f.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.pago='Sim'
          AND (f.id IS NULL OR r.caixa IS NULL OR r.caixa=0)");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $pagamentosSemRastreio=(int)$st->fetchColumn();
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber r
        LEFT JOIN receber origem ON origem.id=r.id_ref AND origem.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.residuo='Sim'
          AND (origem.id IS NULL OR origem.venda_id IS NULL OR origem.venda_id<>r.venda_id)");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $residuosSemOrigem=(int)$st->fetchColumn();
    $formasRecebidas=[];
    $st=$pdo->prepare("SELECT f.nome,COUNT(*) quantidade,
        COALESCE(SUM(COALESCE(r.subtotal,r.valor)),0) total
        FROM receber r INNER JOIN formas_pgto f ON f.id=r.forma_pgto AND f.empresa=r.empresa
        WHERE r.empresa=:empresa AND r.venda_id=:venda AND r.pago='Sim'
        GROUP BY f.id,f.nome");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $formasRecebidas=$st->fetchAll(PDO::FETCH_ASSOC);
    // Bloqueios adicionais para que a interface não ofereça estorno de
    // venda cancelada, com fidelidade ou sem pagamentos efetivos.
    $motivosBloqueio=[];
    if ($venda['status']==='Cancelado') $motivosBloqueio[]='Venda já cancelada';
    if ((int)$venda['cliente']>0) $motivosBloqueio[]='Venda com fidelidade exige revisão';
    if ($recebido<=0) $motivosBloqueio[]='Nenhum valor recebido para devolver';
    if (!$caixaAtual) $motivosBloqueio[]='Operador sem caixa aberto';
    if ($pagamentosSemRastreio>0) $motivosBloqueio[]='Pagamentos sem rastreabilidade';
    if ($residuosSemOrigem>0 || $parciaisLegados>0) $motivosBloqueio[]='Pagamentos parciais com vínculo inconsistente';
    if ($estornoExistente) $motivosBloqueio[]='Estorno já registrado';
    // A divergência financeira e parcelas pendentes também exigem revisão.
    // Não sugerir que uma prévia seja autorização para devolver dinheiro.
    $valorPendentePositivo=$pendente>0.009;
    if ($valorPendentePositivo) $motivosBloqueio[]='Venda parcialmente paga: conciliação manual necessária';
    $pagamentosNaoDinheiro=0;
    foreach ($formasRecebidas as $forma) {
        $nomeForma=mb_strtolower(trim((string)$forma['nome']),'UTF-8');
        if (!in_array($nomeForma,['dinheiro','espécie','especie'],true)
            && strpos($nomeForma,'dinheiro')===false) $pagamentosNaoDinheiro++;
    }
    if ($pagamentosNaoDinheiro>0) $motivosBloqueio[]='PIX, cartão ou outra forma exigem confirmação externa da devolução';
    // Confere a existência de todos os caixas de recebimento, inclusive
    // registros antigos que apontem para caixas de outra empresa.
    $caixasInvalidos=0;
    foreach ($caixasOriginais as $caixaOriginal) {
        if (!$caixaOriginal['encontrado']) $caixasInvalidos++;
    }
    if ($caixasInvalidos>0) $motivosBloqueio[]='Recebimento associado a caixa inexistente nesta empresa';
    // O registro de estorno não é comprovante de devolução PIX/cartão.
    // Nenhuma autorização financeira é inferida desta prévia.
    // Inventário da venda precisa ser rastreável antes de qualquer reversão.
    $st=$pdo->prepare("SELECT c.produto,c.quantidade,p.id produto_encontrado,p.tem_estoque
        FROM carrinho c LEFT JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa
        WHERE c.empresa=:empresa AND c.pedido=:venda");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId]);
    $itensVenda=$st->fetchAll(PDO::FETCH_ASSOC);
    $itensInvalidos=0;
    foreach ($itensVenda as $itemVenda) {
        if (!$itemVenda['produto_encontrado'] || !is_numeric($itemVenda['quantidade'])
            || (float)$itemVenda['quantidade']<=0) $itensInvalidos++;
    }
    if (!$itensVenda || $itensInvalidos>0) $motivosBloqueio[]='Itens ou produtos sem rastreabilidade de estoque';
    // Conferência cruzada: venda marcada como paga precisa ter o valor
    // integralmente conciliado, sem saldo pendente.
    if (($venda['pago'] ?? '')==='Sim' && ($pendente>0.009 || abs(round((float)$venda['valor']-$recebido,2))>0.009)) {
        $motivosBloqueio[]='Venda marcada como paga mas com recebimentos inconsistentes';
    }
    // Filhos de parcelas que ficaram sem venda_id não aparecem na soma
    // principal; precisam ser sinalizados antes de autorizar qualquer estorno.
    $st=$pdo->prepare("SELECT COUNT(*) FROM receber filho
        INNER JOIN receber origem ON origem.id=filho.id_ref AND origem.empresa=filho.empresa
        WHERE origem.empresa=:empresa AND origem.venda_id=:venda
          AND (filho.venda_id IS NULL OR filho.venda_id<>:venda2)");
    $st->execute([':empresa'=>$empresa,':venda'=>$vendaId,':venda2'=>$vendaId]);
    $dependenciasExternas=(int)$st->fetchColumn();
    if ($dependenciasExternas>0) $motivosBloqueio[]='Existem recebimentos derivados sem vínculo com esta venda';
    // Elegibilidade conservadora: somente dinheiro, quitação integral,
    // parcela simples, caixa aberto e estoque rastreável.
    // Isto NÃO executa nem habilita estorno; informa se a venda pode
    // seguir para implementação/testes da rotina transacional.
    $parcelasSimples=true;
    foreach ($parcelas as $parcela) {
        if (($parcela['referencia'] ?? '')!=='Venda'
            || ($parcela['pago'] ?? '')!=='Sim'
            || ($parcela['residuo'] ?? '')==='Sim'
            || (int)($parcela['id_ref'] ?? 0)>0) {
            $parcelasSimples=false;
            break;
        }
    }
    if (!$parcelasSimples) $motivosBloqueio[]='Recebimentos não são parcelas simples integralmente quitadas';
    $divergencia=round((float)$venda['valor']-$recebido-$pendente,2);
    if (abs($divergencia)>0.009) $motivosBloqueio[]='Valores recebidos e pendentes não conciliam com a venda';
    $candidatoEstornoDinheiro=count($parcelas)>0 && count($motivosBloqueio)===0
        && abs($divergencia)<0.01 && $pendente<0.01
        && $pagamentosNaoDinheiro===0 && $parcelasSimples;
    echo json_encode([
      'venda_id'=>$vendaId,'valor_venda'=>(float)$venda['valor'],
      'total_recebido'=>round($recebido,2),'total_pendente'=>round($pendente,2),
      'diferenca_conciliacao'=>$divergencia,
      'caixas_originais'=>$caixasOriginais,
      'caixa_atual_para_devolucao'=>$caixaAtual ?: null,
      'pagamentos_sem_rastreabilidade'=>$pagamentosSemRastreio,
      'caixas_originais_invalidos'=>$caixasInvalidos,
      'quantidade_itens_venda'=>count($itensVenda),
      'itens_sem_rastreabilidade'=>$itensInvalidos,
      'residuos_sem_origem'=>$residuosSemOrigem,
      'formas_de_pagamento_recebidas'=>$formasRecebidas,
      'recebimentos_parciais_sem_vinculo'=>$parciaisLegados,
      'dependencias_financeiras_externas'=>$dependenciasExternas,
      'estorno_existente'=>$estornoExistente?:null,
      'requer_revisao'=>!$parcelas || count($motivosBloqueio)>0,
      'motivos_bloqueio'=>$motivosBloqueio,
      'candidato_estorno_dinheiro'=>$candidatoEstornoDinheiro,
      'estorno_automatico_habilitado'=>false,
      'aviso'=>'Prévia informativa. Nenhuma devolução foi realizada.'
    ],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Previa estorno: '.$e->getMessage());
    http_response_code(500); echo json_encode(['erro'=>'Não foi possível preparar a prévia do estorno.']);
}
