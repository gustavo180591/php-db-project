<?php
session_start();

function getConnection() {
    $host = 'db';
    $db   = 'sistema_captacion';
    $user = 'user';
    $pass = 'pass';
    $charset = 'utf8mb4';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function checkRole($requiredRole) {
    if (!isset($_SESSION['role_id'])) {
        header('Location: login.php');
        exit;
    }
    
    // Administrador puede acceder a todo
    if ($_SESSION['role_id'] == 1) {
        return true;
    }
    
    // Evaluador puede acceder a secciones de evaluador
    if ($_SESSION['role_id'] == 2 && $requiredRole == 2) {
        return true;
    }
    
    // Atleta puede acceder a secciones de atleta
    if ($_SESSION['role_id'] == 3 && $requiredRole == 3) {
        return true;
    }
    
    // Si no cumple ninguna condición, redirigir
    header('Location: index.php');
    exit;
}
