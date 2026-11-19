-- Executar após backup para ativar auditoria de vínculos entre extrato e crédito.
CREATE TABLE IF NOT EXISTS conciliacao_extrato_vinculos_auditoria (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 extrato_linha_id BIGINT UNSIGNED NOT NULL,
 liquidacao_id BIGINT UNSIGNED NOT NULL,
 usuario_id INT DEFAULT NULL,
 valor_extrato DECIMAL(12,2) NOT NULL,
 valor_credito DECIMAL(12,2) NOT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_extrato_aud_empresa (empresa,criado_em),
 KEY idx_extrato_aud_linha (empresa,extrato_linha_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
