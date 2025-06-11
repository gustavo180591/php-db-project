-- Create database if not exists
CREATE DATABASE IF NOT EXISTS sistema_captacion;
USE sistema_captacion;

-- Create roles table
CREATE TABLE roles (
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
CREATE TABLE usuarios (
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
CREATE TABLE zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create centros table
CREATE TABLE centros (
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
CREATE TABLE personas (
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
CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create preguntas table
CREATE TABLE preguntas (
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
CREATE TABLE asignaciones_tests (
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
CREATE TABLE respuestas (
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
CREATE INDEX idx_personas_dni ON personas(dni);
CREATE INDEX idx_personas_zona ON personas(zona_id);
CREATE INDEX idx_asignaciones_persona ON asignaciones_tests(persona_id);
CREATE INDEX idx_asignaciones_test ON asignaciones_tests(test_id);
CREATE INDEX idx_respuestas_asignacion ON respuestas(asignacion_id);
