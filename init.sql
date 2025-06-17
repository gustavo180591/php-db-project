CREATE DATABASE IF NOT EXISTS sistema_captacion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_captacion;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Tabla de zonas
CREATE TABLE zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de centros
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

-- Tabla de personas
CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    sexo ENUM('M', 'F') NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    zona_id INT NOT NULL,
    centro_id INT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (zona_id) REFERENCES zonas(id),
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    persona_id INT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id),
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Tabla de evaluadores
CREATE TABLE evaluadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    experiencia INT NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Tabla de atletas
CREATE TABLE atletas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    deporte VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Tabla de tests
CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de evaluaciones
CREATE TABLE evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    persona_id INT NOT NULL,
    resultado FLOAT NOT NULL,
    nivel VARCHAR(20),
    aprobado BOOLEAN NOT NULL DEFAULT FALSE,
    fecha TIMESTAMP NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id),
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Tabla de preguntas/ítems
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

-- Tabla de asignación de tests
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

-- Tabla de respuestas
CREATE TABLE respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta TEXT,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones_tests(id),
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id)
);

-- Insertar roles iniciales
INSERT INTO roles (nombre, descripcion) VALUES 
('Administrador', 'Tiene acceso completo al sistema'),
('Evaluador', 'Puede asignar y evaluar tests'),
('Atleta', 'Usuario que se somete a evaluaciones');

-- Insertar zonas
INSERT INTO zonas (numero, nombre, descripcion) VALUES
(1, 'Zona Norte', 'Zona norte de la ciudad'),
(2, 'Zona Sur', 'Zona sur de la ciudad'),
(3, 'Zona Este', 'Zona este de la ciudad'),
(4, 'Zona Oeste', 'Zona oeste de la ciudad');

-- Insertar centros
INSERT INTO centros (nombre, direccion, telefono, zona_id) VALUES
('Centro Norte', 'Av. Norte 123', '12345678', 1),
('Centro Sur', 'Av. Sur 456', '23456789', 2),
('Centro Este', 'Av. Este 789', '34567890', 3),
('Centro Oeste', 'Av. Oeste 101', '45678901', 4);

-- Insertar personas
INSERT INTO personas (nombre, apellido, dni, sexo, fecha_nacimiento, direccion, telefono, email, zona_id, centro_id) VALUES
('Juan', 'Pérez', '12345678', 'M', '1990-01-01', 'Av. Norte 123', '1234567890', 'juan@ejemplo.com', 1, 1),
('María', 'Gómez', '87654321', 'F', '1985-05-15', 'Av. Sur 456', '0987654321', 'maria@ejemplo.com', 2, 2),
('Pedro', 'López', '13579246', 'M', '1992-03-21', 'Av. Este 789', '1122334455', 'pedro@ejemplo.com', 3, 3),
('Ana', 'Martínez', '24681357', 'F', '1988-07-30', 'Av. Oeste 101', '5544332211', 'ana@ejemplo.com', 4, 4);

-- Crear usuario administrador
INSERT INTO personas (dni, nombre, apellido, sexo, fecha_nacimiento, email, zona_id, estado) VALUES
('00000000', 'Admin', 'Sistema', 'M', '2000-01-01', 'admin@cepard.com', 1, 1);

SET @persona_admin = LAST_INSERT_ID();

INSERT INTO usuarios (nombre, email, password, rol_id, persona_id, estado) VALUES
('Admin Sistema', 'admin@cepard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, @persona_admin, 1);

-- Insertar personas para evaluadores y atletas
INSERT INTO personas (dni, nombre, apellido, sexo, fecha_nacimiento, email, zona_id, estado) VALUES
('11111111', 'Eva', 'López', 'F', '1980-02-02', 'eva@ejemplo.com', 1, 1),
('22222222', 'Carlos', 'Ruiz', 'M', '1978-03-03', 'carlos@ejemplo.com', 2, 1),
('33333333', 'Luis', 'Martín', 'M', '1995-04-04', 'luis@ejemplo.com', 3, 1),
('44444444', 'Sofía', 'García', 'F', '1998-05-05', 'sofia@ejemplo.com', 4, 1);

-- Insertar usuarios evaluadores y atletas
INSERT INTO usuarios (nombre, email, password, rol_id, persona_id, estado) VALUES
('Evaluador 1', 'eva@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, (SELECT id FROM personas WHERE dni = '11111111'), 1),
('Evaluador 2', 'carlos@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, (SELECT id FROM personas WHERE dni = '22222222'), 1),
('Atleta 1', 'luis@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, (SELECT id FROM personas WHERE dni = '33333333'), 1),
('Atleta 2', 'sofia@ejemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, (SELECT id FROM personas WHERE dni = '44444444'), 1);

-- Insertar tests
INSERT INTO tests (nombre, descripcion) VALUES
('Test de Personalidad', 'Evaluación de rasgos de personalidad'),
('Test de Habilidades', 'Evaluación de habilidades técnicas'),
('Test de Conocimientos', 'Evaluación de conocimientos generales');

-- Insertar preguntas
INSERT INTO preguntas (test_id, numero, pregunta, tipo, opciones, puntos) VALUES
(1, 1, '¿Cómo te describirías?', 'texto', NULL, 10),
(1, 2, '¿Qué te gusta hacer en tu tiempo libre?', 'texto', NULL, 10),
(2, 1, '¿Cuál es tu lenguaje de programación favorito?', 'multiple', 'PHP,Java,Python,C++', 5),
(2, 2, '¿Conoces HTML/CSS?', 'verdadero_falso', NULL, 5),
(3, 1, '¿Cuál es la capital de España?', 'texto', NULL, 5),
(3, 2, '¿Qué año es?', 'texto', NULL, 5);

-- Insertar asignaciones de tests (usando los IDs de los usuarios evaluadores)
INSERT INTO asignaciones_tests (persona_id, test_id, evaluador_id) VALUES
((SELECT id FROM personas WHERE dni = '12345678'), 1, (SELECT id FROM usuarios WHERE email = 'eva@ejemplo.com')),
((SELECT id FROM personas WHERE dni = '12345678'), 2, (SELECT id FROM usuarios WHERE email = 'eva@ejemplo.com')),
((SELECT id FROM personas WHERE dni = '87654321'), 1, (SELECT id FROM usuarios WHERE email = 'carlos@ejemplo.com')),
((SELECT id FROM personas WHERE dni = '87654321'), 3, (SELECT id FROM usuarios WHERE email = 'carlos@ejemplo.com')),
((SELECT id FROM personas WHERE dni = '13579246'), 2, (SELECT id FROM usuarios WHERE email = 'eva@ejemplo.com')),
((SELECT id FROM personas WHERE dni = '24681357'), 3, (SELECT id FROM usuarios WHERE email = 'carlos@ejemplo.com'));

-- Índices para optimizar consultas
CREATE INDEX idx_personas_dni ON personas(dni);
CREATE INDEX idx_personas_zona ON personas(zona_id);
CREATE INDEX idx_personas_sexo ON personas(sexo);
CREATE INDEX idx_personas_fecha_nacimiento ON personas(fecha_nacimiento);
CREATE INDEX idx_asignaciones_persona ON asignaciones_tests(persona_id);
CREATE INDEX idx_asignaciones_test ON asignaciones_tests(test_id);
CREATE INDEX idx_respuestas_asignacion ON respuestas(asignacion_id);
CREATE INDEX idx_evaluaciones_test ON evaluaciones(test_id);
CREATE INDEX idx_evaluaciones_persona ON evaluaciones(persona_id);
CREATE INDEX idx_usuarios_persona ON usuarios(persona_id);
CREATE INDEX idx_evaluaciones_fecha ON evaluaciones(fecha);
CREATE INDEX idx_evaluaciones_aprobado ON evaluaciones(aprobado);
