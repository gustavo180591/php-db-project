-- Crear tabla de evaluaciones
CREATE TABLE IF NOT EXISTS evaluaciones (
    id VARCHAR(36) PRIMARY KEY,
    test_id VARCHAR(36) NOT NULL,
    persona_id VARCHAR(36) NOT NULL,
    resultado FLOAT NOT NULL,
    nivel VARCHAR(20),
    aprobado BOOLEAN NOT NULL DEFAULT FALSE,
    fecha TIMESTAMP NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (test_id) REFERENCES tests(id),
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Crear índices
CREATE INDEX idx_evaluaciones_test ON evaluaciones(test_id);
CREATE INDEX idx_evaluaciones_persona ON evaluaciones(persona_id);
CREATE INDEX idx_evaluaciones_fecha ON evaluaciones(fecha);
CREATE INDEX idx_evaluaciones_aprobado ON evaluaciones(aprobado);
