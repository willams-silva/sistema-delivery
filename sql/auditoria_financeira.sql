-- Bora Comanda | Auditoria financeira (etapa de preparação)
-- Executar manualmente no banco correto, após backup. Não modifica contas existentes.
CREATE TABLE IF NOT EXISTS auditoria_financeira (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa INT NOT NULL,
  tabela_origem VARCHAR(20) NOT NULL,
  registro_id INT NOT NULL,
  acao VARCHAR(20) NOT NULL,
  usuario_id INT DEFAULT NULL,
  ocorrido_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dados_anteriores LONGTEXT DEFAULT NULL,
  dados_novos LONGTEXT DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX idx_auditoria_empresa_data (empresa, ocorrido_em),
  INDEX idx_auditoria_registro (empresa, tabela_origem, registro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
