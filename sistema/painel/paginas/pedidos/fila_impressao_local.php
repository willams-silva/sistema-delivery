<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once('../../../conexao.php');
require_once(__DIR__.'/permissao_setor.php');
function responder($status,$dados) { http_response_code($status); echo json_encode($dados, JSON_UNESCAPED_UNICODE); exit; }
$empresa = filter_var($_SESSION['empresa'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario = filter_var($_SESSION['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$setor = (string)($_REQUEST['setor'] ?? '');
if (!$empresa || !$usuario || ($_SESSION['aut_token_SaL1P'] ?? '') !== '25tNX1L1MSaL1P') responder(401,['erro'=>'Sessao invalida']);
if (!in_array($setor,['Cozinha','Bar','Outro'],true) || !bcPermiteSetor($pdo,(int)$empresa,(int)$usuario,$setor)) responder(403,['erro'=>'Sem permissao para este setor']);
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
try {
    if ($metodo === 'GET') {
        $q = $pdo->prepare("SELECT f.id,f.carrinho_id,f.setor,f.estado,f.tentativas,f.criado_em,f.atualizado_em,f.erro
            FROM pedidos_locais_fila_impressao f
            INNER JOIN carrinho c ON c.id=f.carrinho_id AND c.empresa=f.empresa
            INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa AND p.preparado=f.setor
            WHERE f.empresa=:empresa AND f.setor=:setor ORDER BY f.id DESC LIMIT 100");
        $q->execute([':empresa'=>$empresa,':setor'=>$setor]);
        responder(200,['fila'=>$q->fetchAll(PDO::FETCH_ASSOC),'impressao_automatica_ativa'=>false]);
    }
    if ($metodo !== 'POST') responder(405,['erro'=>'Metodo nao permitido']);
    // Inclusao manual somente: o envio fisico sera implementado em agente local autenticado.
    $id = filter_var($_POST['carrinho_id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$id) responder(400,['erro'=>'Item invalido']);
    $stmt = $pdo->prepare("INSERT INTO pedidos_locais_fila_impressao (empresa,carrinho_id,setor)
        SELECT c.empresa,c.id,p.preparado FROM carrinho c
        INNER JOIN produtos p ON p.id=c.produto AND p.empresa=c.empresa
        WHERE c.id=:id AND c.empresa=:empresa AND p.preparado=:setor
        AND c.status NOT IN ('Cancelado','Finalizado')
        ON DUPLICATE KEY UPDATE id=id");
    $stmt->execute([':id'=>$id,':empresa'=>$empresa,':setor'=>$setor]);
    $q = $pdo->prepare("SELECT id,estado FROM pedidos_locais_fila_impressao
        WHERE empresa=:empresa AND carrinho_id=:id AND setor=:setor LIMIT 1");
    $q->execute([':empresa'=>$empresa,':id'=>$id,':setor'=>$setor]);
    $fila=$q->fetch(PDO::FETCH_ASSOC);
    if (!$fila) responder(404,['erro'=>'Item nao encontrado ou nao elegivel']);
    responder(200,['fila_id'=>(int)$fila['id'],'estado'=>$fila['estado'],'duplicado'=>$stmt->rowCount()===0]);
} catch (PDOException $e) {
    error_log('Fila de impressao: '.$e->getCode());
    responder(503,['erro'=>'Fila indisponivel. Confirme a migracao SQL.']);
}
