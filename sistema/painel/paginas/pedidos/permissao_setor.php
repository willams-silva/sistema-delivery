<?php
// Verificacao de acesso no servidor, independente da visibilidade do menu.
function bcPermiteSetor(PDO $pdo, int $empresa, int $usuario, string $setor): bool {
    $chaves = ['Cozinha'=>'pedidos_mesa','Bar'=>'pedidos_bar','Outro'=>'pedidos_outros'];
    if (!isset($chaves[$setor])) return false;
    $sql = "SELECT 1 FROM usuarios_permissoes up
            INNER JOIN acessos a ON a.id = up.permissao
            WHERE up.usuario = :usuario AND a.empresa = :empresa AND a.chave = :chave LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario'=>$usuario, ':empresa'=>$empresa, ':chave'=>$chaves[$setor]]);
    return (bool)$stmt->fetchColumn();
}
