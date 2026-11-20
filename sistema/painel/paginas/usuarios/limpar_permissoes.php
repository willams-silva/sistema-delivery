<?php
require_once __DIR__.'/permissoes_guard.php';
$q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario');
$q->execute([':usuario'=>$bcUsuario]);
echo 'Salvo com Sucesso';
