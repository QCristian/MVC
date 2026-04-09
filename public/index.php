<?php
session_start();
?>

<?php
// Obtener la página solicitada o 'inicio' por defecto
$page = $_GET['page'] ?? 'inicio';
$page = trim($page, '/'); // Limpia barras al inicio/fin

// Switch para cargar el controlador correspondiente
switch($page){
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
        require '../controllers/DraftController.php';
        break;
    case 'login':
        require '../controllers/LoginController.php';
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