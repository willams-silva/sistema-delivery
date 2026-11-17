-- Execute no MySQL da Hostinger antes de ativar a automacao.
CREATE TABLE IF NOT EXISTS automacao_pedidos_config (
 empresa INT NOT NULL PRIMARY KEY,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 aceitar_segundos INT NOT NULL DEFAULT 60,
 preparar_segundos INT NOT NULL DEFAULT 120,
 imprimir_ao_aceitar TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS automacao_pedidos_estado (
 empresa INT NOT NULL,
 venda_id INT NOT NULL,
 aceito_em DATETIME NULL,
 imprimir_pendente TINYINT(1) NOT NULL DEFAULT 0,
 impresso_em DATETIME NULL,
 PRIMARY KEY (empresa,venda_id),
 KEY idx_impressao (empresa,imprimir_pendente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
