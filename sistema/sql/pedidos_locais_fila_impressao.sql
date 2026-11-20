-- Executar apos backup no banco da Hostinger. Nao ativa impressao automatica.
CREATE TABLE IF NOT EXISTS pedidos_locais_fila_impressao (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 carrinho_id INT NOT NULL,
 setor ENUM('Cozinha','Bar','Outro') NOT NULL,
 estado ENUM('Pendente','Enviado','Impresso','Erro') NOT NULL DEFAULT 'Pendente',
 tentativas INT UNSIGNED NOT NULL DEFAULT 0,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 enviado_em DATETIME NULL,
 impresso_em DATETIME NULL,
 erro VARCHAR(255) NULL,
 UNIQUE KEY uk_empresa_item_setor (empresa,carrinho_id,setor),
 INDEX idx_empresa_setor_estado (empresa,setor,estado,criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
