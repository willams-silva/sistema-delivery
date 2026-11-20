-- Execute uma única vez no banco MySQL do Bora Comanda.
-- Datas específicas por empresa. Não modifica a tabela de dias recorrentes.
CREATE TABLE IF NOT EXISTS fechamentos_excepcionais (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 data_fechamento DATE NOT NULL,
 motivo VARCHAR(150) DEFAULT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_empresa_data (empresa, data_fechamento),
 KEY idx_empresa_data (empresa, data_fechamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
