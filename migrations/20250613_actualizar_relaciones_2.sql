-- Añadir campo persona_id a usuarios
ALTER TABLE usuarios ADD COLUMN persona_id INT AFTER estado;
ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_persona FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Añadir índice para usuarios
CREATE INDEX idx_usuarios_persona ON usuarios(persona_id);

-- Añadir relaciones en personas
ALTER TABLE personas ADD CONSTRAINT fk_personas_zona FOREIGN KEY (zona_id) REFERENCES zonas(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE personas ADD CONSTRAINT fk_personas_centro FOREIGN KEY (centro_id) REFERENCES centros(id) ON DELETE SET NULL ON UPDATE CASCADE;
