-- Executar somente após backup e antes de ativar o código de tapiocas mistas.
-- 0 = tapioca tradicional; 2 ou 3 = proteínas incluídas na tapioca mista.
ALTER TABLE produtos
  ADD COLUMN proteinas_incluidas TINYINT UNSIGNED NOT NULL DEFAULT 0;
