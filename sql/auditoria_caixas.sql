-- Auditoria imutável de eventos operacionais do caixa.
CREATE TABLE IF NOT EXISTS auditoria_caixas (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 empresa INT NOT NULL,
 caixa INT NOT NULL,
 usuario INT NOT NULL,
 evento VARCHAR(30) NOT NULL,
 valor_abertura DECIMAL(12,2) NOT NULL DEFAULT 0,
 saldo_esperado DECIMAL(12,2) NOT NULL DEFAULT 0,
 valor_informado DECIMAL(12,2) NOT NULL DEFAULT 0,
 diferenca DECIMAL(12,2) NOT NULL DEFAULT 0,
 detalhes TEXT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_auditoria_caixas_empresa_caixa (empresa,caixa),
 KEY idx_auditoria_caixas_empresa_data (empresa,criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
