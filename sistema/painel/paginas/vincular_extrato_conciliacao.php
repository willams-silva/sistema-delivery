<?php
@session_start();require_once('../../conexao.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);exit('Método inválido.');}
if(!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)){http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if($empresa<1||empty($_SESSION['csrf_conc'])||!hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))){http_response_code(403);exit('Sessão inválida.');}
$linha=filter_var($_POST['extrato_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$credito=filter_var($_POST['liquidacao_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$linha||!$credito){http_response_code(400);exit('Informe a movimentação e o crédito.');}
try{
 $pdo->beginTransaction();
 $s=$pdo->prepare('SELECT id,valor,liquidacao_id FROM conciliacao_extrato_linhas WHERE id=? AND empresa=? FOR UPDATE');
 $s->execute([$linha,$empresa]);$e=$s->fetch(PDO::FETCH_ASSOC);
 $s=$pdo->prepare('SELECT id,valor_creditado,cancelado_em FROM conciliacao_liquidacoes WHERE id=? AND empresa=? FOR UPDATE');
 $s->execute([$credito,$empresa]);$l=$s->fetch(PDO::FETCH_ASSOC);
 if(!$e||!$l||$e['liquidacao_id']!==null||$l['cancelado_em']!==null)throw new DomainException('Movimentação já vinculada, crédito cancelado ou registro inexistente.');
 if((float)$e['valor']<=0)throw new DomainException('Apenas créditos positivos do extrato podem ser vinculados.');
 // Um crédito corresponde a uma única linha bancária nesta etapa.
 $s=$pdo->prepare('SELECT id FROM conciliacao_extrato_linhas WHERE empresa=? AND liquidacao_id=? LIMIT 1');
 $s->execute([$empresa,$credito]);if($s->fetchColumn())throw new DomainException('Este crédito já está vinculado a outra movimentação bancária.');
 $centavosExtrato=(int)round((float)$e['valor']*100);$centavosCredito=(int)round((float)$l['valor_creditado']*100);
 if($centavosExtrato!==$centavosCredito)throw new DomainException('Valores diferentes. Revise o lançamento antes de vincular.');
 $s=$pdo->prepare('INSERT INTO conciliacao_extrato_vinculos_auditoria (empresa,extrato_linha_id,liquidacao_id,usuario_id,valor_extrato,valor_credito) VALUES (?,?,?,?,?,?)');
 $s->execute([$empresa,$linha,$credito,$e['valor'],$l['valor_creditado']]);
 $s=$pdo->prepare('UPDATE conciliacao_extrato_linhas SET liquidacao_id=? WHERE id=? AND empresa=? AND liquidacao_id IS NULL');
 $s->execute([$credito,$linha,$empresa]);if($s->rowCount()!==1)throw new RuntimeException('Falha na atualização do vínculo.');
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&extrato_vinculado=1',true,303);exit;
}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(422);exit($e->getMessage());}
catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Vinculo extrato: '.$e->getMessage());http_response_code(500);exit('Não foi possível vincular. Confira a migração SQL e tente novamente.');}
