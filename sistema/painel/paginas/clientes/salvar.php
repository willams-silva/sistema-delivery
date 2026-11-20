<?php
@session_start();
$id_empresa = (int) ($_SESSION['empresa'] ?? 0);
$id_usuario = (int) ($_SESSION['id'] ?? 0);
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa <= 0 || $id_usuario <= 0){
    echo 'Sem Sessão da Empresa, refaça o Login!';
    exit();
}
$tabela = 'clientes';

// Operador autenticado validado acima.


$nome = $_POST['nome'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];
$endereco = $_POST['endereco'];
$data_nasc = $_POST['data_nasc'];
$cpf = $_POST['cpf'];
$numero = $_POST['numero'];
$bairro = $_POST['bairro'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];
$cep = $_POST['cep'];
$id = (int) ($_POST['id'] ?? 0);
$complemento = $_POST['complemento'];


// Campos obrigatórios: validar também no servidor.
$nome = trim((string) $nome);
$telefone = trim((string) $telefone);
$cpf = trim((string) $cpf);
if ($nome === '') {
    echo 'Informe o nome do cliente!';
    exit();
}
$telefone_digitos = preg_replace('/\\D/', '', $telefone);
if (!in_array(strlen($telefone_digitos), [10, 11], true)) {
    echo 'Informe um telefone com DDD válido!';
    exit();
}
$cpf_digitos = preg_replace('/\\D/', '', $cpf);
if (strlen($cpf_digitos) !== 11) {
    echo 'Informe um CPF válido!';
    exit();
}
require_once("../../validar_cpf.php");


//validacao email
if($email != "") {
    $query = $pdo->query("SELECT * from $tabela where email = '$email' and empresa = '$id_empresa' ");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $id_reg = @$res[0]['id'];
    if(@count($res) > 0 and $id != $id_reg) {
        echo 'Email já Cadastrado!';
        exit();
    }
}


//validacao telefone
if($telefone != "") {
    $query = $pdo->query("SELECT * from $tabela where telefone = '$telefone' and empresa = '$id_empresa'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $id_reg = @$res[0]['id'];
    if(@count($res) > 0 and $id != $id_reg) {
        echo 'Telefone já Cadastrado!';
        exit();
    }
}

if($data_nasc == "") {
    $nasc = '';
} else {
    $nasc = " ,data_nasc = '$data_nasc'";

}

if($id === 0) {
    $query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, email = :email, telefone = :telefone, data_cad = curDate(), endereco = :endereco, cpf = :cpf $nasc, usuario = '$id_usuario', numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, complemento = :complemento, empresa = '$id_empresa' ");

} else {


    $query = $pdo->prepare("UPDATE $tabela SET nome = :nome, email = :email, telefone = :telefone, endereco = :endereco, cpf = :cpf $nasc , numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, complemento = :complemento where id = '$id' and empresa = '$id_empresa'");
}


$query->bindValue(":nome", "$nome");
$query->bindValue(":email", "$email");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":cpf", "$cpf");
$query->bindValue(":numero", "$numero");
$query->bindValue(":bairro", "$bairro");
$query->bindValue(":cidade", "$cidade");
$query->bindValue(":estado", "$estado");
$query->bindValue(":cep", "$cep");
$query->bindValue(":complemento", "$complemento");
$query->execute();

echo 'Salvo com Sucesso';


?>
