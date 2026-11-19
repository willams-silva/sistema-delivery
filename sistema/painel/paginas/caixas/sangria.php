<?php
@session_start();
require_once('../../../conexao.php');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método inválido.'); }
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$caixa=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$valor=str_replace(',','.',(string)($_POST['valor_sangria']??''));
if (!$empresa || !$usuario || !$caixa || !is_numeric($valor) || !is_finite((float)$valor) || (float)$valor<=0) {
    http_response_code(400); exit('Caixa ou valor de sangria inválido.');
}
try {
    $pdo->beginTransaction();
    $st=$pdo->prepare('SELECT operador,data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $st->execute([':id'=>$caixa,':empresa'=>$empresa]);
    $registro=$st->fetch(PDO::FETCH_ASSOC);
    if (!$registro || !empty($registro['data_fechamento'])) throw new RuntimeException('Caixa inexistente ou fechado.');
    if ((int)$registro['operador']!==$usuario && !in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)) {
        throw new RuntimeException('Operador sem permissão.');
    }
    $st=$pdo->prepare('INSERT INTO sangrias (usuario,valor,data,hora,caixa,feito_por) VALUES (:usuario,:valor,CURDATE(),CURTIME(),:caixa,:feito_por)');
    $st->execute([':usuario'=>$usuario,':valor'=>round((float)$valor,2),':caixa'=>$caixa,':feito_por'=>$usuario]);
    $pdo->commit();
    echo 'Salvo com Sucesso';
} catch(Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Sangria de caixa: '.$e->getMessage());
    http_response_code(409); echo 'Não foi possível registrar a sangria. Verifique o caixa e suas permissões.';
}
