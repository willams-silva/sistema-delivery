<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if (!isset($_SESSION['id']) || !ctype_digit((string)$_SESSION['id'])) { http_response_code(403); exit('Sessão inválida.'); }
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'pagar';


$referencia_agendamento = 'pagar';

$id_usuario = @$_SESSION['id'];

$descricao = $_POST['descricao'];
$valor = $_POST['valor'];
$fornecedor = $_POST['fornecedor'];
$funcionario = $_POST['funcionario'];
$vencimento = $_POST['vencimento'];
$data_pgto = $_POST['data_pgto'];
$forma_pgto = $_POST['forma_pgto'];
$frequencia = $_POST['frequencia'];
$obs = $_POST['obs'];
$id = $_POST['id'];
// Não permitir editar lançamentos de outro estabelecimento.
if ($id !== '' && (!ctype_digit((string)$id) || (int)$id <= 0)) { http_response_code(400); exit('Lançamento inválido.'); }
if ($id !== '') {
    $verificaEmpresa = $pdo->prepare("SELECT id FROM ${tabela} WHERE id = :id AND empresa = :empresa LIMIT 1");
    $verificaEmpresa->execute([':id'=>(int)$id, ':empresa'=>(int)$id_empresa]);
    if (!$verificaEmpresa->fetchColumn()) { http_response_code(403); exit('Lançamento não pertence à empresa.'); }
}



$hora = date('H');

if ($hora < 12 && $hora >= 6)
	$saudacao = "Bom Dia";
if ($hora >= 12 && $hora < 18)
	$saudacao = "Boa Tarde";
if ($hora >= 18 && $hora <= 23)
	$saudacao = "Boa Noite";
if ($hora < 6 && $hora >= 0)
	$saudacao = "Boa madrugada";

$valor = str_replace(',', '.', $valor);

$valorF = @number_format($valor, 2, ',', '.');

if ($fornecedor == "") {
	$fornecedor = 0;
}

if ($funcionario == "") {
	$funcionario = 0;
}

if ($forma_pgto == "") {
	$forma_pgto = 0;
}

if ($frequencia == "") {
	$frequencia = 0;
}



//validacao
if ($descricao == "" and $fornecedor == "0" and $funcionario == "0") {
	echo 'Selecione um Fornecedor ou um Funcionário ou uma Descrição!';
	exit();
}

if ($fornecedor != "0" and $funcionario != "0") {
	echo 'Selecione um Fornecedor ou um Funcionário!';
	exit();
}



//validar troca da foto
$query = $pdo->query("SELECT * FROM $tabela where id = '$id' and empresa = '$id_empresa'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	$foto = $res[0]['arquivo'];
	$vencimento_antiga = $res[0]['vencimento'];
} else {
	$foto = 'sem-foto.png';
	$vencimento_antiga = '';
}



// Anexos financeiros: validar conteúdo real, tamanho e nome antes de armazenar.
$upload = $_FILES['foto'] ?? null;
if ($upload && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if (($upload['error'] ?? -1) !== UPLOAD_ERR_OK) {
        http_response_code(400); exit('Falha no envio do comprovante.');
    }
    $temporario = (string)($upload['tmp_name'] ?? '');
    $tamanho = (int)($upload['size'] ?? 0);
    if ($tamanho <= 0 || $tamanho > 8 * 1024 * 1024 || !is_uploaded_file($temporario)) {
        http_response_code(400); exit('Comprovante inválido ou maior que 8 MB.');
    }
    $extensao = strtolower(pathinfo((string)($upload['name'] ?? ''), PATHINFO_EXTENSION));
    $permitidos = [
        'pdf'=>['application/pdf'],
        'png'=>['image/png'],
        'jpg'=>['image/jpeg'], 'jpeg'=>['image/jpeg'],
        'gif'=>['image/gif'],
        'webp'=>['image/webp']
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($temporario);
    if (!isset($permitidos[$extensao]) || !in_array($mime, $permitidos[$extensao], true)) {
        http_response_code(400); exit('Formato não permitido. Use PDF, PNG, JPG, GIF ou WEBP.');
    }
    if ($extensao !== 'pdf') {
        $dimensoes = @getimagesize($temporario);
        if (!$dimensoes || $dimensoes[0] > 1400) {
            http_response_code(400); exit('Imagem inválida ou com largura superior a 1400px.');
        }
    } else {
        $cabecalho = file_get_contents($temporario, false, null, 0, 5);
        if ($cabecalho !== '%PDF-') {
            http_response_code(400); exit('PDF inválido.');
        }
    }
    try {
        $nome_img = bin2hex(random_bytes(16)) . '.' . $extensao;
    } catch (Throwable $e) {
        http_response_code(500); exit('Não foi possível gerar o nome do comprovante.');
    }
    $diretorio = '../../images/contas/';
    if (!is_dir($diretorio) || !is_writable($diretorio) || !move_uploaded_file($temporario, $diretorio . $nome_img)) {
        http_response_code(500); exit('Não foi possível salvar o comprovante.');
    }
    // Não remover o arquivo antigo: recorrências e resíduos podem compartilhar o anexo.
    $foto = $nome_img;
}

if ($fornecedor != 0 || $funcionario != 0) {
	if ($fornecedor != 0) {
		$tab = 'fornecedores';
		$id_pessoa = $fornecedor;
	}

	if ($funcionario != 0) {
		$tab = 'usuarios';
		$id_pessoa = $funcionario;
	}

	//nome pessoa
	$query = $pdo->query("SELECT * FROM $tab where id = '$id_pessoa'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$total_reg = @count($res);
	if ($total_reg > 0) {
		$nome_pessoa = $res[0]['nome'];
	} else {
		$nome_pessoa = '';
	}

	if ($descricao == "") {
		$descricao = $nome_pessoa;
	}
}


if ($data_pgto == "") {
	$pgto = '';
	$usu_pgto = '';
	$pago = 'Não';
} else {
	$pgto = " ,data_pgto = '$data_pgto'";
	$usu_pgto = " ,usuario_pgto = '$id_usuario'";
	$pago = 'Sim';
}


//verificar caixa aberto
$query1 = $pdo->query("SELECT * from caixas where operador = '$id_usuario' and data_fechamento is null and empresa = '$id_empresa' order by id desc limit 1");
$res1 = $query1->fetchAll(PDO::FETCH_ASSOC);
if (@count($res1) > 0) {
	$id_caixa = @$res1[0]['id'];
} else {
	$id_caixa = 0;
}
//  


if ($id == "") {
	$query = $pdo->prepare("INSERT INTO $tabela SET descricao = :descricao, fornecedor = '$fornecedor', funcionario = '$funcionario', valor = :valor, vencimento = '$vencimento' $pgto, data_lanc = curDate(), forma_pgto = '$forma_pgto', frequencia = '$frequencia', obs = :obs, arquivo = '$foto', foto = '$foto', subtotal = :valor, usuario_lanc = '$id_usuario' $usu_pgto, pago = '$pago', referencia = 'Conta', caixa = '$id_caixa', hora = curTime(), empresa = '$id_empresa' ");
	


	################# CRIAR A PRÓXIMA CONTA A PAGAR CASO TENHA SIDO PAGA #################
	$dias_frequencia = $frequencia;


	if ($dias_frequencia == 30 || $dias_frequencia == 31) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+1 month", strtotime($vencimento)));
	} else if ($dias_frequencia == 90) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+3 month", strtotime($vencimento)));
	} else if ($dias_frequencia == 180) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+6 month", strtotime($vencimento)));
	} else if ($dias_frequencia == 360 || $dias_frequencia == 365) {
		$nova_data_vencimento = date('Y/m/d', strtotime("+1 year", strtotime($vencimento)));
	} else {
		$nova_data_vencimento = date('Y/m/d', strtotime("+$dias_frequencia days", strtotime($vencimento)));
	}


	if(@$dias_frequencia > 0 and $data_pgto != ''){


		$pdo->query("INSERT INTO $tabela SET descricao = '$descricao', fornecedor = '$fornecedor', funcionario = '$funcionario', valor = '$valor' $pgto, data_lanc = curDate(), vencimento = '$nova_data_vencimento', forma_pgto = '$forma_pgto', frequencia = '$frequencia', obs = '$obs', arquivo = '$foto', foto = '$foto', subtotal = '$valor', usuario_lanc = '$id_usuario', referencia = 'Conta', caixa = '$id_caixa', hora = curTime(), pago = 'Não', empresa = '$id_empresa' ");

		$id_ultimo = $pdo->lastInsertId();


		//enviar whatsapp
		if ($api_whatsapp != 'Não' and $telefone_sistema != '') {

			$mensagem_whatsapp = '*Lembrete Automático de Vencimento!* 🤖 %0A%0A';

			$mensagem_whatsapp .= $saudacao . ' 😀%0A%0A';

			$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_sistema);
			$mensagem_whatsapp .= '*' . $nome_sistema . '*%0A';
			$mensagem_whatsapp .= '_Queremos lembrar que sua Conta Vencendo Hoje_ %0A';
			$mensagem_whatsapp .= '*Descrição:* ' . $descricao . ' %0A';
			$mensagem_whatsapp .= '*Valor:* ' . $valorF . ' %0A';

			$data_agd = $nova_data_vencimento . ' 09:00:00';
			$ultimo_id = $id_ultimo;
			$referencia_agendamento = 'pagar';
			require('../../apis/agendar.php');

			$pdo->query("UPDATE $tabela SET hash = '$hash' where id = '$id_ultimo'");
		}
	}

} else {
	$query = $pdo->prepare("UPDATE $tabela SET descricao = :descricao, fornecedor = '$fornecedor', funcionario = '$funcionario', valor = :valor, vencimento = '$vencimento' $pgto, forma_pgto = '$forma_pgto', frequencia = '$frequencia', obs = :obs, arquivo = '$foto', foto = '$foto', subtotal = :valor where id = '$id' and empresa = '$id_empresa'");
	$id_ultimo_alt = $pdo->lastInsertId();


//AGENDA NOVA MSG SE A DATA DE VENICIMENTO FOR DIFERENTE DA ANTERIOR
	if ($vencimento_antiga != $vencimento) {


		$query4 = $pdo->query("SELECT * FROM $tabela where id = '$id' and empresa = '$id_empresa'");
		$res4 = $query4->fetchAll(PDO::FETCH_ASSOC);
		$hash = @$res4[0]['hash'];

		$referencia_agendamento = 'pagar';
		// Remove pendências antigas mesmo quando o hash está vazio.
		require("../../apis/cancelar_agendamento.php");


		//enviar whatsapp
		if ($api_whatsapp != 'Não' and $telefone_sistema != '') {

			$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_sistema);

			$mensagem_whatsapp = '*Lembrete Automático de Vencimento! 🤖* %0A%0A';
			$mensagem_whatsapp .= $saudacao . ' 😀%0A%0A';
			$mensagem_whatsapp .= '*' . $nome_sistema . '*%0A';
			$mensagem_whatsapp .= '_Queremos lembrar que sua Conta Vencendo Hoje_ %0A';
			$mensagem_whatsapp .= '*Descrição:* ' . $descricao . ' %0A';
			$mensagem_whatsapp .= '*Valor:* ' . $valorF . ' %0A';

			$data_agd = $vencimento . ' 09:00:00';
			$ultimo_id = $id;
			$referencia_agendamento = 'pagar';
			require('../../apis/agendar.php');

			$atualizaHash = $pdo->prepare("UPDATE pagar SET hash=:hash WHERE id=:id AND empresa=:empresa");
			$atualizaHash->execute([':hash'=>$hash,':id'=>(int)$id,':empresa'=>(int)$id_empresa]);
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
$query->bindValue(":valor", "$valor");
$query->bindValue(":obs", "$obs");
// Gravação e trilha de auditoria atômicas: erro em qualquer etapa desfaz ambas.
try {
    $pdo->beginTransaction();
    $query->execute();
    $ultimo_id = $id === '' ? (int)$pdo->lastInsertId() : (int)$id;
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
	if ($api_whatsapp != 'Não' and $telefone_sistema != '' and $id == "" and $data_pgto == "") {

		$mensagem_whatsapp = '*Lembrete Automático de Vencimento! 🤖* %0A%0A';

		$mensagem_whatsapp .= $saudacao . ' 😀%0A%0A';

		$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone_sistema);
		$mensagem_whatsapp .= '*' . $nome_sistema . '*%0A';
		$mensagem_whatsapp .= '_Queremos lembrar que sua Conta Vencendo Hoje_ %0A';
		$mensagem_whatsapp .= '*Descrição:* ' . $descricao . ' %0A';
		$mensagem_whatsapp .= '*Valor:* ' . $valorF . ' %0A';

		$data_agd = $vencimento . ' 09:00:00';

		$referencia_agendamento = 'pagar';
		$ultimo_id = (int)$ultimo_id;
		require('../../apis/agendar.php');

		$atualizaHash = $pdo->prepare("UPDATE pagar SET hash=:hash WHERE id=:id AND empresa=:empresa");
		$atualizaHash->execute([':hash'=>$hash,':id'=>$ultimo_id,':empresa'=>(int)$id_empresa]);
	}


echo 'Salvo com Sucesso';
