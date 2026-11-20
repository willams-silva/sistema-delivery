-- Execute no mesmo banco da tabela fechamentos_excepcionais.
-- Horários especiais são independentes dos fechamentos de dia inteiro.
CREATE TABLE IF NOT EXISTS horarios_especiais (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 data_especial DATE NOT NULL,
 hora_inicio TIME NOT NULL,
 hora_fim TIME NOT NULL,
 descricao VARCHAR(150) DEFAULT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uniq_empresa_data_especial (empresa, data_especial),
 KEY idx_empresa_data_especial (empresa, data_especial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Horários que atravessam meia-noite (ex.: 18:00 a 02:00) exigem
-- tratamento adicional no motor de funcionamento; não são aceitos inicialmente.
