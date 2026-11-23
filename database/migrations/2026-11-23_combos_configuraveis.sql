-- Bora Comanda: estrutura inicial de combos configuraveis.
-- Executar manualmente no banco correto, apos backup.
-- Nao altera produtos, carrinho, vendas ou combos legados.
-- Um combo e um produto existente (produtos.id) com combo = 'Sim'.
-- As validacoes de pertencimento a empresa e produtos ativos devem ocorrer no PHP.

CREATE TABLE IF NOT EXISTS combo_configuracoes (
  id INT NOT NULL AUTO_INCREMENT,
  empresa INT NOT NULL,
  produto_combo_id INT NOT NULL,
  tipo ENUM('fixo','personalizavel') NOT NULL DEFAULT 'fixo',
  ativo ENUM('Sim','Não') NOT NULL DEFAULT 'Não',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_combo_empresa_produto (empresa, produto_combo_id),
  KEY idx_combo_empresa_ativo (empresa, ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cada grupo define uma escolha, ex.: Tapiocas (min=2, max=2).
CREATE TABLE IF NOT EXISTS combo_grupos (
  id INT NOT NULL AUTO_INCREMENT,
  empresa INT NOT NULL,
  combo_configuracao_id INT NOT NULL,
  nome VARCHAR(120) NOT NULL,
  minimo SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  maximo SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_grupo_combo (combo_configuracao_id, ordem),
  CONSTRAINT fk_combo_grupo_config FOREIGN KEY (combo_configuracao_id)
    REFERENCES combo_configuracoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Produtos disponiveis em cada grupo e eventual acrescimo unitario.
CREATE TABLE IF NOT EXISTS combo_grupo_produtos (
  id INT NOT NULL AUTO_INCREMENT,
  empresa INT NOT NULL,
  combo_grupo_id INT NOT NULL,
  produto_id INT NOT NULL,
  acrescimo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_combo_grupo_produto (combo_grupo_id, produto_id),
  CONSTRAINT fk_combo_item_grupo FOREIGN KEY (combo_grupo_id)
    REFERENCES combo_grupos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Futuramente: snapshot das escolhas por item do carrinho/pedido para
-- preservar historico e impressao, sem depender de alteracoes no catalogo.
-- NAO habilitar combos configuraveis no cardapio antes da validacao
-- server-side de quantidades, valores, estoque e persistencia das escolhas.
