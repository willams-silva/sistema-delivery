<?php
@session_start();
$id_empresa = $_SESSION['empresa'];
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if($id_empresa == ""){
	echo 'Sem Sessão da Empresa, refaça o Login!';
	exit();
}
$tabela = 'produtos';

$id = $_POST['id'];
$nome = $_POST['nome'];
$valor_compra = isset($_POST['valor_compra']) && $_POST['valor_compra'] !== '' ? str_replace(',', '.', $_POST['valor_compra']) : 0;
$valor_venda = isset($_POST['valor_venda']) && $_POST['valor_venda'] !== '' ? str_replace(',', '.', $_POST['valor_venda']) : 0;

$descricao = $_POST['descricao'];
$nivel_estoque = isset($_POST['nivel_estoque']) && $_POST['nivel_estoque'] !== '' ? $_POST['nivel_estoque'] : 0;
$tem_estoque = $_POST['tem_estoque'];
$promocao = @$_POST['promocao'];
$combo = @$_POST['combo'];
$preparado = @$_POST['preparado'];
$delivery = @$_POST['delivery'];
$categoria = isset($_POST['categoria']) && $_POST['categoria'] !== '' ? $_POST['categoria'] : 0;
$val_promocional = isset($_POST['val_promocional']) && $_POST['val_promocional'] !== '' ? str_replace(',', '.', $_POST['val_promocional']) : 0;
$unidade = isset($_POST['unidade']) && $_POST['unidade'] !== '' ? $_POST['unidade'] : 0;
$codigo = $_POST['codigo'];


if($promocao == 'Sim' and $val_promocional <= 0){
	echo 'Você precisa adicionar um valor Promocional ou desativar o campo Pormoção!';
	die;
}

$valor_venda = str_replace(',', '.', $valor_venda);

$nome_novo = strtolower( preg_replace("[^a-zA-Z0-9-]", "-", 
        strtr(@utf8_decode(trim($nome)), @utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"),
        "aaaaeeiooouuncAAAAEEIOOOUUNC-")) );
$url = preg_replace('/[ -]+/' , '-' , $nome_novo);
$url = str_replace('+', '-', $url);
$url = str_replace('/', '-', $url);
$url = str_replace('"', '-', $url);
$url = str_replace('%', '', $url);


if($categoria == 0 || $categoria == ""){
	echo 'Cadastre uma Categoria de Produtos para o Produto';
	exit();
}



$limite_mista_validacao = (int)($_POST['limite_proteinas_mista'] ?? 0);
$stmt_cat_validacao = $pdo->prepare("SELECT nome FROM categorias WHERE id = ? AND empresa = ? LIMIT 1");
$stmt_cat_validacao->execute([(int)$categoria, $id_empresa]);
if (strtolower(trim((string)$stmt_cat_validacao->fetchColumn())) === 'tapiocas salgadas' && !in_array($limite_mista_validacao, [0, 2, 3], true)) {
    echo 'Selecione 2 ou 3 proteínas inclusas para a tapioca mista.';
    exit();
}

//validar nome
$query = $pdo->query("SELECT * from $tabela where nome = '$nome' and empresa = '$id_empresa' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Produto já Cadastrado, escolha outro nome!!';
	exit();
}

//validar codigo
if($codigo != ""){
	$query = $pdo->query("SELECT * from $tabela where codigo = '$codigo' and empresa = '$id_empresa' ");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	if(@count($res) > 0 and $id != $res[0]['id']){
		echo 'Código já Cadastrado, escolha outro!!';
		exit();
	}
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
    if (!in_array($ext, $extensoes, true) || !$info_imagem || !in_array($info_imagem['mime'] ?? '', ['image/jpeg','image/png','image/gif','image/webp'], true) || $info_imagem[0] > 6000 || $info_imagem[1] > 6000 || $_FILES['foto']['size'] > 10 * 1024 * 1024) {
        echo 'Imagem inválida. Use JPG, PNG, GIF ou WEBP de até 6000 x 6000 pixels e 10 MB.';
        exit();
    }
    $pasta_imagens = dirname(__DIR__, 5) . '/upload_delivery/produtos/';
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


if($categoria == ""){
	$categoria = 0;
}

if($valor_compra == ""){
	$valor_compra = 0;
}

if($valor_venda == ""){
	$valor_venda = 0;
}

if($nivel_estoque == ""){
	$nivel_estoque = 0;
}

if($val_promocional == ""){
	$val_promocional = 0;
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, categoria = '$categoria', valor_compra = :valor_compra, valor_venda = :valor_venda, descricao = :descricao, foto = '$foto', nivel_estoque = '$nivel_estoque', tem_estoque = '$tem_estoque', ativo = 'Sim', url = '$url', promocao = '$promocao', combo = '$combo', preparado = '$preparado', delivery = '$delivery', val_promocional = :val_promocional, unidade = :unidade, empresa = '$id_empresa', codigo = :codigo ");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, categoria = '$categoria', valor_compra = :valor_compra, valor_venda = :valor_venda, descricao = :descricao, foto = '$foto', nivel_estoque = '$nivel_estoque', tem_estoque = '$tem_estoque', url = '$url', promocao = '$promocao', combo = '$combo', preparado = '$preparado', delivery = '$delivery', val_promocional = :val_promocional, unidade = :unidade, codigo = :codigo WHERE id = '$id'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":valor_venda", "$valor_venda");
$query->bindValue(":valor_compra", "$valor_compra");
$query->bindValue(":descricao", "$descricao");
$query->bindValue(":val_promocional", "$val_promocional");
$query->bindValue(":unidade", "$unidade");
$query->bindValue(":codigo", "$codigo");
$query->execute();

// Configuração por produto; não interfere nas demais categorias.
$limite_mista = (int)($_POST['limite_proteinas_mista'] ?? 0);
$stmt_categoria_mista = $pdo->prepare("SELECT nome FROM categorias WHERE id = ? AND empresa = ? LIMIT 1");
$stmt_categoria_mista->execute([(int)$categoria, $id_empresa]);
$nome_categoria_mista = strtolower(trim((string)$stmt_categoria_mista->fetchColumn()));
if ($nome_categoria_mista === 'tapiocas salgadas' && in_array($limite_mista, [2, 3], true)) {
    $id_config_mista = $id !== '' ? (int)$id : (int)$pdo->lastInsertId();
    $stmt_mista = $pdo->prepare("INSERT INTO tapiocas_mistas_config (produto_id, empresa, limite_proteinas) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE empresa = VALUES(empresa), limite_proteinas = VALUES(limite_proteinas)");
    $stmt_mista->execute([$id_config_mista, $id_empresa, $limite_mista]);
} elseif ($id !== '') {
    $stmt_mista = $pdo->prepare("DELETE FROM tapiocas_mistas_config WHERE produto_id = ? AND empresa = ?");
    $stmt_mista->execute([(int)$id, $id_empresa]);
}
echo 'Salvo com Sucesso';

?>