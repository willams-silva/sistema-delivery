<?php
@session_start();
$id_empresa = (int)($_SESSION['empresa'] ?? 0);
require_once("../../../conexao.php");
require_once("../../buscar_config.php");
if ($id_empresa <= 0) { echo 'Sem Sessão da Empresa, refaça o Login!'; exit(); }
$nome = trim((string)($_POST['nome'] ?? ''));
$categoria = (int)($_POST['id'] ?? 0);
$id_editar = (int)($_POST['id_adc_editar'] ?? 0);
$valor = str_replace(',', '.', trim((string)($_POST['valor'] ?? '0')));
$tipo = (string)($_POST['tipo'] ?? 'adicional');
if ($nome === '' || $categoria <= 0 || !is_numeric($valor) || (float)$valor < 0) { echo 'Confira nome, categoria e valor.'; exit(); }
$stmt = $pdo->prepare("SELECT nome FROM categorias WHERE id = ? AND empresa = ? LIMIT 1");
$stmt->execute([$categoria, $id_empresa]);
$nome_categoria = strtolower(trim((string)$stmt->fetchColumn()));
if ($nome_categoria === '') { echo 'Categoria inválida.'; exit(); }
$permite_grupos = (($url_site ?? '') === 'rainhadastapiocas' && $nome_categoria === 'tapiocas salgadas');
if (!$permite_grupos) $tipo = 'adicional';
if (!in_array($tipo, ['adicional', 'opcional', 'proteina'], true)) { echo 'Tipo inválido.'; exit(); }
if ($tipo === 'opcional') $valor = '0';
// Na Rainha, TAPIOCAS SALGADAS pode ter o mesmo ingrediente em grupos distintos.
// Em todas as outras categorias, manter a regra anterior de nome único.
if ($permite_grupos) {
    $stmt = $pdo->prepare("SELECT id FROM adicionais_cat WHERE nome = ? AND categoria = ? AND empresa = ? AND tipo = ? AND id <> ? LIMIT 1");
    $stmt->execute([$nome, $categoria, $id_empresa, $tipo, $id_editar]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM adicionais_cat WHERE nome = ? AND categoria = ? AND empresa = ? AND id <> ? LIMIT 1");
    $stmt->execute([$nome, $categoria, $id_empresa, $id_editar]);
}
if ($stmt->fetchColumn()) { echo 'Item já cadastrado nesta categoria.'; exit(); }
if ($id_editar > 0) {
    $stmt = $pdo->prepare("UPDATE adicionais_cat SET nome = ?, valor = ?, tipo = ? WHERE id = ? AND categoria = ? AND empresa = ?");
    $stmt->execute([$nome, $valor, $tipo, $id_editar, $categoria, $id_empresa]);
} else {
    $stmt = $pdo->prepare("INSERT INTO adicionais_cat (nome, valor, tipo, categoria, ativo, empresa) VALUES (?, ?, ?, ?, 'Sim', ?)");
    $stmt->execute([$nome, $valor, $tipo, $categoria, $id_empresa]);
}
echo 'Salvo com Sucesso';
