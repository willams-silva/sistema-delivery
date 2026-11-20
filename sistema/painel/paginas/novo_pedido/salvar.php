<?php
@session_start();
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
$empresa = filter_var($_SESSION['empresa'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$usuario = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$mesa = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$aberturaRaw = $_POST['id_ab'] ?? '';
$abertura = $aberturaRaw === '' ? null : filter_var($aberturaRaw, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$garcon = filter_var($_POST['garcon'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$pessoas = filter_var($_POST['pessoas'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1, 'max_range'=>100]]);
$nome = trim((string)($_POST['nome'] ?? ''));
$obs = trim((string)($_POST['obs'] ?? ''));
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !$empresa || !$usuario || !$mesa || !$garcon || !$pessoas || ($aberturaRaw !== '' && !$abertura)) {
    http_response_code(400);
    exit('Dados da mesa inválidos');
}
try {
    $pdo->beginTransaction();
    $q = $pdo->prepare("SELECT id, ativo, status FROM mesas WHERE id=:id AND empresa=:empresa FOR UPDATE");
    $q->execute([':id'=>$mesa, ':empresa'=>$empresa]);
    $mesaAtual = $q->fetch(PDO::FETCH_ASSOC);
    if (!$mesaAtual || $mesaAtual['ativo'] !== 'Sim') throw new RuntimeException('Mesa indisponível');
    $q = $pdo->prepare("SELECT id FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1");
    $q->execute([':id'=>$garcon, ':empresa'=>$empresa]);
    if (!$q->fetchColumn()) throw new RuntimeException('Garçom inválido');
    if ($abertura) {
        $q = $pdo->prepare("UPDATE abertura_mesa SET cliente=:cliente, garcon=:garcon, obs=:obs, pessoas=:pessoas WHERE id=:abertura AND mesa=:mesa AND empresa=:empresa AND status='Aberta'");
        $q->execute([':cliente'=>$nome, ':garcon'=>$garcon, ':obs'=>$obs, ':pessoas'=>$pessoas, ':abertura'=>$abertura, ':mesa'=>$mesa, ':empresa'=>$empresa]);
        $check = $pdo->prepare("SELECT id FROM abertura_mesa WHERE id=:abertura AND mesa=:mesa AND empresa=:empresa AND status='Aberta'");
        $check->execute([':abertura'=>$abertura, ':mesa'=>$mesa, ':empresa'=>$empresa]);
        if (!$check->fetchColumn()) throw new RuntimeException('Abertura não encontrada');
    } else {
        $q = $pdo->prepare("SELECT id FROM abertura_mesa WHERE mesa=:mesa AND empresa=:empresa AND status='Aberta' LIMIT 1");
        $q->execute([':mesa'=>$mesa, ':empresa'=>$empresa]);
        if ($q->fetchColumn() || $mesaAtual['status'] === 'Aberta') throw new RuntimeException('Mesa já está aberta');
        $q = $pdo->prepare("INSERT INTO abertura_mesa SET mesa=:mesa, cliente=:cliente, data=CURDATE(), horario_abertura=CURTIME(), garcon=:garcon, status='Aberta', obs=:obs, pessoas=:pessoas, empresa=:empresa");
        $q->execute([':mesa'=>$mesa, ':cliente'=>$nome, ':garcon'=>$garcon, ':obs'=>$obs, ':pessoas'=>$pessoas, ':empresa'=>$empresa]);
    }
    $q = $pdo->prepare("UPDATE mesas SET status='Aberta' WHERE id=:mesa AND empresa=:empresa");
    $q->execute([':mesa'=>$mesa, ':empresa'=>$empresa]);
    $pdo->commit();
    echo 'Salvo com Sucesso';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Falha abertura/edição de mesa: '.$e->getMessage());
    http_response_code(409);
    echo 'Não foi possível salvar a mesa. Verifique se já está aberta e tente novamente.';
}
?>