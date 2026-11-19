<?php 
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'caixas';



$nivel_usuario = @$_SESSION['nivel'];

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id || !ctype_digit((string)$id_empresa) || (int)$id_empresa < 1 || !ctype_digit((string)($_SESSION['id'] ?? ''))) { http_response_code(403); exit('Sessão ou caixa inválido.'); }



try {
    $pdo->beginTransaction();
    $consulta=$pdo->prepare('SELECT operador,data_fechamento FROM caixas WHERE id=:id AND empresa=:empresa FOR UPDATE');
    $consulta->execute([':id'=>$id,':empresa'=>(int)$id_empresa]);
    $caixa=$consulta->fetch(PDO::FETCH_ASSOC);
    if (!$caixa || !empty($caixa['data_fechamento'])) throw new RuntimeException('Caixa não encontrado ou já fechado.');
    if ((int)$caixa['operador'] !== (int)$_SESSION['id'] && !in_array($nivel_usuario,['Administrador','Gerente'],true)) throw new RuntimeException('Operador não autorizado.');
    foreach (['receber','pagar','sangrias'] as $origem) {
        $consulta=$pdo->prepare("SELECT COUNT(*) FROM $origem WHERE caixa=:id AND empresa=:empresa");
        $consulta->execute([':id'=>$id,':empresa'=>(int)$id_empresa]);
        if ((int)$consulta->fetchColumn() > 0) throw new RuntimeException('Caixa com movimentações.');
    }
    $excluir=$pdo->prepare('DELETE FROM caixas WHERE id=:id AND empresa=:empresa AND data_fechamento IS NULL');
    $excluir->execute([':id'=>$id,':empresa'=>(int)$id_empresa]);
    if ($excluir->rowCount() !== 1) throw new RuntimeException('Exclusão não confirmada.');
    $pdo->commit();
} catch(Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Excluir caixa: '.$e->getMessage());
    http_response_code(409); exit('Não é possível excluir um caixa fechado ou com movimentações.');
}
echo 'Excluído com Sucesso';


?>