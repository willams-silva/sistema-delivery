<?php
@session_start();
require_once('../../sistema/conexao.php');

$id_empresa = @$_SESSION['empresa'];
$sessao = @$_SESSION['sessao_usuario'];
if ($id_empresa == '' || $sessao == '') {
    echo 'Sem Sessão da Empresa, acesse novamente!';
    exit();
}
require_once("../../sistema/painel/buscar_config.php");

$id = (int) (@$_POST['id'] ?? 0);
$id_sabor = (int) (@$_POST['id_sabor'] ?? 0);
$quantidade_recebida = (string) (@$_POST['quantidade'] ?? '0');
$quantidade = ($quantidade_recebida === 'Sim') ? 1 : (($quantidade_recebida === 'Não') ? 0 : (int)$quantidade_recebida);
$ids_selecionados = array_filter(array_map('intval', explode('-', trim((string) (@$_POST['ids'] ?? ''), '-'))));

if (!empty($_POST['limpar_sabor']) && $id_sabor > 0) {
    $stmt = $pdo->prepare("DELETE FROM temp WHERE id_sabor = ? AND sessao = ? AND tabela = 'adicionais' AND carrinho = '0' AND empresa = ?");
    $stmt->execute([$id_sabor, $sessao, $id_empresa]);
    echo 'Alterado com Sucesso';
    exit();
}

if ($id <= 0) {
    echo 'Adicional inválido';
    exit();
}

// Quando há vínculo com sabor, ele precisa estar selecionado e pertencer à categoria do adicional.
if ($id_sabor > 0) {
    if (!in_array($id_sabor, $ids_selecionados, true)) {
        echo 'Selecione o sabor antes dos adicionais';
        exit();
    }
    $stmt = $pdo->prepare("SELECT p.id, p.categoria FROM produtos p WHERE p.id = ? LIMIT 1");
    $stmt->execute([$id_sabor]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT id, valor, tipo, categoria FROM adicionais_cat WHERE id = ? AND empresa = ? AND ativo = 'Sim' LIMIT 1");
    $stmt->execute([$id, $id_empresa]);
    $adicional = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$produto || !$adicional || (int)$produto['categoria'] !== (int)$adicional['categoria']) {
        echo 'Adicional não pertence a este sabor';
        exit();
    }
} else {
    $stmt = $pdo->prepare("SELECT id, valor, tipo FROM adicionais_cat WHERE id = ? AND empresa = ? AND ativo = 'Sim' LIMIT 1");
    $stmt->execute([$id, $id_empresa]);
    $adicional = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$adicional) {
        echo 'Adicional não encontrado';
        exit();
    }
}

// Itens opcionais representam uma retirada, nunca uma cobrança.
// Impedir que uma requisição forjada altere preços ou marque múltiplas retiradas.
$tipo_adicional = (string)($adicional['tipo'] ?? 'adicional');
if ($tipo_adicional === 'opcional') {
    $stmt_tipo = $pdo->prepare("SELECT c.nome FROM categorias c JOIN adicionais_cat a ON a.categoria = c.id WHERE a.id = ? AND c.empresa = ? LIMIT 1");
    $stmt_tipo->execute([$id, $id_empresa]);
    $nome_cat = strtolower(trim((string)$stmt_tipo->fetchColumn()));
    if (($url_site ?? '') !== 'rainhadastapiocas' || $nome_cat !== 'tapiocas salgadas' || $id_sabor <= 0) {
        echo 'Opcional inválido para esta categoria';
        exit();
    }
    $quantidade = $quantidade > 0 ? 1 : 0;
    $adicional['valor'] = 0;
}

if ($quantidade <= 0) {
    $stmt = $pdo->prepare("DELETE FROM temp WHERE id_item = ? AND id_sabor = ? AND sessao = ? AND tabela = 'adicionais' AND carrinho = '0' AND categoria = 'Sim' AND empresa = ?");
    $stmt->execute([$id, $id_sabor, $sessao, $id_empresa]);
    echo 'Alterado com Sucesso';
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM temp WHERE id_item = ? AND id_sabor = ? AND sessao = ? AND tabela = 'adicionais' AND carrinho = '0' AND empresa = ? LIMIT 1");
$stmt->execute([$id, $id_sabor, $sessao, $id_empresa]);
$existente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existente) {
    $stmt = $pdo->prepare("UPDATE temp SET quantidade = ?, valor_item = ? WHERE id = ? AND empresa = ?");
    $stmt->execute([$quantidade, $adicional['valor'], $existente['id'], $id_empresa]);
} else {
    $stmt = $pdo->prepare("INSERT INTO temp SET sessao = ?, tabela = 'adicionais', id_item = ?, id_sabor = ?, carrinho = '0', data = curDate(), categoria = 'Sim', quantidade = ?, valor_item = ?, empresa = ?");
    $stmt->execute([$sessao, $id, $id_sabor, $quantidade, $adicional['valor'], $id_empresa]);
}

echo 'Alterado com Sucesso';
