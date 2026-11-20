<?php
require_once __DIR__.'/permissoes_guard.php';
require_once __DIR__.'/auditoria_helper.php';
$idPermissao=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if (!$idPermissao || $idPermissao<=0) bcPermissaoErro(400,'Acesso inválido');
try {
 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT id FROM acessos WHERE id=:id LIMIT 1');
 $q->execute([':id'=>$idPermissao]);
 if (!$q->fetchColumn()) { $pdo->rollBack();bcPermissaoErro(404,'Acesso não encontrado'); }
 $q=$pdo->prepare('SELECT 1 FROM usuarios_permissoes WHERE usuario=:usuario AND permissao=:permissao LIMIT 1 FOR UPDATE');
 $q->execute([':usuario'=>$bcUsuario,':permissao'=>$idPermissao]);
 $existe=(bool)$q->fetchColumn();
 if ($existe) {
  $q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario AND permissao=:permissao');
 } else {
  $q=$pdo->prepare('INSERT INTO usuarios_permissoes (usuario,permissao) VALUES (:usuario,:permissao)');
 }
 $q->execute([':usuario'=>$bcUsuario,':permissao'=>$idPermissao]);
 bcAuditarPermissao($pdo,$bcEmpresa,$bcOperador,$bcUsuario,$existe?'remover':'conceder',$idPermissao);
 $pdo->commit();
 echo 'Salvo com Sucesso';
} catch (PDOException $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Auditoria de permissão individual: '.$e->getMessage());
 bcPermissaoErro(503,'Não foi possível salvar a permissão. Verifique se a tabela de auditoria foi instalada.');
}
