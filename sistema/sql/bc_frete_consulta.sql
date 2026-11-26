CREATE TABLE IF NOT EXISTS bc_frete_consulta_config (
 empresa INT NOT NULL PRIMARY KEY,
 ativo TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS bc_frete_consultas (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 token_hash CHAR(64) NOT NULL,
 endereco TEXT NOT NULL,
 status ENUM('pendente','cotado','aceito','recusado','expirado') NOT NULL DEFAULT 'pendente',
 valor DECIMAL(10,2) DEFAULT NULL,
 operador INT DEFAULT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 expira_em DATETIME NOT NULL,
 UNIQUE KEY uq_token (token_hash),
 KEY idx_empresa_status (empresa,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
