<?php
@session_start();
require_once('../../conexao.php');
if (!in_array($_SESSION['nivel']??'', ['Administrador','Gerente'],true)) {http_response_code(403);exit('Acesso negado.');}
$empresa=(int)($_SESSION['empresa']??0);
$usuario=(int)($_SESSION['id_usuario']??$_SESSION['id']??0);
if ($empresa<=0 || ($_SERVER['REQUEST_METHOD']??'')!=='POST') {http_response_code(400);exit('Requisição inválida.');}
if (empty($_SESSION['csrf_conc']) || !hash_equals($_SESSION['csrf_conc'],(string)($_POST['csrf_conc']??''))) {http_response_code(403);exit('Sessão expirada.');}
$prazos=$_POST['prazos']??[];
if (!is_array($prazos) || count($prazos)>100) {http_response_code(400);exit('Dados inválidos.');}
$dados=[];
foreach ($prazos as $forma=>$prazo) {
 if (!ctype_digit((string)$forma) || (int)$forma<=0 || !is_scalar($prazo)) {http_response_code(400);exit('Forma inválida.');}
 $prazo=trim((string)$prazo);
 if ($prazo==='') continue;
 if (!ctype_digit($prazo) || (int)$prazo>365) {http_response_code(400);exit('Prazo deve estar entre 0 e 365 dias.');}
 $dados[(int)$forma]=(int)$prazo;
}
try {
 $pdo->beginTransaction();
 $verificar=$pdo->prepare('SELECT id FROM formas_pgto WHERE id=:forma AND empresa=:empresa LIMIT 1');
 $salvar=$pdo->prepare('INSERT INTO prazos_repasse_financeiro (empresa,forma_pgto,prazo_dias,atualizado_por) VALUES (:empresa,:forma,:prazo,:usuario) ON DUPLICATE KEY UPDATE prazo_dias=VALUES(prazo_dias),atualizado_por=VALUES(atualizado_por)');
 foreach ($dados as $forma=>$prazo) {
  $verificar->execute([':empresa'=>$empresa,':forma'=>$forma]);
  if (!$verificar->fetchColumn()) throw new RuntimeException('Forma de pagamento inválida.');
  $salvar->execute([':empresa'=>$empresa,':forma'=>$forma,':prazo'=>$prazo,':usuario'=>$usuario?:null]);
 }
 $pdo->commit();
 header('Location: ../index.php?pagina=conciliacao_financeira&prazos_salvos=1',true,303);exit;
} catch(Throwable $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Falha ao salvar prazos de repasse: '.$e->getMessage());
 http_response_code(500);exit('Não foi possível salvar. Verifique a migração SQL.');
}
