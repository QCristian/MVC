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
$canViewUsers = in_array($currentUser['role'], ['admin', 'superadmin'], true);
$canManageUsers = in_array($currentUser['role'], ['admin', 'superadmin'], true);
$canDeleteUsers = $currentUser['role'] === 'superadmin';
$isSuperAdmin = $currentUser['role'] === 'superadmin';

if (!$currentUser || !$canViewUsers) {
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canManageUsers) {
    $action = $_POST['action'] ?? 'update';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($action === 'delete' && $userId > 0 && $canDeleteUsers) {
        $target = $userModel->findById($userId);

        if ((int)$userId === (int)$currentUser['id']) {
            $error = 'No puedes eliminar tu propio usuario.';
        } elseif ($target && in_array($target['role'], ['admin', 'superadmin'], true) && $currentUser['role'] !== 'superadmin') {
            $error = 'No puedes eliminar a un administrador.';
        } else {
            $userModel->deleteUser($userId);
            $success = 'Usuario eliminado correctamente.';
        }
    } elseif ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';

        if ($currentUser['role'] === 'admin' && in_array($role, ['admin', 'superadmin'], true)) {
            $error = 'No puedes crear usuarios con rol administrativo.';
        } elseif ($username === '' || $password === '') {
            $error = 'El usuario y la contraseña son obligatorios.';
        } elseif (strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($userModel->usernameExists($username)) {
            $error = 'Ya existe un usuario con ese nombre.';
        } elseif ($email !== '' && $userModel->emailExists($email)) {
            $error = 'Ya existe un usuario con ese email.';
        } else {
            $userModel->createUser($username, $email, $role, $password);
            $success = 'Usuario creado correctamente.';
        }
    } elseif ($action === 'update' && $userId > 0) {
        $target = $userModel->findById($userId);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';

        if (!$target) {
            $error = 'No se encontró el usuario.';
        } elseif ((int)$userId === (int)$currentUser['id'] && $role !== $currentUser['role']) {
            $error = 'No puedes cambiar tu propio rol.';
        } elseif ($currentUser['role'] === 'admin' && in_array($target['role'], ['admin', 'superadmin'], true)) {
            $error = 'No puedes modificar a un administrador.';
        } elseif ($currentUser['role'] === 'admin' && in_array($role, ['admin', 'superadmin'], true)) {
            $error = 'No puedes asignar un rol administrativo.';
        } elseif ($username === '') {
            $error = 'El nombre de usuario es obligatorio.';
        } elseif ($email !== '' && $userModel->emailExists($email, $userId)) {
            $error = 'Ya existe otro usuario con ese email.';
        } elseif ($userModel->usernameExists($username, $userId)) {
            $error = 'Ya existe otro usuario con ese nombre.';
        } else {
            $userModel->updateUser($userId, $username, $email, $role, $password !== '' ? $password : null);
            $success = 'Usuario actualizado correctamente.';
        }
    }
}

$users = $userModel->getAll();
if (!$isSuperAdmin) {
    $users = array_values(array_filter($users, function ($u) {
        return $u['role'] !== 'superadmin';
    }));
}

$newUsers = $userModel->getNewUsers();

$titulo = 'Gestión de usuarios';
$bodyClass = 'draft-view';
ob_start();
require __DIR__ . '/../views/draft.php';
$contenido = ob_get_clean();
require __DIR__ . '/../views/layout.php';
