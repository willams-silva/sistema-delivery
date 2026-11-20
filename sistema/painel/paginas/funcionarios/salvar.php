<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_operador = (int) ($_SESSION['id'] ?? 0);
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa <= 0 || $id_operador <= 0){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'usuarios';
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $id_operador, ':empresa' => $id_empresa]);
$nivel_operador = $operador->fetchColumn();
if ($nivel_operador === false) {
    http_response_code(403);
    exit('Operador sem acesso a esta empresa');
}
if ($nivel_operador !== 'Administrador') {
    $acesso = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'funcionarios' LIMIT 1");
    $acesso->execute([':usuario' => $id_operador, ':empresa' => $id_empresa]);
    if (!$acesso->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão para gerenciar funcionários');
    }
}


$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$nivel = $_POST['nivel'];
if ($nivel === 'Administrador' && $nivel_operador !== 'Administrador') {
    http_response_code(403);
    exit('Somente administradores podem atribuir o nível Administrador');
}
$cargo = $pdo->prepare("SELECT 1 FROM cargos WHERE nome = :nome AND empresa = :empresa LIMIT 1");
$cargo->execute([':nome' => $nivel, ':empresa' => $id_empresa]);
if (!$cargo->fetchColumn()) {
    http_response_code(400);
    exit('Cargo inválido para esta empresa');
}
$endereco = $_POST['endereco'];
$senha = '123';
$senha_crip = password_hash($senha, PASSWORD_DEFAULT);
$pix = $_POST['pix'];
$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $verificar = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
    $verificar->execute([':id' => $id, ':empresa' => $id_empresa]);
    $nivel_atual = $verificar->fetchColumn();
    if ($nivel_atual === false) {
        http_response_code(404);
        exit('Funcionário não encontrado');
    }
    if ($nivel_atual === 'Administrador' && $nivel_operador !== 'Administrador') {
        http_response_code(403);
        exit('Somente administradores podem editar administradores');
    }
}
$numero = @$_POST['numero'];
$bairro = @$_POST['bairro'];
$cidade = @$_POST['cidade'];
$estado = @$_POST['estado'];
$cep = @$_POST['cep'];
$data_nasc = @$_POST['data_nasc'];
$cpf = @$_POST['cpf'];
$acessar_painel = @$_POST['acessar_painel'];
$complemento = @$_POST['complemento'];
$tipo_chave = @$_POST['tipo_chave'];



// Validação da Chave Pix
if ($pix == '' and $tipo_chave != '') {
	echo 'Preencher o campo Pix';
	exit();
}

if ($pix != '' and $tipo_chave == '') {
	echo 'Escola o Tipo de Chave';
	exit();
}



if ($cpf != "") {
	require_once("../../validar_cpf.php");
}


//validacao telefone
$query = $pdo->prepare("SELECT id FROM usuarios WHERE telefone = :telefone AND empresa = :empresa");
$query->execute([':telefone' => $telefone, ':empresa' => $id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_reg = @$res[0]['id'];
if (@count($res) > 0 and $id != $id_reg) {
	echo 'Telefone já Cadastrado!';
	exit();
}

if ($data_nasc == "") {
	$nasc = '';
} else {
	$nasc = " ,data_nasc = '$data_nasc'";

}





//validar troca da foto
$query = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id AND empresa = :empresa");
$query->execute([':id' => $id, ':empresa' => $id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
	$foto = $res[0]['foto'];
} else {
	$foto = 'sem-foto.jpg';
}




//SCRIPT PARA SUBIR FOTO NO SERVIDOR
$nome_img = date('d-m-Y H:i:s') . '-' . basename((string) (@$_FILES['foto']['name'] ?? ''));
$nome_img = preg_replace('/[ :]+/', '-', $nome_img);

$caminho = '../../images/perfil/' . $nome_img;

$imagem_temp = @$_FILES['foto']['tmp_name'];

if (@$_FILES['foto']['name'] != "") {
    if (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
        !is_uploaded_file($imagem_temp) ||
        ($_FILES['foto']['size'] ?? 0) > 5 * 1024 * 1024) {
        http_response_code(400);
        exit('Imagem inválida ou maior que 5 MB');
    }

	$ext = strtolower(pathinfo($nome_img, PATHINFO_EXTENSION));
    $info_imagem = @getimagesize($imagem_temp);
    if ($info_imagem === false || !in_array($info_imagem[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF], true)) {
        http_response_code(400);
        exit('O arquivo enviado não é uma imagem válida');
    }
	if ($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg' or $ext == 'gif' or $ext == 'PNG' or $ext == 'JPG' or $ext == 'JPEG' or $ext == 'GIF') {

		//EXCLUO A FOTO ANTERIOR
		if ($foto != "sem-foto.jpg") {
			@unlink('../../images/perfil/' . $foto);
		}

		$foto = $nome_img;

		//pegar o tamanho da imagem
		list($largura, $altura) = getimagesize($imagem_temp);

		if ($largura > 1400) {


			if ($ext == 'png') {
				$image = imagecreatefrompng($imagem_temp);
			} else if ($ext == 'jpeg' or $ext == 'jpg') {
				$image = imagecreatefromjpeg($imagem_temp);
			} else {
				die("Formato de imagem não suportado.");
			}



			// Reduza a qualidade para 20% ajuste conforme necessário
			imagejpeg($image, $caminho, 20);
			imagedestroy($image);




		} else {
			move_uploaded_file($imagem_temp, $caminho);
		}



	} else {
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}




if ($id === 0) {
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, email = :email, senha = '', senha_crip = '$senha_crip', nivel = '$nivel', ativo = 'Sim', telefone = :telefone, data = curDate(), endereco = :endereco, pix = :pix $nasc, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, acessar_painel = '$acessar_painel', complemento = :complemento, tipo_chave = '$tipo_chave', foto = '$foto', empresa = '$id_empresa' ");


	//api whats
	if ($api_whatsapp != 'Não' and $telefone != '' and $acessar_painel == 'Sim') {
		$telefone_envio = '55' . preg_replace('/[ ()-]+/', '', $telefone);

		$mensagem_whatsapp = '*' . $nome_sistema . '*%0A%0A';
		$mensagem_whatsapp .= '_Olá ' . $nome . ' Você foi Cadastrado no Sistema!!_ %0A';
		$mensagem_whatsapp .= '*Email:* ' . $email . ' %0A';
		$mensagem_whatsapp .= '*Senha:* ' . $senha . ' %0A';
		$mensagem_whatsapp .= '*Url Acesso:* ' . $url_sistema . ' %0A%0A';
		$mensagem_whatsapp .= '_Após acessar seu painel, adicone uma foto e trocar a senha para uma de sua preferência!_';

		require('../../apis/texto.php');



	}


} else {
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, email = :email, nivel = '$nivel', telefone = :telefone, endereco = :endereco, pix = :pix $nasc, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, acessar_painel = '$acessar_painel', complemento = :complemento, tipo_chave = '$tipo_chave', foto = '$foto' where id = '$id' and empresa = '$id_empresa'");
}
$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":pix", "$pix");
$query->bindValue(":numero", "$numero");
$query->bindValue(":bairro", "$bairro");
$query->bindValue(":cidade", "$cidade");
$query->bindValue(":estado", "$estado");
$query->bindValue(":cep", "$cep");
$query->bindValue(":complemento", "$complemento");
$query->execute();






//enviar email
if ($email != '') {
	$url_logo = $url_sistema . 'img/logo.png';
	$destinatario = $email;
	$assunto = 'Cadastrado no sistema ' . $nome_sistema;
	$mensagem_email = 'Olá ' . $nome . ' você foi cadastrado no sistema <br>';
	$mensagem_email .= '<b>Usuário</b>: ' . $email . '<br>';
	$mensagem_email .= '<b>Senha: </b>' . $senha . '<br><br>';
	$mensagem_email .= 'Url Acesso: <br><a href="' . $url_sistema . '">' . $url_sistema . '</a><br><br>';
	$mensagem_email .= '<i>Após acessar seu painel, adicone uma foto e trocar a senha para uma de sua preferência!</i>' . '<br><br>';
	$mensagem_email .= "<img src='" . $url_logo . "' width='200px'> ";
}


require('../../apis/disparar_email.php');

echo 'Salvo com Sucesso';

?>