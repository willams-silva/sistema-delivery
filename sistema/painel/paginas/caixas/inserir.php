<?php
@session_start();
require_once('../../../conexao.php');
require_once('../../buscar_config.php');
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') { http_response_code(405); exit('Método inválido.'); }
$empresa=filter_var($_SESSION['empresa']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$usuario=filter_var($_SESSION['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$operador=filter_var($_POST['operador']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$idRaw=$_POST['id']??'';
$id=$idRaw===''?null:filter_var($idRaw,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$valor=str_replace(',','.',(string)($_POST['valor_abertura']??''));
$data=(string)($_POST['data_abertura']??'');
$obs=trim((string)($_POST['obs']??''));
$dt=DateTimeImmutable::createFromFormat('!Y-m-d',$data);
if (!$empresa || !$usuario || !$operador || ($idRaw!==''&&!$id) || !is_numeric($valor) || !is_finite((float)$valor) || (float)$valor<0 || !$dt || $dt->format('Y-m-d')!==$data) {
    http_response_code(400); exit('Dados de abertura inválidos.');
}
if ($operador!==$usuario && !in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)) {
    http_response_code(403); exit('Operador não autorizado.');
}
try {
    $pdo->beginTransaction();
    $st=$pdo->prepare("SELECT id FROM usuarios WHERE id=:operador AND empresa=:empresa LIMIT 1");
    $st->execute([':operador'=>$operador,':empresa'=>$empresa]);
    if (!$st->fetchColumn()) throw new RuntimeException('Operador não pertence à empresa.');
    if ($id===null) {
        $st=$pdo->prepare('SELECT id FROM caixas WHERE operador=:operador AND empresa=:empresa AND data_fechamento IS NULL LIMIT 1 FOR UPDATE');
        $st->execute([':operador'=>$operador,':empresa'=>$empresa]);
        if ($st->fetchColumn()) throw new RuntimeException('Operador já possui caixa aberto.');
        $st=$pdo->prepare('INSERT INTO caixas (operador,data_abertura,valor_abertura,usuario_abertura,obs,empresa) VALUES (:operador,:data,:valor,:usuario,:obs,:empresa)');
        $st->execute([':operador'=>$operador,':data'=>$data,':valor'=>round((float)$valor,2),':usuario'=>$usuario,':obs'=>$obs,':empresa'=>$empresa]);
    } else {
        $st=$pdo->prepare('SELECT operador,data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa FOR UPDATE');
        $st->execute([':id'=>$id,':empresa'=>$empresa]);
        $caixa=$st->fetch(PDO::FETCH_ASSOC);
        if (!$caixa || !empty($caixa['data_fechamento'])) throw new RuntimeException('Caixa fechado ou não encontrado.');
        foreach (['receber','pagar','sangrias'] as $tabela) {
            $sql = $tabela === 'sangrias' ? "SELECT COUNT(*) FROM sangrias s INNER JOIN caixas c ON c.id=s.caixa WHERE c.id=:id AND c.empresa=:empresa" : "SELECT COUNT(*) FROM $tabela WHERE caixa=:id AND empresa=:empresa";
            $verifica=$pdo->prepare($sql);
            $verifica->execute([':id'=>$id,':empresa'=>$empresa]);
            if ((int)$verifica->fetchColumn()>0) throw new RuntimeException('Caixa com movimentações não pode ter abertura alterada.');
        }
        $st=$pdo->prepare('UPDATE caixas SET operador=:operador,data_abertura=:data,valor_abertura=:valor,obs=:obs WHERE id=:id AND empresa=:empresa AND data_fechamento IS NULL');
        $st->execute([':operador'=>$operador,':data'=>$data,':valor'=>round((float)$valor,2),':obs'=>$obs,':id'=>$id,':empresa'=>$empresa]);
    }
    $pdo->commit();
    echo 'Salvo com Sucesso';
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    error_log('Abertura de caixa: '.$e->getMessage());
    http_response_code(409); echo 'Não foi possível salvar o caixa. Verifique operador, status e movimentações.';
}
