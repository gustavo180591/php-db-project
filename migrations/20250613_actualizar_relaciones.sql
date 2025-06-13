-- Añadir campo persona_id a usuarios
ALTER TABLE usuarios ADD COLUMN persona_id INT AFTER estado;
ALTER TABLE usuarios ADD FOREIGN KEY (persona_id) REFERENCES personas(id);

-- Añadir índices
CREATE INDEX idx_usuarios_persona ON usuarios(persona_id);

-- Verificar si las relaciones ya existen
SET FOREIGN_KEY_CHECKS = 0;

-- Añadir relaciones en personas (si no existen)
ALTER TABLE personas ADD CONSTRAINT fk_personas_zona FOREIGN KEY (zona_id) REFERENCES zonas(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE personas ADD CONSTRAINT fk_personas_centro FOREIGN KEY (centro_id) REFERENCES centros(id) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
