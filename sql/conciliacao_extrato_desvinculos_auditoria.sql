-- Executar após backup antes de usar desvínculo de extrato.
CREATE TABLE IF NOT EXISTS conciliacao_extrato_desvinculos_auditoria (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 extrato_linha_id BIGINT UNSIGNED NOT NULL,
 liquidacao_id BIGINT UNSIGNED NOT NULL,
 valor_extrato DECIMAL(12,2) NOT NULL,
 usuario_id INT DEFAULT NULL,
 motivo VARCHAR(500) NOT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_extrato_desv_empresa_data (empresa,criado_em),
 KEY idx_extrato_desv_credito (empresa,liquidacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
