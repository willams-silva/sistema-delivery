<?php 
require_once("conexao.php");
$email = filter_var(@$_POST['email-rec'], @FILTER_SANITIZE_STRING);

//VERIFICAR SE EXISTE USUÁRIO ADMIN CADASTRADO
$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email and nivel != 'Administrador'");
$query->bindValue(":email", "$email");
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg == 0){
	echo 'Este email não está cadastrado!';
	exit();
}else{

	$telefone = $res[0]['telefone'];
	
	$nova_senha = rand(100000, 100000000);
	$senha_crip = password_hash($nova_senha, PASSWORD_DEFAULT);
	$query = $pdo->prepare("UPDATE usuarios SET senha_crip = '$senha_crip' where email = :email");
	$query->bindValue(":email", "$email");
	$query->execute();

}

//enviar o email com a nova senha
$destinatario = $email;
$assunto = $nome_sistema . ' - Recuperação de Senha';
$mensagem = 'Sua senha é ' .$nova_senha;
$cabecalhos = "From: ".$email_sistema;
   
@mail($destinatario, $assunto, $mensagem, $cabecalhos);


	if($status_whatsapp == 'Api' and $telefone != ""){
		$tel_cliente_whats = '55'.preg_replace('/[ ()-]+/' , '' , @$telefone);
		$mensagem = '*'.$nome_sistema.'* %0A';
		$mensagem .= '_Recuperação de Senha_ %0A';		
		$mensagem .= 'Sua Senha é: *'.$nova_senha.'*';
		
		require("../js/ajax/api_texto.php");
}

echo 'Recuperado com Sucesso';

 ?>
