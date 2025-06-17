-- Crear tabla de personas
CREATE TABLE IF NOT EXISTS personas (
    id VARCHAR(36) PRIMARY KEY,
    dni VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    sexo ENUM('M', 'F') NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crear índices
CREATE INDEX idx_personas_dni ON personas(dni);
CREATE INDEX idx_personas_sexo ON personas(sexo);
CREATE INDEX idx_personas_fecha_nacimiento ON personas(fecha_nacimiento);
