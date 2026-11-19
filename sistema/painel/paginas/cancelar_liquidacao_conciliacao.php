<?php
@session_start();
require_once('../../conexao.php');
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(405);exit('Método inválido.');}
if (!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)) {http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$motivo=trim((string)($_POST['motivo']??''));
if ($empresa<=0||!$id||mb_strlen($motivo)<10||mb_strlen($motivo)>500) {http_response_code(400);exit('Informe uma justificativa de 10 a 500 caracteres.');}
if (empty($_SESSION['csrf_conc'])||!hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))) {http_response_code(403);exit('Sessão expirada.');}
try {
 $pdo->beginTransaction();
 $lock=$pdo->prepare("SELECT id,cancelado_em FROM conciliacao_liquidacoes WHERE id=:id AND empresa=:empresa FOR UPDATE");
 $lock->execute([':id'=>$id,':empresa'=>$empresa]);$credito=$lock->fetch(PDO::FETCH_ASSOC);
 if(!$credito||$credito['cancelado_em']!==null){$pdo->rollBack();http_response_code(409);exit('Crédito inexistente ou já cancelado.');}
 $vinculos=$pdo->prepare("SELECT COUNT(*) FROM conciliacao_vinculos_recebimentos WHERE empresa=:empresa AND liquidacao_id=:id");
 $vinculos->execute([':empresa'=>$empresa,':id'=>$id]);
 if((int)$vinculos->fetchColumn()>0){$pdo->rollBack();http_response_code(409);exit('Este crédito possui recebimentos vinculados. Revise os vínculos antes de cancelar. Nenhum dado foi alterado.');}
 $extratos=$pdo->prepare("SELECT COUNT(*) FROM conciliacao_extrato_linhas WHERE empresa=:empresa AND liquidacao_id=:id");
 $extratos->execute([':empresa'=>$empresa,':id'=>$id]);
 if((int)$extratos->fetchColumn()>0){$pdo->rollBack();http_response_code(409);exit('Este crédito possui movimentação bancária vinculada. Desvincule o extrato com justificativa antes de cancelar. Nenhum dado foi alterado.');}
 $st=$pdo->prepare("UPDATE conciliacao_liquidacoes SET cancelado_em=NOW(),cancelado_por=:usuario,motivo_cancelamento=:motivo WHERE id=:id AND empresa=:empresa AND cancelado_em IS NULL");
 $st->execute([':usuario'=>$usuario?:null,':motivo'=>$motivo,':id'=>$id,':empresa'=>$empresa]);
 if($st->rowCount()!==1){$pdo->rollBack();http_response_code(409);exit('Crédito inexistente ou já cancelado.');}
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&liquidacao_cancelada=1',true,303);exit;
} catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();error_log('Erro ao cancelar liquidacao: '.$e->getMessage());http_response_code(500);exit('Falha no cancelamento. Confira a migração SQL.');}
