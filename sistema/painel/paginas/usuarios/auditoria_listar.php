<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(405);exit(json_encode(['erro'=>'Método não permitido']));}
require_once '../../../conexao.php';
$empresa=(int)($_SESSION['empresa']??0);$operador=(int)($_SESSION['id']??0);
if (!$empresa || !$operador) {http_response_code(403);exit(json_encode(['erro'=>'Sessão inválida']));}
$q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$operador,':empresa'=>$empresa]);
if ($q->fetchColumn()!=='Administrador') {http_response_code(403);exit(json_encode(['erro'=>'Acesso restrito']));}
try {
 $q=$pdo->prepare('SELECT a.id,a.criado_em,a.acao,a.permissao,a.detalhes,COALESCE(u.nome,CONCAT("Usuário #",a.usuario_alvo)) AS alvo,COALESCE(o.nome,CONCAT("Operador #",a.operador)) AS operador_nome,ac.nome AS acesso_nome FROM auditoria_permissoes a LEFT JOIN usuarios u ON u.id=a.usuario_alvo AND u.empresa=a.empresa LEFT JOIN usuarios o ON o.id=a.operador AND o.empresa=a.empresa LEFT JOIN acessos ac ON ac.id=a.permissao WHERE a.empresa=:empresa ORDER BY a.id DESC LIMIT 100');
 $q->execute([':empresa'=>$empresa]);
 echo json_encode(['registros'=>$q->fetchAll(PDO::FETCH_ASSOC)],JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {error_log('Listar auditoria: '.$e->getMessage());http_response_code(503);echo json_encode(['erro'=>'Auditoria indisponível. Confira a instalação da tabela.']);}
