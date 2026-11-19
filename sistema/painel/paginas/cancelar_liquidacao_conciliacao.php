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
 $st=$pdo->prepare("UPDATE conciliacao_liquidacoes
 SET cancelado_em=NOW(),cancelado_por=:usuario,motivo_cancelamento=:motivo
 WHERE id=:id AND empresa=:empresa AND cancelado_em IS NULL");
 $st->execute([':usuario'=>$usuario?:null,':motivo'=>$motivo,':id'=>$id,':empresa'=>$empresa]);
 if ($st->rowCount()!==1) {http_response_code(409);exit('Crédito inexistente ou já cancelado.');}
 header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&liquidacao_cancelada=1',true,303);exit;
} catch(Throwable $e) {error_log('Erro ao cancelar liquidacao: '.$e->getMessage());http_response_code(500);exit('Falha no cancelamento. Confira a migração SQL.');}
