<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'receber';

$id_usuario = $_SESSION['id'];

$id = $_POST['id-baixar'];
$data_atual = date('Y-m-d');
	$dia = date('d');
	$mes = date('m');
	$ano = date('Y');

$valor = $_POST['valor-baixar'];
$valor = str_replace(',', '.', $valor);

$taxa = $_POST['valor-taxa'];
$taxa = str_replace(',', '.', $taxa);

$multa = $_POST['valor-multa'];
$multa = str_replace(',', '.', $multa);

$desconto = $_POST['valor-desconto'];
$desconto = str_replace(',', '.', $desconto);

$juros = $_POST['valor-juros'];
$juros = str_replace(',', '.', $juros);

$valor = $_POST['valor-baixar'];
$valor = str_replace(',', '.', $valor);
$valor_padrao = $valor;

$subtotal = $_POST['subtotal'];
$subtotal = str_replace(',', '.', $subtotal);

$saida = $_POST['saida-baixar'];
$data_baixar = $_POST['data-baixar'];

if($juros == ""){
	$juros = 0;
}

if($multa == ""){
	$multa = 0;
}
if($taxa == ""){
	$taxa = 0;
}
if($desconto == ""){
	$desconto = 0;
}


// Validação e bloqueio transacional impedem baixa de outra empresa e duplicações.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método inválido.'); }
if (!ctype_digit((string)$id) || (int)$id < 1 || !ctype_digit((string)$id_empresa) || (int)$id_empresa < 1 || !ctype_digit((string)$id_usuario) || (int)$id_usuario < 1) {
    http_response_code(400); exit('Conta ou sessão inválida.');
}
require_once __DIR__ . '/../../includes/auditoria_financeira.php';
try {
$pdo->beginTransaction();
$consultaConta = $pdo->prepare("SELECT * FROM receber WHERE id=:id AND empresa=:empresa FOR UPDATE");
$consultaConta->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
$res = $consultaConta->fetchAll(PDO::FETCH_ASSOC);
if (!$res) throw new RuntimeException('Conta não encontrada neste estabelecimento.');
if (($res[0]['pago'] ?? '') === 'Sim') throw new RuntimeException('Esta conta já foi recebida.');
$auditoriaAntes = array_intersect_key($res[0], array_flip(['descricao','valor','vencimento','data_pgto','forma_pgto','pago','subtotal','frequencia']));

$descricao = $res[0]['descricao'];
$cliente = $res[0]['cliente'];
$valor_antigo = $res[0]['valor'];
$data_lanc = $res[0]['data_lanc'];
$data_venc = $res[0]['vencimento'];
$data_pgto = $res[0]['data_pgto'];
$usuario_lanc = $res[0]['usuario_lanc'];
$usuario_pgto = $res[0]['usuario_pgto'];
$frequencia = $res[0]['frequencia'];
$saida_antiga = $res[0]['forma_pgto'];
$arquivo = $res[0]['arquivo'];
$pago = $res[0]['pago'];
$referencia = $res[0]['referencia'];
$hash = $res[0]['hash'];
$vencimento = $res[0]['vencimento'];

$referencia_agendamento = 'receber';
require("../../apis/cancelar_agendamento.php");

if($cliente == ""){
	$cliente = 0;
}

$query2 = $pdo->prepare("SELECT * FROM clientes WHERE id=:cliente AND empresa=:empresa");
$query2->execute([':cliente'=>(int)$cliente, ':empresa'=>(int)$id_empresa]);
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_cliente = $res2[0]['nome'];
	$telefone_cliente = $res2[0]['telefone'];
}else{
	$nome_cliente = 'Sem Registro';
	$telefone_cliente = "";
}

if($usuario_pgto == ""){
	$usuario_pgto = 0;
}


foreach (['valor'=>$valor,'taxa'=>$taxa,'juros'=>$juros,'multa'=>$multa,'desconto'=>$desconto,'subtotal'=>$subtotal] as $campo=>$numero) {
    if (!is_numeric($numero) || !is_finite((float)$numero) || (float)$numero < 0) throw new RuntimeException('Valor inválido: '.$campo);
}
$valor=round((float)$valor,2); $taxa=round((float)$taxa,2);
$juros=round((float)$juros,2); $multa=round((float)$multa,2);
$desconto=round((float)$desconto,2); $subtotal=round((float)$subtotal,2);
$valor_antigo=round((float)$valor_antigo,2);
if ($valor<=0 || $valor>$valor_antigo) throw new RuntimeException('Valor do recebimento fora do saldo da conta.');
if (abs($subtotal-round($valor+$taxa+$juros+$multa-$desconto,2))>0.009) throw new RuntimeException('Total do recebimento divergente.');
$dataObj = DateTimeImmutable::createFromFormat('!Y-m-d',(string)$data_baixar);
if (!$dataObj || $dataObj->format('Y-m-d')!==$data_baixar) throw new RuntimeException('Data de recebimento inválida.');

if($valor > $valor_antigo){
	echo 'O valor a ser pago não pode ser superior ao valor da conta! O valor da conta é de R$ '.$valor_antigo;
	exit();
}

if($valor <= 0){
	echo 'O precisa ser maior que 0 ';
	exit();
}


//verificar caixa aberto
$query1 = $pdo->prepare("SELECT * FROM caixas WHERE operador=:usuario AND empresa=:empresa AND data_fechamento IS NULL ORDER BY id DESC LIMIT 1");
$query1->execute([':usuario'=>(int)$id_usuario,':empresa'=>(int)$id_empresa]);
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if(@count($res1) > 0){
	$id_caixa = @$res1[0]['id'];
}else{
	$id_caixa = 0;
}
//  

if($valor == $valor_antigo){

	$baixa = $pdo->prepare("UPDATE receber SET forma_pgto=:forma, usuario_pgto=:usuario, pago='Sim', subtotal=:subtotal, taxa=:taxa, juros=:juros, multa=:multa, desconto=:desconto, data_pgto=:data, caixa=:caixa, hora=CURTIME() WHERE id=:id AND empresa=:empresa AND pago <> 'Sim'");
$baixa->execute([':forma'=>$saida,':usuario'=>(int)$id_usuario,':subtotal'=>$subtotal,':taxa'=>$taxa,':juros'=>$juros,':multa'=>$multa,':desconto'=>$desconto,':data'=>$data_baixar,':caixa'=>(int)$id_caixa,':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
if ($baixa->rowCount() !== 1) throw new RuntimeException('Baixa não confirmada.');

	//CRIAR A PRÓXIMA CONTA A PAGAR
	$dias_frequencia = $frequencia;

    $mesesRecorrencia = [30=>1,31=>1,90=>3,180=>6,360=>12,365=>12];
    $dataBase = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$data_venc);
    if (!$dataBase || $dataBase->format('Y-m-d') !== $data_venc) throw new RuntimeException('Vencimento inválido.');
    if (isset($mesesRecorrencia[(int)$dias_frequencia])) {
        $primeiroDia = $dataBase->modify('first day of this month')->modify('+'.$mesesRecorrencia[(int)$dias_frequencia].' months');
        $diaDestino = min((int)$dataBase->format('d'), (int)$primeiroDia->format('t'));
        $nova_data_vencimento = $primeiroDia->setDate((int)$primeiroDia->format('Y'),(int)$primeiroDia->format('m'),$diaDestino)->format('Y-m-d');
    } else {
        $nova_data_vencimento = $dataBase->modify('+'.(int)$dias_frequencia.' days')->format('Y-m-d');
    }

	if(@$dias_frequencia > 0){
        $verificaProxima = $pdo->prepare("SELECT id FROM receber WHERE empresa=:empresa AND id_ref=:origem AND vencimento=:vencimento AND (residuo IS NULL OR residuo <> 'Sim') LIMIT 1");
        $verificaProxima->execute([':empresa'=>(int)$id_empresa, ':origem'=>(int)$id, ':vencimento'=>$nova_data_vencimento]);
        if ($verificaProxima->fetchColumn()) throw new RuntimeException('Recorrência já gerada para este vencimento.');
        $insereProxima = $pdo->prepare("INSERT INTO receber (id_ref,descricao,cliente,valor,data_lanc,vencimento,frequencia,forma_pgto,arquivo,pago,referencia,usuario_lanc,caixa,hora,empresa) VALUES (:origem,:descricao,:cliente,:valor,CURDATE(),:vencimento,:frequencia,:forma,:arquivo,'Não',:referencia,:usuario,:caixa,CURTIME(),:empresa)");
        $insereProxima->execute([':origem'=>(int)$id, ':descricao'=>$descricao, ':cliente'=>(int)$cliente, ':valor'=>$valor_antigo, ':vencimento'=>$nova_data_vencimento, ':frequencia'=>$frequencia, ':forma'=>$saida_antiga, ':arquivo'=>$arquivo, ':referencia'=>$referencia, ':usuario'=>(int)$id_usuario, ':caixa'=>(int)$id_caixa, ':empresa'=>(int)$id_empresa]);
				$id_ult_registro = $pdo->lastInsertId();	


					if($api_whatsapp != 'Não' and $telefone_cliente != ''){

					$valorF = @number_format($valor, 2, ',', '.');

	$telefone_envio = '55'.preg_replace('/[ ()-]+/' , '' , $telefone_cliente);
	$mensagem_whatsapp = '*'.$nome_sistema.'*%0A';
	$mensagem_whatsapp .= '_Conta Vencendo Hoje_ %0A';
	$mensagem_whatsapp .= '*Descrição:* '.$descricao.' %0A';
	$mensagem_whatsapp .= '*Valor:* '.$valorF.' %0A';

	if($dados_pagamento != ""){
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}	
	
	$data_agd = $nova_data_vencimento.' 08:00:00';
	$ultimo_id = (int)$id_ult_registro;
	require('../../apis/agendar.php');

	$atualizaHash = $pdo->prepare("UPDATE receber SET hash=:hash WHERE id=:id AND empresa=:empresa");
    $atualizaHash->execute([':hash'=>$hash,':id'=>(int)$id_ult_registro,':empresa'=>(int)$id_empresa]);
	
}				
	}

	

}else{

	$descricao = '(Resíduo) ' .$descricao;
    // O registro original representa o saldo aberto; cada pagamento parcial
    // é uma linha de histórico vinculada por id_ref.
    $saldoRestante = round($valor_antigo - $valor, 2);
    if ($saldoRestante <= 0) throw new RuntimeException('Saldo residual inválido.');
    $insereParcial = $pdo->prepare("INSERT INTO receber (id_ref,referencia,valor,data_pgto,vencimento,data_lanc,descricao,usuario_lanc,usuario_pgto,cliente,forma_pgto,frequencia,arquivo,subtotal,pago,taxa,multa,juros,desconto,residuo,caixa,hora,empresa) VALUES (:origem,:referencia,:valor,:data_pgto,:vencimento,CURDATE(),:descricao,:usuario_lanc,:usuario_pgto,:cliente,:forma,:frequencia,:arquivo,:subtotal,'Sim',:taxa,:multa,:juros,:desconto,'Sim',:caixa,CURTIME(),:empresa)");
    $insereParcial->execute([
        ':origem'=>(int)$id, ':referencia'=>$referencia, ':valor'=>$valor,
        ':data_pgto'=>$data_baixar, ':vencimento'=>$vencimento,
        ':descricao'=>$descricao, ':usuario_lanc'=>(int)$id_usuario,
        ':usuario_pgto'=>(int)$id_usuario, ':cliente'=>(int)$cliente,
        ':forma'=>$saida, ':frequencia'=>$frequencia, ':arquivo'=>$arquivo,
        ':subtotal'=>$subtotal, ':taxa'=>$taxa, ':multa'=>$multa,
        ':juros'=>$juros, ':desconto'=>$desconto,
        ':caixa'=>(int)$id_caixa, ':empresa'=>(int)$id_empresa
    ]);
    $atualizaSaldo = $pdo->prepare("UPDATE receber SET valor=:saldo, usuario_pgto=:usuario, data_pgto=:data WHERE id=:id AND empresa=:empresa AND pago <> 'Sim'");
    $atualizaSaldo->execute([':saldo'=>$saldoRestante, ':usuario'=>(int)$id_usuario, ':data'=>$data_baixar, ':id'=>(int)$id, ':empresa'=>(int)$id_empresa]);
    if ($atualizaSaldo->rowCount() !== 1) throw new RuntimeException('Saldo da conta não atualizado.');
    $valor_antigo = $saldoRestante;

	if($api_whatsapp != 'Não' and $telefone_cliente != ''){

					$valorF = @number_format($valor_antigo, 2, ',', '.');

			$telefone_envio = '55'.preg_replace('/[ ()-]+/' , '' , $telefone_cliente);
			$mensagem_whatsapp = '*'.$nome_sistema.'*%0A';
			$mensagem_whatsapp .= '_Conta Vencendo Hoje_ %0A';
			$mensagem_whatsapp .= '*Descrição:* '.$descricao.' %0A';
			$mensagem_whatsapp .= '*Valor:* '.$valorF.' %0A';	

			if($dados_pagamento != ""){
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}
			
			$data_agd = $vencimento.' 08:00:00';
			$ultimo_id = (int)$id;
			require('../../apis/agendar.php');

			$atualizaHash = $pdo->prepare("UPDATE receber SET hash=:hash WHERE id=:id AND empresa=:empresa");
            $atualizaHash->execute([':hash'=>$hash,':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
			
		}	


}

$buscaDepois = $pdo->prepare("SELECT descricao,valor,vencimento,data_pgto,forma_pgto,pago,subtotal,frequencia FROM receber WHERE id=:id AND empresa=:empresa");
$buscaDepois->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
$auditoriaDepois = $buscaDepois->fetch(PDO::FETCH_ASSOC);
registrarAuditoriaFinanceira($pdo,(int)$id_empresa,'receber',(int)$id,'BAIXAR',(int)$id_usuario,$auditoriaAntes,$auditoriaDepois);
$pdo->commit();
echo 'Baixado com Sucesso';
} catch (Throwable $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Baixa de conta a receber: '.$e->getMessage());
 http_response_code(409);
 echo 'Não foi possível baixar a conta: '.$e->getMessage();
}

?>

