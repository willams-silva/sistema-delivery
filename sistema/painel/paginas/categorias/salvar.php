<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}

$tabela = 'categorias';

$id = $_POST['id'];
$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$mais_sabores = $_POST['mais_sabores'];
$delivery = $_POST['delivery'];

$nome_novo = @strtolower( @preg_replace("[^a-zA-Z0-9-]", "-", 
        strtr(@utf8_decode(trim($nome)), @utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),
        "aaaaeeiooouuncAAAAEEIOOOUUNC-")) );
$url = @preg_replace('/[ -]+/' , '-' , $nome_novo);
$url = @str_replace('+', '-', $url);
$url = @str_replace('/', '-', $url);
$url = @str_replace('"', '-', $url);
$url = @str_replace('%', '', $url);

//validar nome
$query = $pdo->query("SELECT * from $tabela where nome = '$nome' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Categoria já Cadastrado, escolha outro nome!!';
	exit();
}



//validar troca da foto
$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$foto = $res[0]['foto'];
}else{
	$foto = 'sem-foto.jpg';
}




// Upload seguro: manter a imagem anterior se a nova falhar.
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['foto']['tmp_name'])) {
        echo 'Falha ao receber a imagem. Tente novamente.';
        exit();
    }
    $imagem_temp = $_FILES['foto']['tmp_name'];
    $extensoes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $info_imagem = @getimagesize($imagem_temp);
    if (!in_array($ext, $extensoes, true) || !$info_imagem || $info_imagem[0] > 1400) {
        echo 'Imagem inválida ou maior que 1400px de largura. Use JPG, PNG, GIF ou WEBP.';
        exit();
    }
    $pasta_imagens = dirname(__DIR__, 5) . '/upload_delivery/categorias/';
    if (!is_dir($pasta_imagens) || !is_writable($pasta_imagens)) {
        echo 'Pasta de imagens indisponível para gravação. Verifique a hospedagem.';
        exit();
    }
    $novo_nome = date('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($imagem_temp, $pasta_imagens . $novo_nome)) {
        echo 'Não foi possível salvar a imagem no servidor.';
        exit();
    }
    // Não apagar a imagem anterior: pode ser necessária para recuperação.
    $foto = $novo_nome;
}



if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, descricao = :descricao,  ativo = 'Sim', foto = '$foto', url = '$url', mais_sabores = '$mais_sabores', delivery = '$delivery', empresa = '$id_empresa' ");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, descricao = :descricao,  ativo = 'Sim', foto = '$foto', url = '$url', mais_sabores = '$mais_sabores', delivery = '$delivery' WHERE id = '$id'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":descricao", "$descricao");
$query->execute();

echo 'Salvo com Sucesso';

?>