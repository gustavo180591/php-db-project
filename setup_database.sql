-- Create database
CREATE DATABASE IF NOT EXISTS sistema_captacion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_captacion;

-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Insert initial roles
INSERT INTO roles (nombre, descripcion) VALUES 
('Administrador', 'Tiene acceso completo al sistema'),
('Evaluador', 'Puede asignar y evaluar tests'),
('Usuario', 'Acceso básico al sistema');

-- Create usuarios table
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Create zonas table
CREATE TABLE IF NOT EXISTS zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create centros table
CREATE TABLE IF NOT EXISTS centros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20),
    zona_id INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zona_id) REFERENCES zonas(id)
);

-- Create personas table
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    fecha_nacimiento DATE,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    zona_id INT NOT NULL,
    centro_id INT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zona_id) REFERENCES zonas(id),
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Create tests table
CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create preguntas table
CREATE TABLE IF NOT EXISTS preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    numero INT NOT NULL,
    pregunta TEXT NOT NULL,
    tipo ENUM('texto', 'multiple', 'verdadero_falso') NOT NULL,
    opciones TEXT,
    puntos INT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id)
);

-- Create asignaciones_tests table
CREATE TABLE IF NOT EXISTS asignaciones_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    test_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'en_progreso', 'completado') DEFAULT 'pendiente',
    evaluador_id INT,
    FOREIGN KEY (persona_id) REFERENCES personas(id),
    FOREIGN KEY (test_id) REFERENCES tests(id),
    FOREIGN KEY (evaluador_id) REFERENCES usuarios(id)
);

-- Create respuestas table
CREATE TABLE IF NOT EXISTS respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta TEXT,
    puntos_obtenidos INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones_tests(id),
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_personas_dni ON personas(dni);
CREATE INDEX IF NOT EXISTS idx_personas_zona ON personas(zona_id);
CREATE INDEX IF NOT EXISTS idx_asignaciones_persona ON asignaciones_tests(persona_id);
CREATE INDEX IF NOT EXISTS idx_asignaciones_test ON asignaciones_tests(test_id);
CREATE INDEX IF NOT EXISTS idx_respuestas_asignacion ON respuestas(asignacion_id);

-- Insert test data
INSERT INTO zonas (numero, nombre, descripcion) VALUES
(1, 'Zona Norte', 'Zona norte de la ciudad'),
(2, 'Zona Sur', 'Zona sur de la ciudad'),
(3, 'Zona Este', 'Zona este de la ciudad'),
(4, 'Zona Oeste', 'Zona oeste de la ciudad');

INSERT INTO centros (nombre, direccion, telefono, zona_id) VALUES
('Centro Norte', 'Av. Norte 123', '12345678', 1),
('Centro Sur', 'Av. Sur 456', '23456789', 2),
('Centro Este', 'Av. Este 789', '34567890', 3),
('Centro Oeste', 'Av. Oeste 101', '45678901', 4);
