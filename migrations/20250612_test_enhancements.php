<?php

use PDO;

function up(PDO $pdo) {
    // Tabla para categorías de tests
    $sql = "CREATE TABLE IF NOT EXISTS test_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        icono VARCHAR(50),
        estado BOOLEAN DEFAULT TRUE,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Insertar categorías iniciales
    $stmt = $pdo->prepare("INSERT INTO test_categories (nombre, icono) VALUES (?, ?)");
    $stmt->execute(['Resistencia', 'fas fa-running']);
    $stmt->execute(['Fuerza', 'fas fa-dumbbell']);
    $stmt->execute(['Flexibilidad', 'fas fa-balance-scale']);
    $stmt->execute(['Velocidad', 'fas fa-tachometer-alt']);

    // Tabla de relación entre tests y categorías
    $sql = "CREATE TABLE IF NOT EXISTS test_type_categories (
        test_type_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (test_type_id, category_id),
        FOREIGN KEY (test_type_id) REFERENCES test_types(id),
        FOREIGN KEY (category_id) REFERENCES test_categories(id)
    )";
    $pdo->exec($sql);

    // Agregar campos a test_types para múltiples unidades y permisos
    $sql = "ALTER TABLE test_types 
            ADD COLUMN unidad_principal VARCHAR(50) DEFAULT NULL,
            ADD COLUMN unidad_secundaria VARCHAR(50) DEFAULT NULL,
            ADD COLUMN permisos JSON DEFAULT NULL";
    $pdo->exec($sql);

    // Tabla para gráficos de evolución
    $sql = "CREATE TABLE IF NOT EXISTS test_evolution (
        id INT AUTO_INCREMENT PRIMARY KEY,
        persona_id INT NOT NULL,
        test_type_id INT NOT NULL,
        fecha_inicio DATE,
        fecha_fin DATE,
        datos JSON,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (persona_id) REFERENCES personas(id),
        FOREIGN KEY (test_type_id) REFERENCES test_types(id)
    )";
    $pdo->exec($sql);

    // Tabla para permisos personalizados
    $sql = "CREATE TABLE IF NOT EXISTS test_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rol_id INT NOT NULL,
        test_type_id INT NOT NULL,
        permiso ENUM('ver', 'evaluar', 'administrar') NOT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_permission (rol_id, test_type_id, permiso),
        FOREIGN KEY (rol_id) REFERENCES roles(id),
        FOREIGN KEY (test_type_id) REFERENCES test_types(id)
    )";
    $pdo->exec($sql);

    // Actualizar roles existentes
    $stmt = $pdo->prepare("INSERT INTO test_permissions (rol_id, test_type_id, permiso) 
                          SELECT r.id, tt.id, 'ver' 
                          FROM roles r, test_types tt 
                          WHERE r.nombre IN ('Administrador', 'Evaluador')");
    $stmt->execute();
}

function down(PDO $pdo) {
    $pdo->exec("DROP TABLE IF EXISTS test_permissions");
    $pdo->exec("DROP TABLE IF EXISTS test_evolution");
    $pdo->exec("DROP TABLE IF EXISTS test_type_categories");
    $pdo->exec("DROP TABLE IF EXISTS test_categories");
    
    // Eliminar campos agregados
    $pdo->exec("ALTER TABLE test_types DROP COLUMN unidad_principal");
    $pdo->exec("ALTER TABLE test_types DROP COLUMN unidad_secundaria");
    $pdo->exec("ALTER TABLE test_types DROP COLUMN permisos");
}
