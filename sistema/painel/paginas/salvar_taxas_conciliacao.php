<?php
@session_start();
require_once('../../conexao.php');
if (!in_array($_SESSION['nivel'] ?? '', ['Administrador','Gerente'], true)) {
 http_response_code(403); exit('Acesso negado.');
}
$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario = (int)($_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
if ($empresa <= 0 || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
 http_response_code(400); exit('Requisição inválida.');
}
$token = (string)($_POST['csrf_conc'] ?? '');
if (empty($_SESSION['csrf_conc']) || !hash_equals($_SESSION['csrf_conc'], $token)) {
 http_response_code(403); exit('Sessão expirada. Atualize a página.');
}
$taxas = $_POST['taxas'] ?? [];
if (!is_array($taxas) || count($taxas) > 100) { http_response_code(400); exit('Taxas inválidas.'); }
$dados = [];
foreach ($taxas as $forma=>$valor) {
 if (!ctype_digit((string)$forma) || (int)$forma <= 0 || !is_scalar($valor)) { http_response_code(400); exit('Forma inválida.'); }
 $v = str_replace(',', '.', trim((string)$valor));
 if ($v === '') continue; // Campos vazios não sobrescrevem taxas já salvas.
 if (!preg_match('/^\d{1,3}(?:\.\d{1,4})?$/', $v) || (float)$v > 100) { http_response_code(400); exit('Taxa fora do intervalo de 0 a 100%.'); }
 $dados[(int)$forma] = number_format((float)$v,4,'.','');
}
try {
 $pdo->beginTransaction();
 $validar=$pdo->prepare('SELECT id FROM formas_pgto WHERE id=:forma AND empresa=:empresa LIMIT 1');
 $salvar=$pdo->prepare('INSERT INTO taxas_formas_pagamento (empresa,forma_pgto,taxa_percentual,atualizado_por)
 VALUES (:empresa,:forma,:taxa,:usuario) ON DUPLICATE KEY UPDATE taxa_percentual=VALUES(taxa_percentual),atualizado_por=VALUES(atualizado_por)');
 foreach ($dados as $forma=>$taxa) {
  $validar->execute([':forma'=>$forma,':empresa'=>$empresa]);
  if (!$validar->fetchColumn()) throw new RuntimeException('Forma de pagamento não pertence ao estabelecimento.');
  $salvar->execute([':empresa'=>$empresa,':forma'=>$forma,':taxa'=>$taxa,':usuario'=>$usuario ?: null]);
 }
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&taxas_salvas=1',true,303);
 exit;
} catch (Throwable $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Erro ao salvar taxas financeiras: '.$e->getMessage());
 http_response_code(500);
 exit('Não foi possível salvar as taxas. Verifique se a migração SQL foi executada.');
}
