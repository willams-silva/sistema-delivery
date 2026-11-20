<?php
require_once __DIR__.'/permissoes_guard.php';
require_once __DIR__.'/auditoria_helper.php';
header('Content-Type: application/json; charset=utf-8');
function bcModeloResp(int $status,array $data): void {http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
$cargo=filter_var($_POST['cargo']??null,FILTER_VALIDATE_INT);
$acao=(string)($_POST['acao']??'');
if(!$cargo || $cargo<=0 || !in_array($acao,['prever','aplicar'],true))bcModeloResp(400,['erro'=>'Solicitação inválida']);
$q=$pdo->prepare('SELECT nome FROM cargos WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$cargo,':empresa'=>$bcEmpresa]);
$nome=$q->fetchColumn();
if($nome===false)bcModeloResp(404,['erro'=>'Cargo não encontrado']);
if($nome!==$bcAlvoNivel)bcModeloResp(409,['erro'=>'O cargo selecionado não corresponde ao cargo atual do usuário']);
if($bcAlvoNivel==='Administrador')bcModeloResp(409,['erro'=>'Não é permitido substituir as permissões de administradores por um modelo de cargo']);
try {
 $pdo->beginTransaction();
 // Serializa alterações de modelo e aplicação por meio do registro do cargo.
 $q=$pdo->prepare('SELECT nome FROM cargos WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $q->execute([':id'=>$cargo,':empresa'=>$bcEmpresa]);
 if($q->fetchColumn()!==$nome){$pdo->rollBack();bcModeloResp(409,['erro'=>'Cargo alterado durante a operação']);}
 $q=$pdo->prepare('SELECT permissao_id FROM cargos_permissoes WHERE empresa=:empresa AND cargo_id=:cargo ORDER BY permissao_id');
 $q->execute([':empresa'=>$bcEmpresa,':cargo'=>$cargo]);
 $modelo=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
 if(!$modelo){$pdo->rollBack();bcModeloResp(409,['erro'=>'O modelo do cargo está vazio. Configure os acessos antes de aplicar.']);}
 $q=$pdo->prepare('SELECT permissao FROM usuarios_permissoes WHERE usuario=:usuario ORDER BY permissao');
 $q->execute([':usuario'=>$bcUsuario]);
 $atuais=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
 $adicionar=array_values(array_diff($modelo,$atuais));
 $remover=array_values(array_diff($atuais,$modelo));
 if($acao==='prever'){
  $pdo->rollBack();
  bcModeloResp(200,['cargo'=>$nome,'total_modelo'=>count($modelo),'adicionar'=>count($adicionar),'remover'=>count($remover),'mantidas'=>count(array_intersect($modelo,$atuais))]);
 }
 $confirmar=(string)($_POST['confirmar']??'');
 if($confirmar!=='APLICAR'){$pdo->rollBack();bcModeloResp(400,['erro'=>'Confirmação obrigatória']);}
 // Revalida o vínculo sob bloqueio, antes de modificar permissões.
 $q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa FOR UPDATE');
 $q->execute([':id'=>$bcUsuario,':empresa'=>$bcEmpresa]);
 if($q->fetchColumn()!==$nome){$pdo->rollBack();bcModeloResp(409,['erro'=>'Cargo do usuário foi alterado']);}
 $q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario');
 $q->execute([':usuario'=>$bcUsuario]);
 $q=$pdo->prepare('INSERT INTO usuarios_permissoes (usuario,permissao) VALUES (:usuario,:permissao)');
 foreach($modelo as $id)$q->execute([':usuario'=>$bcUsuario,':permissao'=>$id]);
 bcAuditarPermissao($pdo,$bcEmpresa,$bcOperador,$bcUsuario,'aplicar_cargo',null,json_encode(['cargo_id'=>$cargo,'cargo'=>$nome,'adicionadas'=>count($adicionar),'removidas'=>count($remover),'total'=>count($modelo)],JSON_UNESCAPED_UNICODE));
 $pdo->commit();
 bcModeloResp(200,['sucesso'=>true,'mensagem'=>'Modelo aplicado ao usuário e registrado na auditoria.']);
} catch(PDOException $e){
 if($pdo->inTransaction())$pdo->rollBack();
 error_log('Aplicação de modelo por cargo: '.$e->getMessage());
 bcModeloResp(503,['erro'=>'Não foi possível aplicar o modelo. Nenhuma alteração foi confirmada.']);
}
