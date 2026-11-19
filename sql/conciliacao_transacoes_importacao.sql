-- Migração: executar uma única vez após backup.
CREATE TABLE IF NOT EXISTS conciliacao_extrato_importacoes (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 nome_arquivo VARCHAR(255) NOT NULL,
 hash_arquivo CHAR(64) NOT NULL,
 formato VARCHAR(8) NOT NULL,
 usuario_id INT DEFAULT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uk_conc_arquivo (empresa,hash_arquivo),
 KEY idx_conc_import_empresa (empresa,criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS conciliacao_extrato_linhas (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 importacao_id BIGINT UNSIGNED NOT NULL,
 data_movimento DATE NOT NULL,
 valor DECIMAL(12,2) NOT NULL,
 descricao VARCHAR(500) DEFAULT NULL,
 identificador VARCHAR(120) DEFAULT NULL,
 hash_linha CHAR(64) NOT NULL,
 liquidacao_id BIGINT UNSIGNED DEFAULT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uk_conc_linha (empresa,hash_linha),
 KEY idx_conc_extrato_empresa_data (empresa,data_movimento),
 KEY idx_conc_extrato_liquidacao (empresa,liquidacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS conciliacao_vinculos_recebimentos (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 receber_id INT NOT NULL,
 liquidacao_id BIGINT UNSIGNED NOT NULL,
 valor_vinculado DECIMAL(12,2) NOT NULL,
 usuario_id INT DEFAULT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uk_conc_recebimento (empresa,receber_id),
 KEY idx_conc_liquidacao (empresa,liquidacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
