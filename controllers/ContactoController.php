<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/ContactoModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['subject'] ?? '');
    $mensaje = trim($_POST['message'] ?? '');

    if ($nombre !== '' && $email !== '' && $mensaje !== '') {
        $stmt = $pdo->prepare("INSERT INTO contacts (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $nombre,
            $email,
            $asunto !== '' ? $asunto : 'Consulta',
            $mensaje,
        ]);
        $message = 'Gracias por tu mensaje. Nos pondremos en contacto pronto.';
    } else {
        $message = 'Completa nombre, email y mensaje para enviar la consulta.';
    }
}

$data = ContactoModel::getDatos();
$titulo = 'Contacto';
ob_start();
include __DIR__ . '/../views/contacto.php';
$contenido = ob_get_clean();
include __DIR__ . '/../views/layout.php';
