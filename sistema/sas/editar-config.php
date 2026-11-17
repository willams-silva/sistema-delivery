<?php 
$tabela = 'config';
require_once("../conexao.php");

if($modo_teste == 'Sim'){
	echo 'Em modo de teste esse recurso fica desabilitado!';
	exit();
}

$nome = $_POST['nome_sistema'];
$email = $_POST['email_sistema'];
$telefone = $_POST['telefone_sistema'];
$endereco = $_POST['endereco_sistema'];
$instagram = $_POST['instagram_sistema'];
$multa_atraso = $_POST['multa_atraso'];
$juros_atraso = $_POST['juros_atraso'];
$marca_dagua = $_POST['marca_dagua'];
$assinatura_recibo = $_POST['assinatura_recibo'];
$impressao_automatica = $_POST['impressao_automatica'];
$cnpj_sistema = $_POST['cnpj_sistema'];
$entrar_automatico = $_POST['entrar_automatico'];
$mostrar_preloader = $_POST['mostrar_preloader'];
$ocultar_mobile = $_POST['ocultar_mobile'];
$api_whatsapp = $_POST['api_whatsapp'];
$token_whatsapp = $_POST['token_whatsapp'];
$instancia_whatsapp = $_POST['instancia_whatsapp'];
$alterar_acessos = $_POST['alterar_acessos'];
$dados_pagamento = $_POST['dados_pagamento'];
$abertura_caixa = $_POST['abertura_caixa'];
$access_token = $_POST['access_token'];
$public_key = $_POST['public_key'];
$limitar_recursos = $_POST['limitar_recursos'];
$api_pagamento = $_POST['api_pagamento'];
$chave_api_asaas = $_POST['chave_api_asaas'];
$alterar_api_whatsapp = $_POST['alterar_api_whatsapp'];
$pagina_entrada = $_POST['pagina_entrada'];
$multi_empresas = $_POST['multi_empresas'];
$meta_descricao = $_POST['meta_descricao'];

$multa_atraso = str_replace(',', '.', $multa_atraso);
$multa_atraso = str_replace('%', '', $multa_atraso);

$juros_atraso = str_replace(',', '.', $juros_atraso);
$juros_atraso = str_replace('%', '', $juros_atraso);

//foto logo
$caminho = '../img/logo.png';
$imagem_temp = @$_FILES['foto-logo']['tmp_name']; 

if (!empty($_FILES['foto-logo']['name'])) {
    if (($_FILES['foto-logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        echo 'Falha ao receber a logo. Verifique o tamanho do arquivo e tente novamente.';
        exit();
    }
    $ext = strtolower(pathinfo($_FILES['foto-logo']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'png') {
        echo 'A logo deve estar em formato PNG!';
        exit();
    }
    if (!is_uploaded_file($imagem_temp) || ($imgInfo = @getimagesize($imagem_temp)) === false || ($imgInfo['mime'] ?? '') !== 'image/png') {
        echo 'Arquivo PNG inválido. Selecione uma imagem PNG válida.';
        exit();
    }
    if (!is_dir(dirname($caminho)) || !is_writable(dirname($caminho))) {
        echo 'A pasta sistema/img não permite gravação. Verifique as permissões na Hostinger.';
        exit();
    }
    if (!move_uploaded_file($imagem_temp, $caminho)) {
        echo 'Não foi possível salvar a logo em sistema/img/logo.png. Verifique as permissões.';
        exit();
    }
    clearstatcache(true, $caminho);
}


// Logo de relatorio: converte PNG para JPEG, pois os relatorios leem logo.jpg.
if (!empty($_FILES['foto-logo-rel']['name'])) {
    $upload = $_FILES['foto-logo-rel'];
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
        echo 'Falha no upload da logo do relatório.'; exit();
    }
    $info = @getimagesize($upload['tmp_name']);
    $mime = $info['mime'] ?? '';
    $destino = '../img/logo.jpg';
    if ($mime === 'image/jpeg') {
        $ok = @move_uploaded_file($upload['tmp_name'], $destino);
    } elseif ($mime === 'image/png') {
        if (!function_exists('imagecreatefrompng')) { echo 'Servidor sem suporte a conversão PNG. Envie JPG para a logo do relatório.'; exit(); }
        $origem = @imagecreatefrompng($upload['tmp_name']);
        if (!$origem) { echo 'PNG inválido para logo do relatório.'; exit(); }
        $fundo = imagecreatetruecolor(imagesx($origem), imagesy($origem));
        $branco = imagecolorallocate($fundo, 255, 255, 255);
        imagefill($fundo, 0, 0, $branco);
        imagecopy($fundo, $origem, 0, 0, 0, 0, imagesx($origem), imagesy($origem));
        $ok = @imagejpeg($fundo, $destino, 92);
        imagedestroy($origem); imagedestroy($fundo);
    } else { echo 'Logo do relatório deve ser PNG ou JPG.'; exit(); }
    if (!$ok) { echo 'Não foi possível gravar a logo do relatório. Verifique permissões da pasta img.'; exit(); }
}

// Ícone: o SAS exibe icone.png, portanto salvar no mesmo arquivo.
if (!empty($_FILES['foto-icone']['name'])) {
    $upload = $_FILES['foto-icone'];
    $info = @getimagesize($upload['tmp_name'] ?? '');
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($info['mime'] ?? '') !== 'image/png' || !@move_uploaded_file($upload['tmp_name'], '../img/icone.png')) {
        echo 'Não foi possível salvar o ícone. Envie um PNG válido e verifique as permissões.'; exit();
    }
}

//foto ass
$caminho = '../img/assinatura.jpg';
$imagem_temp = @$_FILES['assinatura_rel']['tmp_name']; 

if(@$_FILES['assinatura_rel']['name'] != ""){
	$ext = pathinfo(@$_FILES['assinatura_rel']['name'], PATHINFO_EXTENSION);   
	if($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG'){ 	
			
		move_uploaded_file($imagem_temp, $caminho);
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}


// Logo do painel
if (!empty($_FILES['foto-painel']['name'])) {
    $upload = $_FILES['foto-painel'];
    $info = @getimagesize($upload['tmp_name'] ?? '');
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($info['mime'] ?? '') !== 'image/png' || !@move_uploaded_file($upload['tmp_name'], '../img/foto-painel.png')) {
        echo 'Não foi possível salvar a logo do painel. Envie um PNG válido e verifique as permissões.'; exit();
    }
}

//foto fundo login
$nome_img = date('d-m-Y H:i:s') . '-' . @$_FILES['fundo_login']['name'];
$nome_img = preg_replace('/[ :]+/', '-', $nome_img);
$caminho = '../img/'.$nome_img;
$imagem_temp = @$_FILES['fundo_login']['tmp_name']; 

if(@$_FILES['fundo_login']['name'] != ""){
	$ext = pathinfo(@$_FILES['fundo_login']['name'], PATHINFO_EXTENSION);   
	if($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG'|| $ext == 'gif' || $ext == 'GIF' || $ext == 'webp' || $ext == 'WEBP'){			
		move_uploaded_file($imagem_temp, $caminho);
		$fundo_login = $nome_img;

		$query = $pdo->query("SELECT * FROM config where empresa = 0");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$fundo_login_antigo = @$res[0]['fundo_login'];

		if($fundo_login_antigo != "sem-foto.png"){
			@unlink('../img/'.$fundo_login_antigo);
		} 

	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}











$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, email = :email, telefone = :telefone, endereco = :endereco, instagram = :instagram, multa_atraso = :multa_atraso, juros_atraso = :juros_atraso, marca_dagua = :marca_dagua, marca_dagua = :marca_dagua, assinatura_recibo = :assinatura_recibo, impressao_automatica = :impressao_automatica, cnpj = :cnpj_sistema, entrar_automatico = :entrar_automatico, mostrar_preloader = :mostrar_preloader, ocultar_mobile = :ocultar_mobile, api_whatsapp = '$api_whatsapp', token_whatsapp = :token_whatsapp, instancia_whatsapp = :instancia_whatsapp, alterar_acessos = :alterar_acessos, dados_pagamento = :dados_pagamento, abertura_caixa = :abertura_caixa, access_token = :access_token, public_key = :public_key, limitar_recursos = :limitar_recursos, fundo_login = '$fundo_login', api_pagamento = :api_pagamento,  chave_api_asaas = :chave_api_asaas, alterar_api_whatsapp = :alterar_api_whatsapp, pagina_entrada = :pagina_entrada, multi_empresas = :multi_empresas, meta_descricao = :meta_descricao where empresa = 0");

$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":instagram", "$instagram");
$query->bindValue(":multa_atraso", "$multa_atraso");
$query->bindValue(":juros_atraso", "$juros_atraso");
$query->bindValue(":marca_dagua", "$marca_dagua");
$query->bindValue(":assinatura_recibo", "$assinatura_recibo");
$query->bindValue(":impressao_automatica", "$impressao_automatica");
$query->bindValue(":cnpj_sistema", "$cnpj_sistema");
$query->bindValue(":entrar_automatico", "$entrar_automatico");
$query->bindValue(":mostrar_preloader", "$mostrar_preloader");
$query->bindValue(":ocultar_mobile", "$ocultar_mobile");
$query->bindValue(":token_whatsapp", "$token_whatsapp");
$query->bindValue(":instancia_whatsapp", "$instancia_whatsapp");
$query->bindValue(":alterar_acessos", "$alterar_acessos");
$query->bindValue(":dados_pagamento", "$dados_pagamento");
$query->bindValue(":abertura_caixa", "$abertura_caixa");
$query->bindValue(":access_token", "$access_token");
$query->bindValue(":public_key", "$public_key");
$query->bindValue(":limitar_recursos", "$limitar_recursos");
$query->bindValue(":api_pagamento", "$api_pagamento");
$query->bindValue(":chave_api_asaas", "$chave_api_asaas");
$query->bindValue(":alterar_api_whatsapp", "$alterar_api_whatsapp");
$query->bindValue(":pagina_entrada", "$pagina_entrada");
$query->bindValue(":multi_empresas", "$multi_empresas");
$query->bindValue(":meta_descricao", "$meta_descricao");
$query->execute();

echo 'Editado com Sucesso';
 ?>