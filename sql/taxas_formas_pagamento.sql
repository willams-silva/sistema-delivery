-- Executar no banco do Bora Comanda após backup.
CREATE TABLE IF NOT EXISTS taxas_formas_pagamento (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 empresa INT NOT NULL,
 forma_pgto INT NOT NULL,
 taxa_percentual DECIMAL(7,4) NOT NULL DEFAULT 0,
 atualizado_por INT DEFAULT NULL,
 atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uk_empresa_forma (empresa,forma_pgto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
