-- Execute após backup, uma única vez.
ALTER TABLE conciliacao_liquidacoes
 ADD COLUMN cancelado_em DATETIME NULL DEFAULT NULL,
 ADD COLUMN cancelado_por INT NULL DEFAULT NULL,
 ADD COLUMN motivo_cancelamento VARCHAR(500) NULL DEFAULT NULL,
 ADD KEY idx_empresa_cancelado (empresa,cancelado_em);
