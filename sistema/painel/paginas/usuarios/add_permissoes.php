<?php
require_once __DIR__.'/permissoes_guard.php';
try {
 $pdo->beginTransaction();
 $q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario');
 $q->execute([':usuario'=>$bcUsuario]);
 $q=$pdo->prepare('INSERT INTO usuarios_permissoes (usuario,permissao) SELECT :usuario,id FROM acessos');
 $q->execute([':usuario'=>$bcUsuario]);
 $pdo->commit();
 echo 'Salvo com Sucesso';
} catch (PDOException $e) {
 if ($pdo->inTransaction()) $pdo->rollBack();
 error_log('Permissões em lote: '.$e->getMessage());
 bcPermissaoErro(503,'Não foi possível atualizar as permissões');
}
