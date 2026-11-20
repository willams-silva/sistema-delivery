<?php
@session_start();
require_once("../../../conexao.php");
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$q = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$q->execute([':id'=>$usuario, ':empresa'=>$empresa]);
if ($q->fetchColumn() !== 'Administrador') { http_response_code(403); exit('Somente administradores podem alterar permissões'); }
$q = $pdo->prepare("SELECT alterar_acessos FROM config WHERE empresa = :empresa LIMIT 1");
$q->execute([':empresa'=>$empresa]);
if ($q->fetchColumn() !== 'Sim') { http_response_code(403); exit('Alteração de acessos desabilitada para esta empresa'); }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Acesso inválido'); }
try {
    $pdo->beginTransaction();
    $q = $pdo->prepare("SELECT id FROM acessos WHERE id = :id FOR UPDATE");
    $q->execute([':id'=>$id]);
    if (!$q->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Acesso não encontrado');
    }
    $q = $pdo->prepare("SELECT 1 FROM usuarios_permissoes WHERE permissao = :id LIMIT 1");
    $q->execute([':id'=>$id]);
    if ($q->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(409);
        exit('Acesso atribuído a usuários; remova os vínculos antes de excluir');
    }
    $q = $pdo->prepare("SELECT 1 FROM cargos_permissoes WHERE permissao_id = :id LIMIT 1");
    $q->execute([':id'=>$id]);
    if ($q->fetchColumn()) {
        $pdo->rollBack();
        http_response_code(409);
        exit('Acesso utilizado em modelos de cargos; remova os vínculos antes de excluir');
    }
    $q = $pdo->prepare("DELETE FROM acessos WHERE id = :id");
    $q->execute([':id'=>$id]);
    $pdo->commit();
    echo 'Excluído com Sucesso';
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Excluir acesso: '.$e->getMessage());
    http_response_code(503);
    echo 'Não foi possível excluir o acesso. Confira os vínculos e tente novamente.';
}
