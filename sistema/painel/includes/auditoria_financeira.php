<?php
/**
 * Registra alterações financeiras sem incluir credenciais, arquivos ou dados pessoais.
 * Invocar dentro da mesma transação do INSERT/UPDATE/DELETE auditado.
 * Requer migração sql/auditoria_financeira.sql.
 */
function registrarAuditoriaFinanceira(PDO $pdo, int $empresa, string $origem, int $registro, string $acao, ?int $usuario, ?array $antes, ?array $depois): void
{
    if ($empresa <= 0 || $registro <= 0 || !in_array($origem, ['pagar','receber'], true) ||
        !in_array($acao, ['CRIAR','ALTERAR','EXCLUIR','BAIXAR','ESTORNAR'], true)) {
        throw new InvalidArgumentException('Dados inválidos para auditoria financeira.');
    }
    $permitidos = ['descricao','valor','vencimento','data_pgto','forma_pgto','pago','subtotal','frequencia'];
    $limpar = static function (?array $dados) use ($permitidos): ?array {
        return $dados === null ? null : array_intersect_key($dados, array_flip($permitidos));
    };
    $st = $pdo->prepare('INSERT INTO auditoria_financeira
        (empresa,tabela_origem,registro_id,acao,usuario_id,dados_anteriores,dados_novos)
        VALUES (:empresa,:origem,:registro,:acao,:usuario,:antes,:depois)');
    $st->execute([
        ':empresa'=>$empresa, ':origem'=>$origem, ':registro'=>$registro,
        ':acao'=>$acao, ':usuario'=>$usuario,
        ':antes'=>$antes === null ? null : json_encode($limpar($antes), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ':depois'=>$depois === null ? null : json_encode($limpar($depois), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
    ]);
}
