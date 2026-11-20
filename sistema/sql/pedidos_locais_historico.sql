-- Executar uma vez no banco MySQL da empresa, apos backup.
CREATE TABLE IF NOT EXISTS pedidos_locais_historico (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 carrinho_id INT NOT NULL,
 usuario_id INT NOT NULL,
 setor VARCHAR(30) NOT NULL,
 status_anterior VARCHAR(30) NOT NULL,
 status_novo VARCHAR(30) NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_empresa_item (empresa, carrinho_id),
 INDEX idx_empresa_data (empresa, criado_em),
 INDEX idx_empresa_setor (empresa, setor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
