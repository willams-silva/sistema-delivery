<?php 
@session_set_cookie_params(['httponly' => true]);
@session_start();
@session_regenerate_id(true);
require_once("conexao.php");

$email = filter_var(@$_POST['email'], @FILTER_SANITIZE_STRING);
$senha = filter_var(@$_POST['senha'], @FILTER_SANITIZE_STRING);

$query = $pdo->prepare("SELECT * FROM usuarios WHERE (email = :email or cpf = :email) order by id desc");
$query->bindValue(":email", "$email");
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

	$hash = $res[0]['senha_crip'];

$senhaValida = password_verify($senha, $hash);

// Compatibilidade com senhas antigas em MD5
if (!$senhaValida && preg_match('/^[a-f0-9]{32}$/i', $hash)) {
    if (hash_equals(strtolower($hash), md5($senha))) {
        $senhaValida = true;

        // Atualiza para um hash seguro
        $novoHash = password_hash($senha, PASSWORD_DEFAULT);

        $update = $pdo->prepare(
            "UPDATE usuarios SET senha_crip = :senha WHERE id = :id"
        );

        $update->execute([
            ':senha' => $novoHash,
            ':id' => $res[0]['id']
        ]);
    }
}

if (!$senhaValida) {
    echo '<script>alert("Usuário ou senha incorretos!");</script>';
    echo '<script>window.location="index.php";</script>';
    exit();
}

	$_SESSION['id'] = $res[0]['id'];
	$_SESSION['nome'] = $res[0]['nome'];
	$_SESSION['nivel']= $res[0]['nivel'];
	$_SESSION['ativo'] = $res[0]['ativo'];
	$_SESSION['aut_token_8952'] = 'fdsfdsafda885222';

	if($_SESSION['ativo'] == 'Sim'){
		echo "<script>window.location='painel'</script>";
	}else{
		echo "<script>window.alert('Usuário Inativo!')</script>";
		echo "<script>window.location='index.php'</script>";
	}
	
}else{
	echo "<script>window.alert('Usuário ou Senha Incorretos!')</script>";
	echo "<script>window.location='index.php'</script>";
}

 ?>