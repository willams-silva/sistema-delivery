-- Executar no banco de produção após backup.
CREATE TABLE IF NOT EXISTS conciliacao_liquidacoes (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 empresa INT NOT NULL,
 forma_pgto INT NOT NULL,
 data_referencia DATE NOT NULL,
 data_credito DATE NOT NULL,
 valor_creditado DECIMAL(12,2) NOT NULL,
 identificador VARCHAR(120) DEFAULT NULL,
 observacao VARCHAR(500) DEFAULT NULL,
 usuario_id INT DEFAULT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (id),
 KEY idx_empresa_referencia (empresa,data_referencia),
 KEY idx_empresa_credito (empresa,data_credito),
 KEY idx_empresa_forma (empresa,forma_pgto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
