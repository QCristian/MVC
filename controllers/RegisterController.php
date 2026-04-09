<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/User.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if($password !== $password2) {
        $error = "Las contraseñas no coinciden";
    } else {
        $userModel = new User($pdo);
        if($userModel->findByUsername($username)) {
            $error = "Usuario ya existe";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $success = "Usuario registrado, ahora puedes iniciar sesión";
        }
    }
}

$titulo = "Registro";
ob_start();
require __DIR__ . '/../views/register.php';
$contenido = ob_get_clean();
require __DIR__ . '/../views/layout.php';