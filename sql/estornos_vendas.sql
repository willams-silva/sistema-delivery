-- Bora Comanda: base auditável para estornos de vendas.
-- Executar somente após backup no banco correto. Não altera lançamentos existentes.
-- Este script NÃO habilita o estorno automático; somente cria o histórico.
CREATE TABLE IF NOT EXISTS estornos_vendas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa INT NOT NULL,
  venda_id INT NOT NULL,
  caixa_estorno_id INT NOT NULL,
  usuario_id INT NOT NULL,
  motivo VARCHAR(500) NOT NULL,
  valor_venda DECIMAL(12,2) NOT NULL,
  valor_recebido DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  valor_pendente DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(20) NOT NULL DEFAULT 'Registrado',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_estorno_empresa_venda (empresa,venda_id),
  KEY idx_estorno_empresa_caixa (empresa,caixa_estorno_id),
  KEY idx_estorno_empresa_data (empresa,criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estornos_vendas_recebimentos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  estorno_id BIGINT UNSIGNED NOT NULL,
  empresa INT NOT NULL,
  receber_id INT NOT NULL,
  caixa_original_id INT DEFAULT NULL,
  forma_pgto_id INT DEFAULT NULL,
  valor_recebido DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  valor_devolvido DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  dados_originais LONGTEXT DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_estorno_recebimento (estorno_id,receber_id),
  KEY idx_estorno_receber_empresa (empresa,receber_id),
  CONSTRAINT fk_estorno_recebimentos_cabecalho
    FOREIGN KEY (estorno_id) REFERENCES estornos_vendas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
