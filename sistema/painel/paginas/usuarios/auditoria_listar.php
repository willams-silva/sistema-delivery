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
 $filtroUsuario=filter_var($_POST['usuario']??null,FILTER_VALIDATE_INT);
 $filtroAcao=trim((string)($_POST['acao']??''));
 $dataInicio=trim((string)($_POST['inicio']??''));
 $dataFim=trim((string)($_POST['fim']??''));
 $acoes=['conceder','remover','conceder_todos','remover_todos'];
 if ($filtroAcao!=='' && !in_array($filtroAcao,$acoes,true)) {http_response_code(400);exit(json_encode(['erro'=>'Ação inválida']));}
 foreach([$dataInicio,$dataFim] as $data) if($data!=='' && (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$data) || !DateTime::createFromFormat('!Y-m-d',$data) || DateTime::createFromFormat('!Y-m-d',$data)->format('Y-m-d')!==$data)) {http_response_code(400);exit(json_encode(['erro'=>'Data inválida']));}
 $sql='SELECT a.id,a.criado_em,a.acao,a.permissao,a.detalhes,COALESCE(u.nome,CONCAT("Usuário #",a.usuario_alvo)) AS alvo,COALESCE(o.nome,CONCAT("Operador #",a.operador)) AS operador_nome,ac.nome AS acesso_nome FROM auditoria_permissoes a LEFT JOIN usuarios u ON u.id=a.usuario_alvo AND u.empresa=a.empresa LEFT JOIN usuarios o ON o.id=a.operador AND o.empresa=a.empresa LEFT JOIN acessos ac ON ac.id=a.permissao WHERE a.empresa=:empresa';
 $params=[':empresa'=>$empresa];
 if($filtroUsuario){$sql.=' AND a.usuario_alvo=:usuario';$params[':usuario']=$filtroUsuario;}
 if($filtroAcao!==''){$sql.=' AND a.acao=:acao';$params[':acao']=$filtroAcao;}
 if($dataInicio!==''){$sql.=' AND a.criado_em>=:inicio';$params[':inicio']=$dataInicio.' 00:00:00';}
 if($dataFim!==''){$sql.=' AND a.criado_em<DATE_ADD(:fim,INTERVAL 1 DAY)';$params[':fim']=$dataFim;}
 $sql.=' ORDER BY a.id DESC LIMIT 100';
 $q=$pdo->prepare($sql);
 $q->execute($params);
 echo json_encode(['registros'=>$q->fetchAll(PDO::FETCH_ASSOC)],JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {error_log('Listar auditoria: '.$e->getMessage());http_response_code(503);echo json_encode(['erro'=>'Auditoria indisponível. Confira a instalação da tabela.']);}
