<?php
function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $host = $_ENV['DB_HOST'] ?? 'db';
            $dbname = $_ENV['DB_DATABASE'] ?? 'sistema_captacion';
            $user = $_ENV['DB_USERNAME'] ?? 'captacion_user';
            $password = $_ENV['DB_PASSWORD'] ?? 'captacion_password';
            $port = $_ENV['DB_PORT'] ?? '3306';

            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
