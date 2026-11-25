<?php 
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, acesse novamente!';
	exit();
}
require_once("../../sistema/painel/buscar_config.php");

require_once __DIR__.'/frete_ruas_helper.php';
$taxa = bcFreteRua($pdo,(int)$id_empresa,(string)($_POST['bairro']??''),(string)($_POST['rua']??''),(string)($_POST['entrega']??''));
if ($taxa === null) { http_response_code(422); exit('Bairro inválido'); }
echo number_format($taxa,2,',','.').'-'.number_format((float)($_POST['total_compra']??0)+$taxa,2,',','.');
