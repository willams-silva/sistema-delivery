-- Execute uma única vez no phpMyAdmin antes de usar os novos controles.
-- Mantém os banners existentes ativos e sem agendamento.
ALTER TABLE banner_rotativo
 ADD COLUMN ativo ENUM('Sim','Não') NOT NULL DEFAULT 'Sim',
 ADD COLUMN ordem INT NOT NULL DEFAULT 0,
 ADD COLUMN inicio_em DATETIME NULL DEFAULT NULL,
 ADD COLUMN fim_em DATETIME NULL DEFAULT NULL;
CREATE INDEX idx_banner_empresa_exibicao ON banner_rotativo (empresa,ativo,ordem);
