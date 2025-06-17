<?php

use PDO;

function up(PDO $pdo) {
    // Tabla para tipos de tests
    $sql = "CREATE TABLE IF NOT EXISTS test_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        tipo ENUM('cooper', 'flexibilidad', 'fuerza', 'velocidad') NOT NULL,
        configuracion JSON,
        estado BOOLEAN DEFAULT TRUE,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Insertar tipos de tests iniciales
    $stmt = $pdo->prepare("INSERT INTO test_types (nombre, tipo, configuracion) VALUES (?, ?, ?)");
    
    // Test de Cooper
    $cooper_config = json_encode([
        'descripcion' => 'Prueba de resistencia aeróbica que consiste en recorrer la mayor distancia posible a velocidad constante en 12 minutos.',
        'unidad' => 'metros',
        'tiempo' => '12 minutos',
        'edad_min' => 18,
        'edad_max' => 60,
        'rango' => [
            'M' => [
                ['nivel' => 'Muy Bajo', 'min' => 1500, 'max' => 1699],
                ['nivel' => 'Bajo', 'min' => 1700, 'max' => 1999],
                ['nivel' => 'Regular', 'min' => 2000, 'max' => 2299],
                ['nivel' => 'Bueno', 'min' => 2300, 'max' => 2499],
                ['nivel' => 'Muy Bueno', 'min' => 2500, 'max' => 2699],
                ['nivel' => 'Excelente', 'min' => 2700, 'max' => 3000]
            ],
            'F' => [
                ['nivel' => 'Muy Bajo', 'min' => 1200, 'max' => 1399],
                ['nivel' => 'Bajo', 'min' => 1400, 'max' => 1599],
                ['nivel' => 'Regular', 'min' => 1600, 'max' => 1899],
                ['nivel' => 'Bueno', 'min' => 1900, 'max' => 2099],
                ['nivel' => 'Muy Bueno', 'min' => 2100, 'max' => 2299],
                ['nivel' => 'Excelente', 'min' => 2300, 'max' => 2600]
            ]
        ]
    ]);
    
    $stmt->execute(['Cooper', 'cooper', $cooper_config]);
    
    // Otros tipos de tests
    $stmt->execute([
        'Salto Vertical', 'fuerza', 
        json_encode([
            'descripcion' => 'Prueba de fuerza explosiva de las piernas.',
            'unidad' => 'centímetros',
            'tiempo' => 'instantáneo'
        ])
    ]);
    
    $stmt->execute([
        'Flexibilidad', 'flexibilidad', 
        json_encode([
            'descripcion' => 'Prueba de flexibilidad de la espalda y piernas.',
            'unidad' => 'centímetros',
            'tiempo' => 'instantáneo'
        ])
    ]);
}

function down(PDO $pdo) {
    $pdo->exec("DROP TABLE IF EXISTS test_types");
}
