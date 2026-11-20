<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
function bcLoteResposta(int $codigo,array $dados): void {
 http_response_code($codigo);echo json_encode($dados,JSON_UNESCAPED_UNICODE);exit;
}
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') bcLoteResposta(405,['erro'=>'Método não permitido']);
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id']??0);
if (!$empresa || !$usuario) bcLoteResposta(403,['erro'=>'Sessão inválida']);
require_once '../../../conexao.php';
try {
 $q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:usuario AND empresa=:empresa LIMIT 1');
 $q->execute([':usuario'=>$usuario,':empresa'=>$empresa]);
 if ($q->fetchColumn()!=='Administrador') bcLoteResposta(403,['erro'=>'Somente administradores podem excluir grupos']);
 $q=$pdo->prepare('SELECT alterar_acessos FROM config WHERE empresa=:empresa LIMIT 1');
 $q->execute([':empresa'=>$empresa]);
 if ($q->fetchColumn()!=='Sim') bcLoteResposta(403,['erro'=>'Alteração de acessos desabilitada']);
 $ids=$_POST['ids']??null;
 if (!is_array($ids) || !$ids || count($ids)>100) bcLoteResposta(400,['erro'=>'Selecione de 1 a 100 grupos']);
 $idsValidos=[];
 foreach($ids as $id) {
  if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/',(string)$id)) bcLoteResposta(400,['erro'=>'Identificador inválido']);
  $idsValidos[]=(int)$id;
 }
 $idsValidos=array_values(array_unique($idsValidos));
 $marcadores=implode(',',array_fill(0,count($idsValidos),'?'));
 $pdo->beginTransaction();
 $q=$pdo->prepare("SELECT id FROM grupo_acessos WHERE id IN ($marcadores) FOR UPDATE");
 $q->execute($idsValidos);
 $existentes=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
 if (count($existentes)!==count($idsValidos)) {
  $pdo->rollBack();bcLoteResposta(409,['erro'=>'Um ou mais grupos não existem. Atualize a listagem.']);
 }
 $q=$pdo->prepare("SELECT COUNT(*) FROM acessos WHERE grupo IN ($marcadores)");
 $q->execute($idsValidos);
 if ((int)$q->fetchColumn()>0) {
  $pdo->rollBack();bcLoteResposta(409,['erro'=>'Há acessos vinculados aos grupos selecionados. Nenhum grupo foi excluído.']);
 }
 $q=$pdo->prepare("DELETE FROM grupo_acessos WHERE id IN ($marcadores)");
 $q->execute($idsValidos);
 $excluidos=$q->rowCount();
 $pdo->commit();
 bcLoteResposta(200,['sucesso'=>true,'excluidos'=>$excluidos]);
} catch (PDOException $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Excluir grupos em lote: '.$e->getMessage());
 bcLoteResposta(503,['erro'=>'Não foi possível excluir os grupos. Nenhuma exclusão parcial foi confirmada.']);
}
