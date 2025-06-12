<?php

use PDO;

function up(PDO $pdo) {
    // Tabla para resultados de tests
    $sql = "CREATE TABLE IF NOT EXISTS test_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        persona_id INT NOT NULL,
        test_type_id INT NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        edad INT NOT NULL,
        sexo ENUM('M', 'F') NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (persona_id) REFERENCES personas(id),
        FOREIGN KEY (test_type_id) REFERENCES test_types(id)
    )";
    $pdo->exec($sql);
}

function down(PDO $pdo) {
    $pdo->exec("DROP TABLE IF EXISTS test_results");
}
