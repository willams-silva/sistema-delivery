-- Executar no phpMyAdmin após backup do banco de dados.
-- A coluna é compartilhada, mas somente TAPIOCAS SALGADAS da Rainha
-- utiliza os novos grupos. Registros existentes continuam como adicional.
ALTER TABLE adicionais_cat
  ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'adicional';

-- Valores permitidos pela aplicação: adicional, opcional, proteina.
-- Opcional = ingrediente que o cliente pede para retirar, sem custo.
-- Para classificar itens existentes, use o painel administrativo.
