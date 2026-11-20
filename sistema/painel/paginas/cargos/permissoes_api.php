<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
function bcCargoResp(int $status,array $dados): void {http_response_code($status);echo json_encode($dados,JSON_UNESCAPED_UNICODE);exit;}
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') bcCargoResp(405,['erro'=>'Método não permitido']);
require_once '../../../conexao.php';
$empresa=(int)($_SESSION['empresa']??0);$operador=(int)($_SESSION['id']??0);
if(!$empresa||!$operador)bcCargoResp(403,['erro'=>'Sessão inválida']);
$q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$operador,':empresa'=>$empresa]);
if($q->fetchColumn()!=='Administrador')bcCargoResp(403,['erro'=>'Somente administradores podem gerenciar modelos de permissões']);
$q=$pdo->prepare('SELECT alterar_acessos FROM config WHERE empresa=:empresa LIMIT 1');
$q->execute([':empresa'=>$empresa]);
if($q->fetchColumn()!=='Sim')bcCargoResp(403,['erro'=>'Alteração de acessos desabilitada']);
$cargo=filter_var($_POST['cargo']??null,FILTER_VALIDATE_INT);
if(!$cargo||$cargo<=0)bcCargoResp(400,['erro'=>'Cargo inválido']);
$q=$pdo->prepare('SELECT nome FROM cargos WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$cargo,':empresa'=>$empresa]);
$nome=$q->fetchColumn();
if($nome===false)bcCargoResp(404,['erro'=>'Cargo não encontrado nesta empresa']);
$acao=(string)($_POST['acao']??'listar');
try {
 if($acao==='listar') {
  $q=$pdo->prepare('SELECT permissao_id FROM cargos_permissoes WHERE empresa=:empresa AND cargo_id=:cargo');
  $q->execute([':empresa'=>$empresa,':cargo'=>$cargo]);
  $selecionadas=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
  $q=$pdo->query('SELECT a.id,a.nome,COALESCE(g.nome,"Sem grupo") AS grupo FROM acessos a LEFT JOIN grupo_acessos g ON g.id=a.grupo ORDER BY g.nome,a.nome');
  bcCargoResp(200,['cargo'=>$nome,'acessos'=>$q->fetchAll(PDO::FETCH_ASSOC),'selecionadas'=>$selecionadas]);
 }
 if($acao!=='salvar')bcCargoResp(400,['erro'=>'Operação inválida']);
 $ids=$_POST['permissoes']??[];
 if(!is_array($ids)||count($ids)>500)bcCargoResp(400,['erro'=>'Lista de permissões inválida']);
 $ids=array_values(array_unique(array_map('intval',$ids)));
 foreach($ids as $id)if($id<=0)bcCargoResp(400,['erro'=>'Acesso inválido']);
 if($ids) {
  $marcadores=implode(',',array_fill(0,count($ids),'?'));
  $q=$pdo->prepare("SELECT COUNT(*) FROM acessos WHERE id IN ($marcadores)");
  $q->execute($ids);
  if((int)$q->fetchColumn()!==count($ids))bcCargoResp(400,['erro'=>'Há acessos inexistentes na seleção']);
 }
 $pdo->beginTransaction();
 $q=$pdo->prepare('DELETE FROM cargos_permissoes WHERE empresa=:empresa AND cargo_id=:cargo');
 $q->execute([':empresa'=>$empresa,':cargo'=>$cargo]);
 $q=$pdo->prepare('INSERT INTO cargos_permissoes (empresa,cargo_id,permissao_id) VALUES (:empresa,:cargo,:permissao)');
 foreach($ids as $id)$q->execute([':empresa'=>$empresa,':cargo'=>$cargo,':permissao'=>$id]);
 $pdo->commit();
 bcCargoResp(200,['sucesso'=>true,'total'=>count($ids),'mensagem'=>'Modelo salvo. Nenhuma permissão de funcionário foi alterada.']);
} catch(PDOException $e) {
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('Modelos de permissão por cargo: '.$e->getMessage());
 bcCargoResp(503,['erro'=>'Não foi possível acessar os modelos. Confira a instalação da tabela cargos_permissoes.']);
}
