<?php
function getConnection() {
    $host = 'localhost';
    $dbname = 'sistema_captacion';
    $user = 'captacion_user';
    $password = 'captacion_password';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage() . "\n";
        exit(1);
    }
}

function executeMigration($pdo, $migrationFile) {
    try {
        $sql = file_get_contents($migrationFile);
        $pdo->exec($sql);
        echo "Migration $migrationFile executed successfully\n";
    } catch (PDOException $e) {
        echo "Error executing migration $migrationFile: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Get list of migration files
$migrationDir = __DIR__ . '/migrations';
$migrationFiles = glob($migrationDir . '/*.sql');

// Sort migrations numerically
usort($migrationFiles, function($a, $b) {
    return strnatcmp(basename($a), basename($b));
});

// Connect to database
$pdo = getConnection();

// Execute each migration
foreach ($migrationFiles as $migrationFile) {
    executeMigration($pdo, $migrationFile);
}

// Insert test data if needed
try {
    // Insert admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO usuarios (nombre, email, password, rol_id) VALUES 
        ('Admin', 'admin@ejemplo.com', '$adminPassword', 1)");
    
    // Insert test zones
    $pdo->exec("INSERT INTO zonas (numero, nombre, descripcion) VALUES
        (1, 'Zona Norte', 'Zona norte de la ciudad'),
        (2, 'Zona Sur', 'Zona sur de la ciudad'),
        (3, 'Zona Este', 'Zona este de la ciudad'),
        (4, 'Zona Oeste', 'Zona oeste de la ciudad')");
    
    echo "Test data inserted successfully\n";
} catch (PDOException $e) {
    echo "Error inserting test data: " . $e->getMessage() . "\n";
}

echo "Database migrations completed successfully\n";
