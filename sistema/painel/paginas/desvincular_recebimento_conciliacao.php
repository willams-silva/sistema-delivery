<?php
@session_start();require_once('../../conexao.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método inválido.');}
if(!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)){http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if($empresa<1||empty($_SESSION['csrf_conc'])||!hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))){http_response_code(403);exit('Sessão inválida.');}
$id=filter_var($_POST['vinculo_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$motivo=trim((string)($_POST['motivo']??''));
if(!$id||mb_strlen($motivo)<10||mb_strlen($motivo)>500){http_response_code(400);exit('Informe o vínculo e uma justificativa de 10 a 500 caracteres.');}
try{
 $pdo->beginTransaction();
 $st=$pdo->prepare('SELECT id,receber_id,liquidacao_id,valor_vinculado,usuario_id,criado_em FROM conciliacao_vinculos_recebimentos WHERE id=? AND empresa=? FOR UPDATE');
 $st->execute([$id,$empresa]);$v=$st->fetch(PDO::FETCH_ASSOC);
 if(!$v){$pdo->rollBack();http_response_code(409);exit('Vínculo não encontrado ou já desfeito.');}
 $lock=$pdo->prepare('SELECT id FROM conciliacao_liquidacoes WHERE id=? AND empresa=? FOR UPDATE');
 $lock->execute([$v['liquidacao_id'],$empresa]);
 if(!$lock->fetchColumn())throw new DomainException('Crédito não encontrado nesta empresa.');
 $aud=$pdo->prepare('INSERT INTO conciliacao_vinculos_auditoria (empresa,vinculo_id,receber_id,liquidacao_id,valor_vinculado,vinculado_por,vinculado_em,desvinculado_por,motivo) VALUES (?,?,?,?,?,?,?,?,?)');
 $aud->execute([$empresa,$v['id'],$v['receber_id'],$v['liquidacao_id'],$v['valor_vinculado'],$v['usuario_id'],$v['criado_em'],$usuario?:null,$motivo]);
 $del=$pdo->prepare('DELETE FROM conciliacao_vinculos_recebimentos WHERE id=? AND empresa=?');
 $del->execute([$id,$empresa]);
 if($del->rowCount()!==1)throw new RuntimeException('Falha ao remover vínculo.');
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&vinculo_desfeito=1',true,303);exit;
}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(422);exit($e->getMessage());}
catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Desvinculo conciliacao: '.$e->getMessage());http_response_code(500);exit('Falha ao desvincular. Verifique se a migração de auditoria foi executada.');}
