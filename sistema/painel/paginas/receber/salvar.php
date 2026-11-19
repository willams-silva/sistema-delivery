<?php
@session_start();
$id_empresa = $_SESSION['empresa'] ?? null;
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if (!ctype_digit((string)($_SESSION['id'] ?? '')) || (int)$_SESSION['id'] < 1 || !ctype_digit((string)$id_empresa) || (int)$id_empresa < 1) { http_response_code(403); exit('Sessão inválida.'); }
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'receber';


@session_start();
$id_usuario = @$_SESSION['id'];

$hora = date('H');

if ($hora < 12 && $hora >= 6)
	$saudacao = "Bom Dia";

if ($hora >= 12 && $hora < 18)
	$saudacao = "Boa Tarde";

if ($hora >= 18 && $hora <= 23)
	$saudacao = "Boa Noite";
if ($hora < 6 && $hora >= 0)
	$saudacao = "Boa madrugada";


$descricao = $_POST['descricao'];
$valor = $_POST['valor'];
$cliente = $_POST['cliente'];
$vencimento = $_POST['vencimento'];
$data_pgto = $_POST['data_pgto'];
$forma_pgto = $_POST['forma_pgto'];
$frequencia = $_POST['frequencia'];
$obs = $_POST['obs'];
$id = $_POST['id'];
// Não permitir editar lançamentos de outro estabelecimento.
if ($id !== '' && (!ctype_digit((string)$id) || (int)$id <= 0)) { http_response_code(400); exit('Lançamento inválido.'); }
if ($id !== '') {
    // Registros liquidados e com dependências devem ser tratados por estorno,
    // não por edição direta do valor ou do vencimento.
    $verificaMovimentos = $pdo->prepare("SELECT pago, residuo FROM receber WHERE id=:id AND empresa=:empresa");
    $verificaMovimentos->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
    $estadoConta = $verificaMovimentos->fetch(PDO::FETCH_ASSOC);
    if (!$estadoConta) { http_response_code(403); exit('Conta não pertence à empresa.'); }
    $dependencias = $pdo->prepare("SELECT COUNT(*) FROM receber WHERE id_ref=:id AND empresa=:empresa");
    $dependencias->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
    if (($estadoConta['pago']??'')==='Sim' || ($estadoConta['residuo']??'')==='Sim' || (int)$dependencias->fetchColumn()>0) {
        http_response_code(409); exit('Conta com movimentação financeira vinculada. Utilize o procedimento de estorno.');
    }
    $verificaEmpresa = $pdo->prepare("SELECT id FROM ${tabela} WHERE id = :id AND empresa = :empresa LIMIT 1");
    $verificaEmpresa->execute([':id'=>(int)$id, ':empresa'=>(int)$id_empresa]);
    if (!$verificaEmpresa->fetchColumn()) { http_response_code(403); exit('Lançamento não pertence à empresa.'); }
}




// Validação dos dados antes de gravar lançamentos financeiros.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('Método inválido.'); }
if (!is_scalar($valor) || !is_numeric(str_replace(',', '.', (string)$valor)) || !is_finite((float)str_replace(',', '.', (string)$valor)) || (float)str_replace(',', '.', (string)$valor) <= 0) {
    http_response_code(400); exit('Informe um valor válido maior que zero.');
}
foreach (['vencimento'=>$vencimento] + ($data_pgto !== '' ? ['data_pgto'=>$data_pgto] : []) as $campo=>$data) {
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$data);
    if (!$dt || $dt->format('Y-m-d') !== $data) { http_response_code(400); exit('Data inválida: '.$campo); }
}
foreach (['cliente'=>$cliente,'forma_pgto'=>$forma_pgto,'frequencia'=>$frequencia] as $campo=>$numero) {
    if ($numero !== '' && (!ctype_digit((string)$numero) || (int)$numero < 0)) {
        http_response_code(400); exit('Campo inválido: '.$campo);
    }
}
$valor = str_replace(',', '.', $valor);

$valorF = @number_format($valor, 2, ',', '.');

if($cliente == ""){
	$cliente = 0;
}

if($forma_pgto == ""){
	$forma_pgto = 0;
}

if($frequencia == ""){
	$frequencia = 0;
}

if($data_pgto == ""){
	$pgto = '';
	$usu_pgto = '';
	$pago = 'Não';
}else{
	$pgto = " ,data_pgto = '$data_pgto'";
	$usu_pgto = " ,usuario_pgto = '$id_usuario'";
	$pago = 'Sim';
}

//validacao
if($descricao == "" and $cliente == "0"){
	echo 'Selecione um Cliente ou uma Descrição!';
	exit();
}

$query2 = $pdo->prepare("SELECT * FROM clientes WHERE id=:cliente AND empresa=:empresa");
$query2->execute([':cliente'=>(int)$cliente,':empresa'=>(int)$id_empresa]);
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
if(@count($res2) > 0){
	$nome_cliente = $res2[0]['nome'];
	$telefone_cliente = $res2[0]['telefone'];
}else{
	$nome_cliente = 'Sem Registro';
	$telefone_cliente = "";
}

// extrair o primiero nome
$primeiroNome = explode(" ", $nome_cliente);

if($descricao == ""){
	$descricao = $nome_cliente;
}

//validar troca da foto
$query = $pdo->prepare("SELECT * FROM receber WHERE id=:id AND empresa=:empresa");
$query->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$foto = $res[0]['arquivo'];
	$vencimento_antiga = $res[0]['vencimento'];
}else{
	$foto = 'sem-foto.png';
	$vencimento_antiga = '';
}



// Anexos: nome gerado pelo servidor, tamanho limitado e extensoes permitidas.
// A substituicao preserva o arquivo anterior, que pode ser compartilhado por recorrencias.
$upload = $_FILES['foto'] ?? null;
if (is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if (($upload['error'] ?? -1) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'] ?? '')) {
        http_response_code(400); exit('Falha no envio do anexo.');
    }
    if (($upload['size'] ?? 0) <= 0 || $upload['size'] > 10 * 1024 * 1024) {
        http_response_code(400); exit('Anexo deve ter no máximo 10 MB.');
    }
    $ext = strtolower(pathinfo((string)($upload['name'] ?? ''), PATHINFO_EXTENSION));
    $extensoes = ['png','jpg','jpeg','gif','webp','pdf','doc','docx','xls','xlsx','xlsm','zip','rar','xml'];
    if (!in_array($ext, $extensoes, true)) {
        http_response_code(400); exit('Extensão de anexo não permitida.');
    }
    $nome_img = bin2hex(random_bytes(16)) . '.' . $ext;
    $caminho = '../../images/contas/' . $nome_img;
    if (in_array($ext, ['png','jpg','jpeg','gif','webp'], true)) {
        $dimensoes = @getimagesize($upload['tmp_name']);
        if (!$dimensoes || $dimensoes[0] > 1400) {
            http_response_code(400); exit('Imagem inválida ou maior que 1400px de largura.');
        }
    }
    if (!move_uploaded_file($upload['tmp_name'], $caminho)) {
        http_response_code(500); exit('Não foi possível salvar o anexo.');
    }
    $foto = $nome_img;
}

//verificar caixa aberto
$query1 = $pdo->prepare("SELECT * FROM caixas WHERE operador=:usuario AND data_fechamento IS NULL AND empresa=:empresa ORDER BY id DESC LIMIT 1");
$query1->execute([':usuario'=>(int)$id_usuario,':empresa'=>(int)$id_empresa]);
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if(@count($res1) > 0){
	$id_caixa = @$res1[0]['id'];
}else{
	$id_caixa = 0;
}


if($id == ""){
$query = $pdo->prepare("INSERT INTO receber SET descricao=:descricao, cliente=:cliente, valor=:valor, vencimento=:vencimento, data_pgto=:data_pgto, data_lanc=CURDATE(), forma_pgto=:forma_pgto, frequencia=:frequencia, obs=:obs, arquivo=:arquivo, foto=:foto, subtotal=:subtotal, usuario_lanc=:usuario_lanc, usuario_pgto=:usuario_pgto, pago=:pago, referencia='Conta', caixa=:caixa, hora=CURTIME(), empresa=:empresa");

}else{
$query = $pdo->prepare("UPDATE receber SET descricao=:descricao, cliente=:cliente, valor=:valor, vencimento=:vencimento, data_pgto=:data_pgto, forma_pgto=:forma_pgto, frequencia=:frequencia, obs=:obs, arquivo=:arquivo, foto=:foto, subtotal=:subtotal, usuario_pgto=:usuario_pgto, pago=:pago WHERE id=:id AND empresa=:empresa AND (pago IS NULL OR pago <> 'Sim')");



	if ($vencimento_antiga != $vencimento) {


		$query4 = $pdo->prepare("SELECT * FROM receber WHERE id=:id AND empresa=:empresa");
        $query4->execute([':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
		$res4 = $query4->fetchAll(PDO::FETCH_ASSOC);
		$hash = @$res4[0]['hash'];

		if ($hash != "") {
			require("../../apis/cancelar_agendamento.php");
		}

		$vencimentoF = implode('/', array_reverse(explode('-', $vencimento)));


		//enviar whatsapp
		if ($api_whatsapp != 'Não' and $telefone_cliente != '') {


			$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);

			############## AGENDAR MENSAGEM PARA RENOVAÇÃO ###########################
			$mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';
			$mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome[0] . '* tudo bem? 😀%0A%0A';
			$mensagem_whatsapp .= '_Queremos lembrar que você tem uma Conta Vencendo Hoje_ %0A%0A';
			$mensagem_whatsapp .= 'Empresa: *' . $nome_sistema . '* %0A';
			$mensagem_whatsapp .= 'Nome: *' . $nome_cliente . '* %0A';
			$mensagem_whatsapp .= 'Valor: *R$ ' . $valorF . '* %0A';
			$mensagem_whatsapp .= 'Data de Vencimento: *' . $vencimentoF . '* %0A%0A';
			$mensagem_whatsapp .= '_Entre em contato conosco para acertar o pagamento!_ %0A%0A';


			if ($dados_pagamento != "") {
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}

			$mensagem_whatsapp .= '%0A';
			$mensagem_whatsapp .= '🤖 _Esta é uma mensagem automática!_';

			$data_agd = $vencimento . ' 09:00:00';
			$ultimo_id = $id;
			require('../../apis/agendar.php');

			$stHash=$pdo->prepare("UPDATE receber SET hash=:hash WHERE id=:id AND empresa=:empresa");
            $stHash->execute([':hash'=>$hash,':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
		}
	}


}


// Captura do estado imediatamente anterior para auditoria de criação/edição.
require_once __DIR__ . '/../../includes/auditoria_financeira.php';
$auditoriaAnterior = null;
if ($id !== '') {
    $auditoriaBusca = $pdo->prepare("SELECT descricao, valor, vencimento, data_pgto, forma_pgto, pago, subtotal, frequencia FROM ${tabela} WHERE id = :id AND empresa = :empresa");
    $auditoriaBusca->execute([':id'=>(int)$id, ':empresa'=>(int)$id_empresa]);
    $auditoriaAnterior = $auditoriaBusca->fetch(PDO::FETCH_ASSOC);
    if (!$auditoriaAnterior) { http_response_code(403); exit('Lançamento não encontrado para auditoria.'); }
}
$query->bindValue(":descricao", "$descricao");
$query->bindValue(":cliente", "$cliente");
$query->bindValue(":valor", "$valor");
$query->bindValue(':subtotal',$valor);
$query->bindValue(":obs", "$obs");
$query->bindValue(':vencimento',$vencimento);
$query->bindValue(':data_pgto',$data_pgto !== '' ? $data_pgto : null);
$query->bindValue(':forma_pgto',$forma_pgto);
$query->bindValue(':frequencia',$frequencia);
$query->bindValue(':arquivo',$foto);
$query->bindValue(':foto',$foto);
$query->bindValue(':usuario_pgto',$data_pgto !== '' ? (int)$id_usuario : null);
$query->bindValue(':pago',$pago);
$query->bindValue(':empresa',(int)$id_empresa,PDO::PARAM_INT);
if ($id === '') {
    $query->bindValue(':usuario_lanc',(int)$id_usuario,PDO::PARAM_INT);
    $query->bindValue(':caixa',(int)$id_caixa,PDO::PARAM_INT);
} else {
    $query->bindValue(':id',(int)$id,PDO::PARAM_INT);
}
// Gravação e trilha de auditoria atômicas: erro em qualquer etapa desfaz ambas.
try {
    $pdo->beginTransaction();
    $query->execute();
    $ultimo_id = $id === '' ? (int)$pdo->lastInsertId() : (int)$id;
    // A recorrência nasce somente depois da conta original, na mesma transação.
    if ($id === '' && $data_pgto !== '' && (int)$frequencia > 0) {
        $base = DateTimeImmutable::createFromFormat('!Y-m-d', $vencimento);
        if (!$base || $base->format('Y-m-d') !== $vencimento) {
            throw new RuntimeException('Vencimento inválido para recorrência.');
        }
        $meses = [30=>1,31=>1,90=>3,180=>6,360=>12,365=>12];
        if (isset($meses[(int)$frequencia])) {
            $primeiro = $base->modify('first day of this month')->modify('+'.$meses[(int)$frequencia].' months');
            $dia = min((int)$base->format('d'), (int)$primeiro->format('t'));
            $proximoVencimento = $primeiro->setDate((int)$primeiro->format('Y'),(int)$primeiro->format('m'),$dia)->format('Y-m-d');
        } else {
            $proximoVencimento = $base->modify('+'.(int)$frequencia.' days')->format('Y-m-d');
        }
        $insereRecorrencia = $pdo->prepare("INSERT INTO receber (id_ref,descricao,cliente,valor,data_lanc,vencimento,frequencia,forma_pgto,arquivo,foto,pago,referencia,usuario_lanc,hora,obs,caixa,empresa) VALUES (:origem,:descricao,:cliente,:valor,CURDATE(),:vencimento,:frequencia,:forma,:arquivo,:foto,'Não','Conta',:usuario,CURTIME(),:obs,:caixa,:empresa)");
        $insereRecorrencia->execute([
            ':origem'=>$ultimo_id, ':descricao'=>$descricao, ':cliente'=>(int)$cliente,
            ':valor'=>$valor, ':vencimento'=>$proximoVencimento, ':frequencia'=>(int)$frequencia,
            ':forma'=>$forma_pgto, ':arquivo'=>$foto, ':foto'=>$foto, ':usuario'=>(int)$id_usuario,
            ':obs'=>$obs, ':caixa'=>(int)$id_caixa, ':empresa'=>(int)$id_empresa
        ]);
    }

    $auditoriaBuscaDepois = $pdo->prepare("SELECT descricao, valor, vencimento, data_pgto, forma_pgto, pago, subtotal, frequencia FROM ${tabela} WHERE id = :id AND empresa = :empresa");
    $auditoriaBuscaDepois->execute([':id'=>$ultimo_id, ':empresa'=>(int)$id_empresa]);
    $auditoriaDepois = $auditoriaBuscaDepois->fetch(PDO::FETCH_ASSOC);
    if (!$auditoriaDepois) { throw new RuntimeException('Lançamento não localizado após gravação.'); }
    $acaoAuditoria = $id === '' ? 'CRIAR' : 'ALTERAR';
    if ($id !== '' && ($auditoriaAnterior['pago'] ?? '') !== ($auditoriaDepois['pago'] ?? '')) {
        $acaoAuditoria = ($auditoriaDepois['pago'] ?? '') === 'Sim' ? 'BAIXAR' : 'ESTORNAR';
    }
    registrarAuditoriaFinanceira($pdo, (int)$id_empresa, $tabela, $ultimo_id, $acaoAuditoria, (int)$id_usuario, $auditoriaAnterior, $auditoriaDepois);
    $pdo->commit();
} catch (Throwable $erroAuditoria) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log('Erro ao salvar lançamento financeiro com auditoria: ' . $erroAuditoria->getMessage());
    http_response_code(500);
    exit('Não foi possível salvar o lançamento financeiro. Nenhuma alteração foi confirmada.');
}






//enviar whatsapp
if($api_whatsapp != 'Não' and $telefone_cliente != ''  and $id == "" and $data_pgto == ""){

	$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);

	$mensagem_whatsapp = '🔔_Lembrete Automático de Vencimento!_ %0A%0A';

	$mensagem_whatsapp .= $saudacao . ' *' . $primeiroNome[0] . '* tudo bem? 😀%0A%0A';

	$mensagem_whatsapp .= '*'.$nome_sistema.'*%0A';
	$mensagem_whatsapp .= '_Queremos lembrar que sua Conta Vencendo Hoje_ %0A';
	$mensagem_whatsapp .= '*Descrição:* '.$descricao.' %0A';
	$mensagem_whatsapp .= '*Valor:* '.$valorF.' %0A%0A';	


	if($dados_pagamento != ""){
				$mensagem_whatsapp .= '*Dados para o Pagamento:* %0A';
				$mensagem_whatsapp .= $dados_pagamento;
			}
	
	$data_agd = $vencimento.' 09:00:00';
	require('../../apis/agendar.php');

	$stHash=$pdo->prepare("UPDATE receber SET hash=:hash WHERE id=:id AND empresa=:empresa");
    $stHash->execute([':hash'=>$hash,':id'=>(int)$ultimo_id,':empresa'=>(int)$id_empresa]);
	
}


echo 'Salvo com Sucesso';
