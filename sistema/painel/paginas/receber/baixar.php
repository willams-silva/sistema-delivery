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

$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
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

	$pdo->query("UPDATE $tabela set forma_pgto = '$saida', usuario_pgto = '$id_usuario', pago = 'Sim', subtotal = '$subtotal', taxa = '$taxa', juros = '$juros', multa = '$multa', desconto = '$desconto', data_pgto = '$data_baixar', caixa = '$id_caixa', hora = curTime() where id = '$id' AND empresa = '$id_empresa'");

	//CRIAR A PRÓXIMA CONTA A PAGAR
	$dias_frequencia = $frequencia;

	if($dias_frequencia == 30 || $dias_frequencia == 31){		
		$nova_data_vencimento = date('Y/m/d', strtotime("+1 month",strtotime($data_venc)));

	}else if($dias_frequencia == 90){
		$nova_data_vencimento = date('Y/m/d', strtotime("+3 month",strtotime($data_venc)));

	}else if($dias_frequencia == 180){ 
		$nova_data_vencimento = date('Y/m/d', strtotime("+6 month",strtotime($data_venc)));

	}else if($dias_frequencia == 360 || $dias_frequencia == 365){
		$nova_data_vencimento = date('Y/m/d', strtotime("+1 year",strtotime($data_venc)));

	}else{		
		$nova_data_vencimento = date('Y/m/d', strtotime("+$dias_frequencia days",strtotime($data_venc))); 
	}


	if(@$dias_frequencia > 0){
		$pdo->query("INSERT INTO $tabela set descricao = '$descricao', cliente = '$cliente', valor = '$valor_antigo', data_lanc = curDate(), vencimento = '$nova_data_vencimento', frequencia = '$frequencia', forma_pgto = '$saida_antiga', arquivo = '$arquivo', pago = 'Não', referencia = '$referencia', usuario_lanc = '$id_usuario', caixa = '$id_caixa', hora = curTime(), empresa = '$id_empresa' ");
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

	$pdo->query("UPDATE $tabela SET hash = '$hash' where id = '$id_ult_registro'");
	
}				
	}

	

}else{

	$descricao = '(Resíduo) ' .$descricao;
	//PEGAR RESIDUOS DA CONTA
	$total_resid = 0;
	$query = $pdo->query("SELECT * FROM receber WHERE id_ref = '$id' and residuo = 'Sim' AND empresa = '$id_empresa'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res) > 0){
	
		for($i=0; $i < @count($res); $i++){
		foreach ($res[$i] as $key => $value){} 
			$valor_resid = $res[$i]['valor'];
			$total_resid += $valor_resid;
		}
	}

	$valor_antigo = round($valor_antigo - $valor, 2);

	$pdo->query("INSERT INTO receber set id_ref = '$id', referencia = '$referencia', valor = '$valor_padrao', data_pgto = curDate(), vencimento = curDate(), data_lanc = curDate(), descricao = '$descricao', usuario_lanc = '$id_usuario', usuario_pgto = '$id_usuario', cliente = '$cliente', forma_pgto = '$saida', frequencia = '$frequencia', arquivo = '$arquivo', subtotal = '$subtotal', pago = 'Sim', taxa = '$taxa', multa = '$multa', juros = '$juros', desconto = '$desconto', residuo = 'Sim', caixa = '$id_caixa', hora = curTime(), empresa = '$id_empresa' ");

	$pdo->query("UPDATE $tabela set forma_pgto = '$saida', usuario_pgto = '$id_usuario', valor = '$valor_antigo', data_pgto = curDate() where id = '$id' AND empresa = '$id_empresa'");


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

			$pdo->query("UPDATE $tabela SET hash = '$hash' where id = '$id' AND empresa = '$id_empresa'");
			
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

