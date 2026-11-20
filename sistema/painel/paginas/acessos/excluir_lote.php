<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
function bcRespostaLote(int $status,array $dados): void {http_response_code($status);echo json_encode($dados,JSON_UNESCAPED_UNICODE);exit;}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST')bcRespostaLote(405,['erro'=>'Método não permitido']);
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id']??0);
if(!$empresa||!$usuario)bcRespostaLote(403,['erro'=>'Sessão inválida']);
require_once '../../../conexao.php';
try{
 $q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
 $q->execute([':id'=>$usuario,':empresa'=>$empresa]);
 if($q->fetchColumn()!=='Administrador')bcRespostaLote(403,['erro'=>'Somente administradores podem excluir acessos']);
 $q=$pdo->prepare('SELECT alterar_acessos FROM config WHERE empresa=:empresa LIMIT 1');
 $q->execute([':empresa'=>$empresa]);
 if($q->fetchColumn()!=='Sim')bcRespostaLote(403,['erro'=>'Alteração de acessos desabilitada']);
 $ids=$_POST['ids']??null;
 if(!is_array($ids)||count($ids)<1||count($ids)>100)bcRespostaLote(400,['erro'=>'Selecione de 1 a 100 acessos']);
 $validos=[];
 foreach($ids as $id){
  if(!is_scalar($id)||!preg_match('/^[1-9][0-9]*$/',(string)$id))bcRespostaLote(400,['erro'=>'Identificador de acesso inválido']);
  $validos[]=(int)$id;
 }
 $validos=array_values(array_unique($validos));
 $placeholders=implode(',',array_fill(0,count($validos),'?'));
 $pdo->beginTransaction();
 $q=$pdo->prepare("SELECT id FROM acessos WHERE id IN ($placeholders) ORDER BY id FOR UPDATE");
 $q->execute($validos);
 $encontrados=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
 if(count($encontrados)!==count($validos)){
  $pdo->rollBack();bcRespostaLote(409,['erro'=>'Algum acesso não existe mais. Atualize a listagem. Nenhum acesso foi excluído.']);
 }
 $q=$pdo->prepare("SELECT COUNT(*) FROM usuarios_permissoes WHERE permissao IN ($placeholders)");
 $q->execute($validos);
 if((int)$q->fetchColumn()>0){
  $pdo->rollBack();bcRespostaLote(409,['erro'=>'Existem permissões de usuários vinculadas. Nenhum acesso foi excluído.']);
 }
 $q=$pdo->prepare("SELECT COUNT(*) FROM cargos_permissoes WHERE permissao_id IN ($placeholders)");
 $q->execute($validos);
 if((int)$q->fetchColumn()>0){
  $pdo->rollBack();bcRespostaLote(409,['erro'=>'Existem modelos de cargos vinculados. Nenhum acesso foi excluído.']);
 }
 $q=$pdo->prepare("DELETE FROM acessos WHERE id IN ($placeholders)");
 $q->execute($validos);
 $excluidos=$q->rowCount();
 if($excluidos!==count($validos))throw new RuntimeException('Quantidade excluída divergente');
 $pdo->commit();
 bcRespostaLote(200,['sucesso'=>true,'excluidos'=>$excluidos]);
}catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('Exclusão em lote de acessos: '.$e->getMessage());
 bcRespostaLote(503,['erro'=>'Não foi possível excluir os acessos. Nenhuma exclusão parcial foi confirmada.']);
}
