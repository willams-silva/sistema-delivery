<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa <= 0){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'usuarios';


if($modo_teste == 'Sim'){
	echo 'Esse recurso está desativado no modo teste!';
	exit();
}


$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$nivel = $_POST['nivel'];
if (!in_array($nivel, ['Administrador', 'Gerente', 'Funcionário', 'Funcionario'], true)) {
    http_response_code(400);
    exit('Nível de usuário inválido');
}
$endereco = $_POST['endereco'];
$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $verificar = $pdo->prepare("SELECT 1 FROM usuarios WHERE id = :id AND empresa = :empresa");
    $verificar->execute([':id' => $id, ':empresa' => $id_empresa]);
    if (!$verificar->fetchColumn()) {
        http_response_code(404);
        exit('Usuário não encontrado');
    }
}
$numero =@ $_POST['numero'];
$bairro = @$_POST['bairro'];
$cidade = @$_POST['cidade'];
$estado = @$_POST['estado'];
$cep = @$_POST['cep'];
$data_nasc = @$_POST['data_nasc'];
$cpf = @$_POST['cpf'];
$pix = @$_POST['pix'];
$acessar_painel = @$_POST['acessar_painel'];
$tipo_chave = @$_POST['tipo_chave'];
$complemento = @$_POST['complemento'];

if($cpf != ""){
	require_once("../../validar_cpf.php");
}


$senha = '123';
$senha_crip = password_hash($senha, PASSWORD_DEFAULT);

//validacao email
$query = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND empresa = :empresa");
$query->execute([':email' => $email, ':empresa' => $id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_reg = @$res[0]['id'];
if(@count($res) > 0 and $id != $id_reg){
	echo 'Email já Cadastrado!';
	exit();
}

//validacao telefone
$query = $pdo->prepare("SELECT id FROM usuarios WHERE telefone = :telefone AND empresa = :empresa");
$query->execute([':telefone' => $telefone, ':empresa' => $id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_reg = @$res[0]['id'];
if(@count($res) > 0 and $id != $id_reg){
	echo 'Telefone já Cadastrado!';
	exit();
}

if($data_nasc == ""){
	$nasc = '';	
}else{
	$nasc = " ,data_nasc = '$data_nasc'";
	
}

if($id === 0){
$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, email = :email, senha = '', senha_crip = '$senha_crip', nivel = '$nivel', ativo = 'Sim', foto = 'sem-foto.jpg', telefone = :telefone, data = curDate(), endereco = :endereco, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep $nasc, cpf = :cpf, pix = :pix, acessar_painel = 'Sim', tipo_chave = '$tipo_chave', complemento = :complemento, empresa = '$id_empresa' ");

//enviar whatsapp
if($api_whatsapp != 'Não' and $telefone != ''){

	$telefone_envio = '55'.preg_replace('/[ ()-]+/' , '' , $telefone);
	$mensagem_whatsapp = '*'.$nome_sistema.'*%0A';
	$mensagem_whatsapp .= '_Olá '.$nome.' Você foi Cadastrado no Sistema!!_ %0A';
	$mensagem_whatsapp .= '*Email:* '.$email.' %0A';
	$mensagem_whatsapp .= '*Senha:* '.$senha.' %0A';
	$mensagem_whatsapp .= '*Url Acesso:* %0A'.$url_sistema.' %0A%0A';
	$mensagem_whatsapp .= '_Ao entrar no sistema, troque sua senha!_';

	require('../../apis/texto.php');
}

//enviar email
if($email != ''){
	$url_logo = $url_sistema.'img/logo.png';
	$destinatario = $email;
	$assunto = 'Cadastrado no sistema '. $nome_sistema;
	$mensagem_email = 'Olá '.$nome.' você foi cadastrado no sistema <br>';
	$mensagem_email .= '<b>Usuário</b>: '.$email.'<br>';
	$mensagem_email .= '<b>Senha: </b>'.$senha.'<br><br>';
	$mensagem_email .= 'Url Acesso: <br><a href="'.$url_sistema.'">'.$url_sistema. '</a><br><br>';
	$mensagem_email .= '<i>Ao entrar no sistema, troque sua senha!</i>'. '<br><br>';
	$mensagem_email .= "<img src='".$url_logo."' width='200px'> ";
}


require('../../apis/disparar_email.php');
	
}else{
$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, email = :email, nivel = '$nivel', telefone = :telefone, endereco = :endereco, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep $nasc, cpf = :cpf, pix = :pix, acessar_painel = 'Sim', tipo_chave = '$tipo_chave', complemento = :complemento where id = '$id' and empresa = '$id_empresa'");
}
$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":numero", "$numero");
$query->bindValue(":bairro", "$bairro");
$query->bindValue(":cidade", "$cidade");
$query->bindValue(":estado", "$estado");
$query->bindValue(":cep", "$cep");
$query->bindValue(":cpf", "$cpf");
$query->bindValue(":pix", "$pix");
$query->bindValue(":complemento", "$complemento");
$query->execute();

echo 'Salvo com Sucesso';
 ?>