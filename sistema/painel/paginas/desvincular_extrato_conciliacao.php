<?php
@session_start();require_once('../../conexao.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método inválido.');}
if(!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)){http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if($empresa<1||empty($_SESSION['csrf_conc'])||!hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))){http_response_code(403);exit('Sessão inválida.');}
$id=filter_var($_POST['extrato_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$motivo=trim((string)($_POST['motivo']??''));
if(!$id||mb_strlen($motivo)<10||mb_strlen($motivo)>500){http_response_code(400);exit('Informe a movimentação e justificativa de 10 a 500 caracteres.');}
try{
 $pdo->beginTransaction();
 $st=$pdo->prepare('SELECT id,valor,liquidacao_id FROM conciliacao_extrato_linhas WHERE id=? AND empresa=? FOR UPDATE');
 $st->execute([$id,$empresa]);$e=$st->fetch(PDO::FETCH_ASSOC);
 if(!$e||$e['liquidacao_id']===null){$pdo->rollBack();http_response_code(409);exit('Movimentação inexistente ou já desvinculada.');}
 $st=$pdo->prepare('SELECT id FROM conciliacao_liquidacoes WHERE id=? AND empresa=? FOR UPDATE');
 $st->execute([$e['liquidacao_id'],$empresa]);if(!$st->fetchColumn())throw new DomainException('Crédito inexistente nesta empresa.');
 $st=$pdo->prepare('INSERT INTO conciliacao_extrato_desvinculos_auditoria (empresa,extrato_linha_id,liquidacao_id,valor_extrato,usuario_id,motivo) VALUES (?,?,?,?,?,?)');
 $st->execute([$empresa,$id,$e['liquidacao_id'],$e['valor'],$usuario?:null,$motivo]);
 $st=$pdo->prepare('UPDATE conciliacao_extrato_linhas SET liquidacao_id=NULL WHERE id=? AND empresa=? AND liquidacao_id=?');
 $st->execute([$id,$empresa,$e['liquidacao_id']]);
 if($st->rowCount()!==1)throw new RuntimeException('Não foi possível desfazer o vínculo.');
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&extrato_desvinculado=1',true,303);exit;
}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(422);exit($e->getMessage());}
catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Desvinculo extrato: '.$e->getMessage());http_response_code(500);exit('Falha ao desvincular. Confira a migração SQL.');}
