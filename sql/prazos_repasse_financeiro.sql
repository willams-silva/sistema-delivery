-- Executar após backup. Prazo em dias corridos.
CREATE TABLE IF NOT EXISTS prazos_repasse_financeiro (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 empresa INT NOT NULL,
 forma_pgto INT NOT NULL,
 prazo_dias INT UNSIGNED NOT NULL DEFAULT 0,
 atualizado_por INT DEFAULT NULL,
 atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uk_empresa_forma_repasse (empresa,forma_pgto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
