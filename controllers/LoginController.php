<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $userModel = new User($pdo);
    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        if (!empty($user['force_password_change'])) {
            header('Location: /mvc/public/index.php?page=change_password');
            exit;
        }
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