<?php

use PDO;

function up(PDO $pdo) {
    // Tabla para resultados del test de Cooper
    $sql = "CREATE TABLE IF NOT EXISTS cooper_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        persona_id INT NOT NULL,
        test_id INT NOT NULL,
        distancia_metros DECIMAL(10,2) NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        edad INT NOT NULL,
        sexo ENUM('M', 'F') NOT NULL,
        estado BOOLEAN DEFAULT TRUE,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (persona_id) REFERENCES personas(id),
        FOREIGN KEY (test_id) REFERENCES tests(id)
    )";
    $pdo->exec($sql);

    // Tabla para rangos de Cooper por edad y sexo
    $sql = "CREATE TABLE IF NOT EXISTS cooper_ranges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sexo ENUM('M', 'F') NOT NULL,
        edad_min INT NOT NULL,
        edad_max INT NOT NULL,
        nivel ENUM('Muy Bajo', 'Bajo', 'Regular', 'Bueno', 'Muy Bueno', 'Excelente') NOT NULL,
        distancia_min DECIMAL(10,2) NOT NULL,
        distancia_max DECIMAL(10,2) NOT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Insertar rangos iniciales para hombres
    $rangoHombres = [
        ['M', 18, 25, 'Muy Bajo', 1500, 1699],
        ['M', 18, 25, 'Bajo', 1700, 1999],
        ['M', 18, 25, 'Regular', 2000, 2299],
        ['M', 18, 25, 'Bueno', 2300, 2499],
        ['M', 18, 25, 'Muy Bueno', 2500, 2699],
        ['M', 18, 25, 'Excelente', 2700, 3000],
        // ... más rangos para otras edades
    ];

    $stmt = $pdo->prepare("INSERT INTO cooper_ranges (sexo, edad_min, edad_max, nivel, distancia_min, distancia_max) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($rangoHombres as $rango) {
        $stmt->execute($rango);
    }
}

function down(PDO $pdo) {
    $pdo->exec("DROP TABLE IF EXISTS cooper_results");
    $pdo->exec("DROP TABLE IF EXISTS cooper_ranges");
}
