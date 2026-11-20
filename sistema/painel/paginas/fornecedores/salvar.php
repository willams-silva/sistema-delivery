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
$tabela = 'fornecedores';
$operador = $pdo->prepare("SELECT nivel FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
$operador->execute([':id' => $id_operador, ':empresa' => $id_empresa]);
$nivel_operador = $operador->fetchColumn();
if ($nivel_operador === false) {
    http_response_code(403);
    exit('Operador não autorizado');
}
if ($nivel_operador !== 'Administrador') {
    $permissao = $pdo->prepare("SELECT 1 FROM usuarios_permissoes up INNER JOIN acessos a ON a.id = up.permissao WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = 'fornecedores' LIMIT 1");
    $permissao->execute([':usuario' => $id_operador, ':empresa' => $id_empresa]);
    if (!$permissao->fetchColumn()) {
        http_response_code(403);
        exit('Sem permissão para gerenciar fornecedores');
    }
}


$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$endereco = $_POST['endereco'];
$pix = $_POST['pix'];
$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $verificar = $pdo->prepare("SELECT 1 FROM fornecedores WHERE id = :id AND empresa = :empresa LIMIT 1");
    $verificar->execute([':id' => $id, ':empresa' => $id_empresa]);
    if (!$verificar->fetchColumn()) {
        http_response_code(404);
        exit('Fornecedor não encontrado');
    }
}
$numero =@ $_POST['numero'];
$bairro = @$_POST['bairro'];
$cidade = @$_POST['cidade'];
$estado = @$_POST['estado'];
$cep = @$_POST['cep'];
$cnpj = @$_POST['cnpj'];
$complemento = @$_POST['complemento'];
$tipo_chave = @$_POST['tipo_chave'];


// Validação da Chave Pix
if($pix == '' and $tipo_chave != ''){
	echo 'Preencher o campo Pix';
	exit();
}

if ($pix != '' and $tipo_chave == '') {
	echo 'Escola o Tipo de Chave';
	exit();
}



//validacao email
if($email != ""){
	$query = $pdo->prepare("SELECT id FROM fornecedores WHERE email = :email AND empresa = :empresa");
    $query->execute([':email' => $email, ':empresa' => $id_empresa]);
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res) > 0 and $id != $res[0]['id']){
		echo 'Email já Cadastrado, escolha outro!!';
		exit();
	}
}


//validacao telefone
$query = $pdo->prepare("SELECT id FROM fornecedores WHERE telefone = :telefone AND empresa = :empresa");
$query->execute([':telefone' => $telefone, ':empresa' => $id_empresa]);
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_reg = @$res[0]['id'];
if(@count($res) > 0 and $id != $id_reg){
	echo 'Telefone já Cadastrado!';
	exit();
}

if($id === 0){
$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, email = :email, telefone = :telefone, data = curDate(), endereco = :endereco, pix = :pix, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, cnpj = :cnpj, complemento = :complemento, tipo_chave = :tipo_chave, empresa = :empresa ");
	
}else{
$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, email = :email, telefone = :telefone, endereco = :endereco, pix = :pix, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, cnpj = :cnpj, complemento = :complemento, tipo_chave = :tipo_chave where id = :id and empresa = :empresa");
}
$query->bindValue(':tipo_chave', $tipo_chave);
$query->bindValue(':empresa', $id_empresa, PDO::PARAM_INT);
if ($id > 0) {
    $query->bindValue(':id', $id, PDO::PARAM_INT);
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
$query->bindValue(":cnpj", "$cnpj");
$query->bindValue(":complemento", "$complemento");
$query->execute();

echo 'Salvo com Sucesso';


 ?>
