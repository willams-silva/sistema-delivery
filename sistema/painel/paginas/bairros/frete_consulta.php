<?php
$chave = 'bairros';
@session_start();
require_once("../../../conexao.php");
$empresa = (int) ($_SESSION['empresa'] ?? 0);
$usuario = (int) ($_SESSION['id'] ?? 0);
if ($empresa <= 0 || $usuario <= 0) { http_response_code(403); exit('Sessão inválida'); }
$op = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$op->execute([':id'=>$usuario, ':empresa'=>$empresa]);
$nivel = $op->fetchColumn();
if ($nivel === false) { http_response_code(403); exit('Acesso negado'); }
if ($nivel !== 'Administrador') {
    $per = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = :chave LIMIT 1");
    $per->execute([':usuario'=>$usuario, ':empresa'=>$empresa, ':chave'=>$chave]);
    if (!$per->fetchColumn()) { http_response_code(403); exit('Sem permissão'); }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método não permitido'); }


header('Content-Type: application/json; charset=utf-8');
if (!hash_equals((string)($_SESSION['bc_frete_csrf'] ?? ''), (string)($_POST['csrf'] ?? '')) || empty($_SESSION['bc_frete_csrf'])) { http_response_code(403); exit('Sessão inválida'); }

$acao=$_POST['acao']??'listar';
try {
if($acao==='alertas'){
$q=$pdo->prepare("SELECT c.id FROM bc_frete_consultas c JOIN bc_frete_consulta_config f ON f.empresa=c.empresa AND f.ativo=1 WHERE c.empresa=? AND c.status='pendente' AND c.expira_em>NOW() ORDER BY c.id DESC");$q->execute([$empresa]);echo json_encode(['ids'=>$q->fetchAll(PDO::FETCH_COLUMN)]);exit;
}
if($acao==='listar'){
$q=$pdo->prepare("SELECT ativo FROM bc_frete_consulta_config WHERE empresa=?");$q->execute([$empresa]);$ativo=(int)$q->fetchColumn();
$q=$pdo->prepare("SELECT id,endereco,status,valor,criado_em,expira_em FROM bc_frete_consultas WHERE empresa=? AND expira_em>NOW() AND status IN ('pendente','cotado') ORDER BY criado_em DESC LIMIT 100");$q->execute([$empresa]);
echo json_encode(['ativo'=>$ativo,'consultas'=>$q->fetchAll(PDO::FETCH_ASSOC)]);exit;
}
if($acao==='config'){
$ativo=($_POST['ativo']??'')==='1'?1:0;
$q=$pdo->prepare("INSERT INTO bc_frete_consulta_config (empresa,ativo) VALUES (?,?) ON DUPLICATE KEY UPDATE ativo=VALUES(ativo)");$q->execute([$empresa,$ativo]);echo json_encode(['ok'=>true]);exit;
}
if($acao==='cotar'){
$valor=str_replace(',','.',trim((string)($_POST['valor']??'')));
if(!preg_match('/^\d+(?:\.\d{1,2})?$/',$valor)||(float)$valor>999999.99){http_response_code(400);exit(json_encode(['erro'=>'Valor inválido']));}
$q=$pdo->prepare("UPDATE bc_frete_consultas SET valor=?,operador=?,status='cotado' WHERE empresa=? AND id=? AND status='pendente' AND expira_em>NOW()");$q->execute([$valor,$usuario,$empresa,(int)($_POST['id']??0)]);
if(!$q->rowCount()){http_response_code(409);exit(json_encode(['erro'=>'Solicitação indisponível']));}echo json_encode(['ok'=>true]);exit;
}
http_response_code(400);echo json_encode(['erro'=>'Operação inválida']);
}catch(PDOException $e){http_response_code(500);echo json_encode(['erro'=>'Não foi possível processar a consulta']);}
