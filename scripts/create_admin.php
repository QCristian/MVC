<?php
// Script para crear o actualizar el usuario administrador.
// Uso CLI: php scripts/create_admin.php username email password
// Uso web: POST a scripts/create_admin.php con fields username,email,password

require __DIR__ . '/../config/database.php';

function createOrUpdateAdmin($pdo, $username, $email, $password) {
    // comprobar si existe por username o email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'superadmin', email = ?, force_password_change = 1 WHERE id = ?");
        $stmt->execute([$hash, $email, $user['id']]);
        return "Usuario existente actualizado a superadmin: " . $user['username'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, force_password_change) VALUES (?, ?, ?, 'superadmin', 1)");
        $stmt->execute([$username, $email, $hash]);
        return "Usuario superadmin creado: " . $username;
    }
}

if (php_sapi_name() === 'cli') {
    $argv = $_SERVER['argv'];
    $autoCreated = false;
    if (count($argv) >= 4) {
        $username = $argv[1];
        $email = $argv[2];
        $password = $argv[3];
    } else {
        // Crear admin por defecto con contraseña aleatoria
        $username = 'superadmin';
        $email = 'admin@example.com';
        try {
            $password = bin2hex(random_bytes(6)); // 12 caracteres hex
        } catch (Exception $e) {
            $password = 'AdminChangeMe123!';
        }
        $autoCreated = true;
    }

    try {
        echo createOrUpdateAdmin($pdo, $username, $email, $password) . "\n";
        if ($autoCreated) {
            $credFile = __DIR__ . '/admin_credentials.txt';
            $text = "username:$username\nemail:$email\npassword:$password\n";
            file_put_contents($credFile, $text);
            echo "Credenciales guardadas en: " . $credFile . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
    exit(0);
} else {
    // Denegar acceso web por seguridad
    http_response_code(403);
    echo "Acceso no permitido. Ejecuta el script desde CLI.";
}
