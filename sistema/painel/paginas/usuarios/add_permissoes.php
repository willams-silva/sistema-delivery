<?php
require_once __DIR__.'/permissoes_guard.php';
require_once __DIR__.'/auditoria_helper.php';
try {
 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT COUNT(*) FROM usuarios_permissoes WHERE usuario=:usuario');
 $q->execute([':usuario'=>$bcUsuario]);
 $antes=(int)$q->fetchColumn();
 $q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario');
 $q->execute([':usuario'=>$bcUsuario]);
 $q=$pdo->prepare('INSERT INTO usuarios_permissoes (usuario,permissao) SELECT :usuario,id FROM acessos');
 $q->execute([':usuario'=>$bcUsuario]);
 $depois=$q->rowCount();
 bcAuditarPermissao($pdo,$bcEmpresa,$bcOperador,$bcUsuario,'conceder_todos',null,json_encode(['antes'=>$antes,'depois'=>$depois]));
 $pdo->commit();
 echo 'Salvo com Sucesso';
} catch (PDOException $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Auditoria de permissões em lote: '.$e->getMessage());
 bcPermissaoErro(503,'Não foi possível atualizar as permissões. Verifique se a tabela de auditoria foi instalada.');
}
