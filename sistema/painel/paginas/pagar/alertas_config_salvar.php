<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$empresa||!$usuario){http_response_code(403);echo json_encode(['erro'=>'Sessão inválida']);exit;}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){http_response_code(405);echo json_encode(['erro'=>'Método inválido']);exit;}
$dias=filter_var($_POST['dias']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>30]]);
if($dias===false){http_response_code(400);echo json_encode(['erro'=>'Informe de 1 a 30 dias']);exit;}
require_once '../../../conexao.php';
try{
 $st=$pdo->prepare('INSERT INTO alertas_vencimento_pagar (empresa,dias_antecedencia) VALUES (:empresa,:dias) ON DUPLICATE KEY UPDATE dias_antecedencia=VALUES(dias_antecedencia)');
 $st->execute([':empresa'=>$empresa,':dias'=>$dias]);
 echo json_encode(['ok'=>true,'dias'=>$dias]);
}catch(Throwable $e){error_log('Configurar alertas pagar: '.$e->getMessage());http_response_code(500);echo json_encode(['erro'=>'Não foi possível salvar. Verifique a migração SQL.']);}
