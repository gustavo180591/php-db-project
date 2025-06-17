-- Añadir campo persona_id a usuarios
ALTER TABLE usuarios ADD COLUMN persona_id INT AFTER estado;
ALTER TABLE usuarios ADD FOREIGN KEY (persona_id) REFERENCES personas(id);

-- Añadir índices
CREATE INDEX idx_usuarios_persona ON usuarios(persona_id);

-- Actualizar relaciones en personas
ALTER TABLE personas ADD FOREIGN KEY (zona_id) REFERENCES zonas(id);
ALTER TABLE personas ADD FOREIGN KEY (centro_id) REFERENCES centros(id);
