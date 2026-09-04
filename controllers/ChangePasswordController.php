<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /mvc/public/index.php?page=login');
    exit;
}

$userModel = new User($pdo);
$currentUser = $userModel->findByUsername($_SESSION['user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if ($password !== $password2) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userModel->updatePassword($currentUser['id'], $hash, 0);
        $success = 'Contraseña actualizada';
        header('Location: /mvc/public/index.php?page=inicio');
        exit;
    }
}

$titulo = 'Cambiar contraseña';
ob_start();
require __DIR__ . '/../views/change_password.php';
$contenido = ob_get_clean();
require __DIR__ . '/../views/layout.php';
