<?php
session_start();

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
    
    if ($_SESSION['role_id'] != $requiredRole) {
        header('Location: index.php');
        exit;
    }
}
