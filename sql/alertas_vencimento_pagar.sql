-- Executar no banco da Hostinger antes de habilitar a personalizacao.
CREATE TABLE IF NOT EXISTS alertas_vencimento_pagar (
 empresa INT NOT NULL PRIMARY KEY,
 dias_antecedencia TINYINT UNSIGNED NOT NULL DEFAULT 7,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
