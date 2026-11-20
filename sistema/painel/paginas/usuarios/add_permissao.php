<?php
require_once __DIR__.'/permissoes_guard.php';
$idPermissao=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if (!$idPermissao || $idPermissao<=0) bcPermissaoErro(400,'Acesso inválido');
$q=$pdo->prepare('SELECT 1 FROM acessos WHERE id=:id LIMIT 1');
$q->execute([':id'=>$idPermissao]);
if (!$q->fetchColumn()) bcPermissaoErro(404,'Acesso não encontrado');
$q=$pdo->prepare('SELECT 1 FROM usuarios_permissoes WHERE usuario=:usuario AND permissao=:permissao LIMIT 1');
$q->execute([':usuario'=>$bcUsuario,':permissao'=>$idPermissao]);
if ($q->fetchColumn()) {
 $q=$pdo->prepare('DELETE FROM usuarios_permissoes WHERE usuario=:usuario AND permissao=:permissao');
} else {
 $q=$pdo->prepare('INSERT INTO usuarios_permissoes (usuario,permissao) VALUES (:usuario,:permissao)');
}
$q->execute([':usuario'=>$bcUsuario,':permissao'=>$idPermissao]);
echo 'Salvo com Sucesso';
