CREATE TABLE IF NOT EXISTS bc_frete_zonas (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 bairro_id INT NOT NULL,
 nome VARCHAR(150) NOT NULL,
 valor DECIMAL(10,2) NOT NULL,
 UNIQUE KEY uq_empresa_bairro_zona (empresa,bairro_id,nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bc_frete_zona_ruas (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 bairro_id INT NOT NULL,
 zona_id BIGINT UNSIGNED NOT NULL,
 rua VARCHAR(150) NOT NULL,
 UNIQUE KEY uq_empresa_bairro_rua (empresa,bairro_id,rua),
 KEY idx_zona (empresa,zona_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
