<?php
// Acesso por setor: administrador da propria empresa ou permissao explicita.
function bcPermiteSetor(PDO $pdo, int $empresa, int $usuario, string $setor): bool {
    $chaves = ['Cozinha'=>'pedidos_mesa','Bar'=>'pedidos_bar','Outro'=>'pedidos_outros'];
    if ($empresa < 1 || $usuario < 1 || !isset($chaves[$setor])) return false;
    // Nao confiar somente no nivel guardado na sessao: validar no banco e na empresa.
    $admin = $pdo->prepare("SELECT 1 FROM usuarios WHERE id=:usuario AND empresa=:empresa AND nivel='Administrador' LIMIT 1");
    $admin->execute([':usuario'=>$usuario, ':empresa'=>$empresa]);
    if ($admin->fetchColumn()) return true;
    $sql = "SELECT 1 FROM usuarios_permissoes up
            INNER JOIN acessos a ON a.id=up.permissao
            INNER JOIN usuarios u ON u.id=up.usuario AND u.empresa=a.empresa
            WHERE up.usuario=:usuario AND a.empresa=:empresa AND a.chave=:chave LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario'=>$usuario, ':empresa'=>$empresa, ':chave'=>$chaves[$setor]]);
    return (bool)$stmt->fetchColumn();
}
