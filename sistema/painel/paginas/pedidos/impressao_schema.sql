-- Execute uma vez no phpMyAdmin. Nao altera status nem ativa outras empresas.
CREATE TABLE IF NOT EXISTS automacao_impressoras (
 empresa INT NOT NULL PRIMARY KEY,
 token_hash CHAR(64) NOT NULL,
 nome VARCHAR(120) DEFAULT NULL,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS automacao_impressao_jobs (
 empresa INT NOT NULL,
 venda_id INT NOT NULL,
 estado ENUM('pendente','reservado','impresso') NOT NULL DEFAULT 'pendente',
 reserva_token CHAR(64) DEFAULT NULL,
 reservado_ate DATETIME DEFAULT NULL,
 impresso_em DATETIME DEFAULT NULL,
 tentativas INT NOT NULL DEFAULT 0,
 PRIMARY KEY (empresa,venda_id),
 KEY idx_jobs (empresa,estado,reservado_ate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Gere um token aleatorio de pelo menos 32 bytes para CADA estabelecimento.
-- Exemplo: INSERT INTO automacao_impressoras (empresa,token_hash,nome)
-- VALUES (7,SHA2('SUBSTITUA_POR_TOKEN_ALEATORIO_LONGO',256),'Caixa 1')
-- ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash),nome=VALUES(nome);
