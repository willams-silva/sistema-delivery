-- Executar uma única vez no banco da empresa, após backup.
-- Relacionamento explícito entre venda e parcelas, sem depender de data/valor.
-- Verifique antes se a coluna venda_id já existe.
ALTER TABLE receber ADD COLUMN venda_id INT NULL DEFAULT NULL;
CREATE INDEX idx_receber_empresa_venda ON receber (empresa, venda_id);
-- Não atualizar registros antigos por aproximação: somente conciliação manual auditada.
-- A migração não habilita estornos automaticamente.
