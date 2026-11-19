-- Antes de executar, faça backup do banco.
-- Diagnóstico: esta consulta deve retornar ZERO linhas.
SELECT empresa, liquidacao_id, COUNT(*) AS quantidade
FROM conciliacao_extrato_linhas
WHERE liquidacao_id IS NOT NULL
GROUP BY empresa, liquidacao_id
HAVING COUNT(*) > 1;

-- Execute a instrução abaixo SOMENTE se a consulta acima não retornar linhas.
-- MySQL permite múltiplos NULL em índice UNIQUE, mantendo extratos não vinculados.
ALTER TABLE conciliacao_extrato_linhas
ADD UNIQUE KEY uq_conc_extrato_credito_empresa (empresa, liquidacao_id);
