<?php
require_once __DIR__.'/permissoes_guard.php';
require_once __DIR__.'/auditoria_helper.php';
try {
 $pdo->beginTransaction();
 $q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario');
 $q->execute([':usuario'=>$bcUsuario]);
 $removidas=$q->rowCount();
 bcAuditarPermissao($pdo,$bcEmpresa,$bcOperador,$bcUsuario,'remover_todos',null,json_encode(['removidas'=>$removidas]));
 $pdo->commit();
 echo 'Salvo com Sucesso';
} catch (PDOException $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Auditoria de limpeza de permissões: '.$e->getMessage());
 bcPermissaoErro(503,'Não foi possível remover as permissões. Verifique se a tabela de auditoria foi instalada.');
}
