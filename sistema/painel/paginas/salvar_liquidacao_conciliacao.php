<?php
@session_start();
require_once('../../conexao.php');
if (!in_array($_SESSION['nivel'] ?? '', ['Administrador','Gerente'], true)) {http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if ($empresa<=0 || ($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(400);exit('Requisição inválida.');}
if (empty($_SESSION['csrf_conc']) || !hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))) {http_response_code(403);exit('Sessão expirada.');}
$forma=filter_var($_POST['forma_pgto']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$dataRef=(string)($_POST['data_referencia']??'');
$dataCred=(string)($_POST['data_credito']??'');
$val=(string)($_POST['valor_creditado']??'');
$idf=trim((string)($_POST['identificador']??''));
$obs=trim((string)($_POST['observacao']??''));
$validaData=static function($d){return preg_match('/^\d{4}-\d{2}-\d{2}$/',$d) && checkdate((int)substr($d,5,2),(int)substr($d,8,2),(int)substr($d,0,4));};
if (!$forma || !$validaData($dataRef) || !$validaData($dataCred) || !preg_match('/^\d{1,10}(?:[.,]\d{1,2})?$/',$val) || (float)str_replace(',','.',$val)<=0 || mb_strlen($idf)>120 || mb_strlen($obs)>500) {
 http_response_code(400);exit('Confira os campos da liquidação.');
}
$valor=number_format((float)str_replace(',','.',$val),2,'.','');
try {
 $validar=$pdo->prepare('SELECT id FROM formas_pgto WHERE id=:forma AND empresa=:empresa LIMIT 1');
 $validar->execute([':forma'=>$forma,':empresa'=>$empresa]);
 if (!$validar->fetchColumn()) {http_response_code(403);exit('Forma de pagamento inválida.');}
 // Verificação preventiva de duplicidade. Sem índice único, não substitui proteção transacional no banco.
 $duplicado=$pdo->prepare("SELECT id FROM conciliacao_liquidacoes
 WHERE empresa=:empresa AND forma_pgto=:forma AND cancelado_em IS NULL
 AND ((:idf1 <> '' AND identificador=:idf2)
      OR (data_referencia=:ref AND data_credito=:credito AND valor_creditado=:valor
          AND (:idf3 = '' OR identificador IS NULL OR identificador = '' OR identificador=:idf4)))
 LIMIT 1");
 $duplicado->execute([':empresa'=>$empresa,':forma'=>$forma,':idf1'=>$idf,':idf2'=>$idf,':idf3'=>$idf,':idf4'=>$idf,
 ':ref'=>$dataRef,':credito'=>$dataCred,':valor'=>$valor]);
 $idDuplicado=$duplicado->fetchColumn();
 if ($idDuplicado) {
  header('Location: ../index.php?pagina=conciliacao_financeira&aba=liquidacoes&liquidacao_duplicada=1&inicio='.rawurlencode($dataRef).'&fim='.rawurlencode($dataRef),true,303);
  exit;
 }
 $st=$pdo->prepare('INSERT INTO conciliacao_liquidacoes (empresa,forma_pgto,data_referencia,data_credito,valor_creditado,identificador,observacao,usuario_id) VALUES (:empresa,:forma,:ref,:credito,:valor,:identificador,:obs,:usuario)');
 $st->execute([':empresa'=>$empresa,':forma'=>$forma,':ref'=>$dataRef,':credito'=>$dataCred,':valor'=>$valor,':identificador'=>$idf?:null,':obs'=>$obs?:null,':usuario'=>$usuario?:null]);
 header('Location: ../index.php?pagina=conciliacao_financeira&inicio='.rawurlencode($dataRef).'&fim='.rawurlencode($dataRef).'&liquidacao_salva=1',true,303);
 exit;
} catch(Throwable $e) {
 error_log('Falha ao registrar liquidacao: '.$e->getMessage());
 http_response_code(500);exit('Falha ao registrar. Confira a migração SQL.');
}
