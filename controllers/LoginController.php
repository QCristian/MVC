<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $userModel = new User($pdo);
    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header('Location: /mvc/public/index.php?page=draft');
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}

$titulo = "Login";
ob_start();
require __DIR__ . '/../views/login.php';
$contenido = ob_get_clean();
require __DIR__ . '/../views/layout.php';