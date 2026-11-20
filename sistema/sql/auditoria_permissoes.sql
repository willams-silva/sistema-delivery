-- Execute uma vez no banco MySQL do Bora Comanda antes de usar a auditoria.
CREATE TABLE IF NOT EXISTS auditoria_permissoes (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 empresa INT NOT NULL,
 operador INT NOT NULL,
 usuario_alvo INT NOT NULL,
 acao VARCHAR(32) NOT NULL,
 permissao INT NULL,
 detalhes TEXT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (id),
 INDEX idx_auditoria_empresa_data (empresa, criado_em),
 INDEX idx_auditoria_usuario_data (usuario_alvo, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
