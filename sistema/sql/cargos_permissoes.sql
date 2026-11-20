-- Executar uma vez no MySQL da Hostinger.
-- Os cargos são separados por empresa; os acessos são definidos globalmente.
CREATE TABLE IF NOT EXISTS cargos_permissoes (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 empresa INT NOT NULL,
 cargo_id INT NOT NULL,
 permissao_id INT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id),
 UNIQUE KEY uk_cargo_permissao (empresa,cargo_id,permissao_id),
 KEY idx_cargo_empresa (empresa,cargo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
