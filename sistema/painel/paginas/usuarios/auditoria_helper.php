<?php
function bcAuditarPermissao(PDO $pdo,int $empresa,int $operador,int $alvo,string $acao,?int $permissao=null,?string $detalhes=null): void {
 $q=$pdo->prepare('INSERT INTO auditoria_permissoes (empresa,operador,usuario_alvo,acao,permissao,detalhes) VALUES (:empresa,:operador,:alvo,:acao,:permissao,:detalhes)');
 $q->bindValue(':empresa',$empresa,PDO::PARAM_INT);
 $q->bindValue(':operador',$operador,PDO::PARAM_INT);
 $q->bindValue(':alvo',$alvo,PDO::PARAM_INT);
 $q->bindValue(':acao',$acao);
 $q->bindValue(':permissao',$permissao,$permissao===null?PDO::PARAM_NULL:PDO::PARAM_INT);
 $q->bindValue(':detalhes',$detalhes,$detalhes===null?PDO::PARAM_NULL:PDO::PARAM_STR);
 $q->execute();
}
