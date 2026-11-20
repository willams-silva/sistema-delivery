<?php
// Proteção comum aos endpoints de permissões dos usuários do painel.
@session_start();
require_once '../../../conexao.php';
function bcPermissaoErro(int $codigo,string $mensagem): void {http_response_code($codigo);exit($mensagem);}
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') bcPermissaoErro(405,'Método não permitido');
$bcEmpresa=(int)($_SESSION['empresa']??0);
$bcOperador=(int)($_SESSION['id']??0);
if ($bcEmpresa<=0 || $bcOperador<=0) bcPermissaoErro(403,'Sessão inválida');
$q=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$bcOperador,':empresa'=>$bcEmpresa]);
if ($q->fetchColumn()!=='Administrador') bcPermissaoErro(403,'Somente administradores podem gerenciar permissões');
$q=$pdo->prepare('SELECT alterar_acessos FROM config WHERE empresa=:empresa LIMIT 1');
$q->execute([':empresa'=>$bcEmpresa]);
if ($q->fetchColumn()!=='Sim') bcPermissaoErro(403,'Alteração de acessos desabilitada para esta empresa');
$bcUsuario=filter_var($_POST['id_usuario']??$_POST['usuario']??$_POST['id']??null,FILTER_VALIDATE_INT);
if (!$bcUsuario || $bcUsuario<=0) bcPermissaoErro(400,'Usuário inválido');
$q=$pdo->prepare('SELECT id FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
$q->execute([':id'=>$bcUsuario,':empresa'=>$bcEmpresa]);
if (!$q->fetchColumn()) bcPermissaoErro(404,'Usuário não pertence a esta empresa');

$bcAlvoNivelQuery=$pdo->prepare('SELECT nivel FROM usuarios WHERE id=:id AND empresa=:empresa LIMIT 1');
$bcAlvoNivelQuery->execute([':id'=>$bcUsuario,':empresa'=>$bcEmpresa]);
$bcAlvoNivel=(string)$bcAlvoNivelQuery->fetchColumn();
// Alterar os acessos do próprio administrador pode provocar perda de acesso acidental.
if ($bcUsuario===$bcOperador && basename((string)($_SERVER['SCRIPT_NAME']??''))==='limpar_permissoes.php') {
 bcPermissaoErro(409,'Não é permitido remover todas as permissões do próprio administrador');
}
