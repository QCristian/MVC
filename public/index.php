<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionTimeoutSeconds = 1200;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeoutSeconds) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash_error'] = 'La sesión expiró por inactividad.';
    header('Location: /mvc/public/index.php?page=login');
    exit;
}

$_SESSION['last_activity'] = time();
?>

<?php
// Obtener la página solicitada o 'inicio' por defecto
$page = $_GET['page'] ?? 'inicio';
$page = trim($page, '/'); // Limpia barras al inicio/fin

// Switch para cargar el controlador correspondiente
switch($page){
    case 'inicio':
    case 'home':
        require '../controllers/HomeController.php';
        break;
    case 'sobre':
        require '../controllers/SobreController.php';
        break;
    case 'servicios':
        require '../controllers/ServiciosController.php';
        break;
    case 'contacto':
        require '../controllers/ContactoController.php';
        break;
    case 'draft':
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            require '../controllers/DraftController.php';
            exit;
        }
        require '../controllers/DraftController.php';
        break;
    case 'login':
        require '../controllers/LoginController.php';
        break;
    case 'change_password':
        require '../controllers/ChangePasswordController.php';
        break;
    case 'users':
        require '../controllers/UserManagementController.php';
        break;
    case 'toggle_theme':
        require '../controllers/ToggleThemeController.php';
        break;
    case 'logout':
        require '../controllers/LogoutController.php';
        break;
    case 'register':
        require '../controllers/RegisterController.php';
        break;
    default:
        require '../controllers/HomeController.php';
        break;
}