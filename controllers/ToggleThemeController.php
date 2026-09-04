<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Toggle or set theme preference in session
$data = json_decode(file_get_contents('php://input'), true);
$mode = $data['mode'] ?? $_POST['mode'] ?? null;

if ($mode === 'dark') {
    $_SESSION['darkMode'] = 1;
} elseif ($mode === 'light') {
    $_SESSION['darkMode'] = 0;
} else {
    // toggle
    $_SESSION['darkMode'] = empty($_SESSION['darkMode']) ? 1 : 0;
}

header('Content-Type: application/json');
echo json_encode(['dark' => (bool)($_SESSION['darkMode'] ?? false)]);
exit;
