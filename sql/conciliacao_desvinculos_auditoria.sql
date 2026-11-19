-- Executar após backup, antes de ativar o desvínculo controlado.
CREATE TABLE IF NOT EXISTS conciliacao_vinculos_auditoria (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 vinculo_id BIGINT UNSIGNED NOT NULL,
 receber_id INT NOT NULL,
 liquidacao_id BIGINT UNSIGNED NOT NULL,
 valor_vinculado DECIMAL(12,2) NOT NULL,
 vinculado_por INT DEFAULT NULL,
 vinculado_em TIMESTAMP NULL,
 desvinculado_por INT DEFAULT NULL,
 motivo VARCHAR(500) NOT NULL,
 desvinculado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_vinculos_aud_empresa_data (empresa,desvinculado_em),
 KEY idx_vinculos_aud_credito (empresa,liquidacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
