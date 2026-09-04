<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../models/DraftModel.php';
require __DIR__ . '/../models/User.php';
require __DIR__ . '/../models/SectionModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /mvc/public/index.php?page=login');
    exit;
}

$userModel = new User($pdo);
$sectionModel = new SectionModel($pdo);
$currentUser = $userModel->findByUsername($_SESSION['user']);
if (!$currentUser) {
    header('Location: /mvc/public/index.php?page=login');
    exit;
}

$canViewUsers = in_array($currentUser['role'], ['admin', 'superadmin'], true);
$canManageUsers = in_array($currentUser['role'], ['admin', 'superadmin'], true);
$canDeleteUsers = $currentUser['role'] === 'superadmin';
$isSuperAdmin = $currentUser['role'] === 'superadmin';
$canManageSidebarSections = in_array($currentUser['role'], ['admin', 'superadmin'], true);
$canManageContentViews = in_array($currentUser['role'], ['moderator', 'admin', 'superadmin'], true);
$canModerateSections = in_array($currentUser['role'], ['moderator', 'admin', 'superadmin'], true);
$canReviewSectionChanges = in_array($currentUser['role'], ['admin', 'superadmin'], true);
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Note: hiding/visibility of views removed per new requirements

    if ($action === 'save_product' && $canManageContentViews) {
        $viewId = (int)($_POST['view_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $name = trim((string)($_POST['product_name'] ?? ''));
        $value = (float)($_POST['product_value'] ?? 0);
        $weight = (float)($_POST['product_weight'] ?? 0);
        $photoUrl = trim((string)($_POST['product_photo'] ?? ''));
        $photoPath = '';

        if (isset($_FILES['product_photo_file']) && $_FILES['product_photo_file']['error'] === UPLOAD_ERR_OK && !empty($_FILES['product_photo_file']['name'])) {
            $uploadDir = __DIR__ . '/../public/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['product_photo_file']['name'], PATHINFO_EXTENSION));
            $safeName = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target = $uploadDir . $safeName;
            if (move_uploaded_file($_FILES['product_photo_file']['tmp_name'], $target)) {
                $photoPath = '/mvc/public/uploads/products/' . $safeName;
            }
        }

        if ($viewId > 0 && $name !== '') {
            $sectionModel->saveProduct($viewId, $name, $value, $weight, $photoPath, $photoUrl, (int)$currentUser['id'], $productId > 0 ? $productId : null);
            $_SESSION['flash_success'] = $productId > 0 ? 'Producto actualizado.' : 'Producto añadido correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Debes indicar al menos el nombre del producto.';
        }
    }

    if ($action === 'delete_product' && $canManageContentViews) {
        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId > 0) {
            $sectionModel->deleteProduct($productId);
            $_SESSION['flash_success'] = 'Producto eliminado correctamente.';
        }
    }

    if ($action === 'update_dashboard_text' && in_array($currentUser['role'], ['admin', 'superadmin'], true)) {
        $targetRole = strtolower(trim($_POST['dashboard_role'] ?? $currentUser['role']));
        $validRoles = ['global', 'user', 'moderator', 'admin', 'superadmin'];
        if (!in_array($targetRole, $validRoles, true)) {
            $targetRole = $currentUser['role'];
        }

        $title = trim($_POST['draft_title'] ?? '');
        $subtitle = trim($_POST['draft_subtitle'] ?? '');
        $content = trim($_POST['draft_content'] ?? '');
        if ($title === '' || $content === '') {
            $_SESSION['flash_error'] = 'El título y el contenido del draft son obligatorios.';
            header('Location: /mvc/public/index.php?page=draft');
            exit;
        }

        if ($targetRole === 'global') {
            $sectionModel->saveGlobalHeader('title', $title);
            $sectionModel->saveGlobalHeader('subtitle', $subtitle);
            $sectionModel->saveGlobalHeader('content', $content);
        } else {
            $sectionModel->saveRoleHeader($targetRole, 'title', $title);
            $sectionModel->saveRoleHeader($targetRole, 'subtitle', $subtitle);
            $sectionModel->saveRoleHeader($targetRole, 'content', $content);
        }

        $_SESSION['flash_success'] = 'Texto del panel actualizado correctamente.';
        header('Location: /mvc/public/index.php?page=draft');
        exit;
    }

    if ($action === 'create_section' && $canManageSidebarSections) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title !== '' && $content !== '') {
            $sectionModel->create($title, $content, (int)$currentUser['id']);
            $_SESSION['flash_success'] = 'Sección creada correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Titulo y contenido son obligatorios.';
        }
    } elseif ($action === 'delete_section' && $canManageSidebarSections) {
        $id = (int)($_POST['section_id'] ?? 0);
        if ($id > 0) {
            $sectionModel->delete($id);
            $_SESSION['flash_success'] = 'Sección eliminada.';
        }
    } elseif ($action === 'update_section' && $canManageSidebarSections) {
        $id = (int)($_POST['section_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($id > 0 && $title !== '' && $content !== '') {
            $sectionModel->update($id, $title, $content);
            $_SESSION['flash_success'] = 'Sección actualizada.';
        } else {
            $_SESSION['flash_error'] = 'La sección, el título y el contenido son obligatorios.';
        }
    } elseif ($action === 'create_section_view' && $canManageContentViews) {
        $sectionId = (int)($_POST['section_id'] ?? 0);
        $title = trim($_POST['view_title'] ?? '');
        $kind = $_POST['view_kind'] ?? 'custom';
        $visibleTo = trim((string)($_POST['view_visible_to'] ?? 'all'));
            if ($sectionId > 0 && $title !== '') {
                $sectionModel->createSectionView($sectionId, $title, $kind, $visibleTo, (int)$currentUser['id']);
                $_SESSION['flash_success'] = 'Vista creada correctamente.';
            } else {
                $_SESSION['flash_error'] = 'Debes escribir un nombre para la vista.';
            }
    } elseif ($action === 'delete_section_view' && $canManageContentViews) {
        $viewId = (int)($_POST['view_id'] ?? 0);
        if ($viewId > 0) {
            $view = $sectionModel->getSectionViewById($viewId);
            $sectionId = $view['section_id'] ?? 0;
            $sectionModel->deleteSectionView($viewId);
            $remaining = $sectionModel->getSectionViews((int)$sectionId);
            if (empty($remaining)) {
                // If all views were deleted, create a single default view
                $sectionModel->createSingleDefaultView((int)$sectionId, (int)$currentUser['id']);
            }
            $_SESSION['flash_success'] = 'Vista eliminada correctamente.';
        }
    } elseif ($action === 'create_view_block' && $canManageContentViews) {
        $viewId = (int)($_POST['view_id'] ?? 0);
        $blockType = $_POST['block_type'] ?? 'div';
        $content = trim($_POST['block_content'] ?? '');
        if ($viewId > 0 && $content !== '') {
            $sectionModel->createViewBlock($viewId, $blockType, $content, (int)$currentUser['id']);
            $_SESSION['flash_success'] = 'Bloque añadido correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Contenido del bloque vacío o vista inválida.';
        }
    } elseif ($action === 'update_view_block' && $canManageContentViews) {
        $blockId = (int)($_POST['block_id'] ?? 0);
        $content = trim($_POST['block_content'] ?? '');
        if ($blockId > 0 && $content !== '') {
            $sectionModel->updateViewBlock($blockId, $content);
            $_SESSION['flash_success'] = 'Bloque actualizado.';
        } else {
            $_SESSION['flash_error'] = 'Contenido del bloque vacío o bloque inválido.';
        }
    } elseif ($action === 'delete_view_block' && $canManageContentViews) {
        $blockId = (int)($_POST['block_id'] ?? 0);
        if ($blockId > 0) {
            $sectionModel->deleteViewBlock($blockId);
            $_SESSION['flash_success'] = 'Bloque eliminado.';
        }
    } elseif ($action === 'move_view_block' && $canManageContentViews) {
        $blockId = (int)($_POST['block_id'] ?? 0);
        $direction = ($_POST['direction'] ?? 'up') === 'down' ? 'down' : 'up';
        if ($blockId > 0) {
            if ($sectionModel->moveViewBlock($blockId, $direction)) {
                $_SESSION['flash_success'] = 'Bloque reordenado.';
            } else {
                $_SESSION['flash_error'] = 'No se pudo reordenar el bloque.';
            }
        }
    } elseif ($action === 'move_section_view' && $canManageContentViews) {
        $viewId = (int)($_POST['view_id'] ?? 0);
        $direction = ($_POST['direction'] ?? 'up') === 'down' ? 'down' : 'up';
        if ($viewId > 0) {
            if ($sectionModel->moveSectionView($viewId, $direction)) {
                $_SESSION['flash_success'] = 'Vista reordenada.';
            } else {
                $_SESSION['flash_error'] = 'No se pudo reordenar la vista.';
            }
        }
    }
    // Update view action removed — views are not editable via an editor panel per new requirements

    if (isset($_SESSION['flash_error']) || isset($_SESSION['flash_success'])) {
        header('Location: /mvc/public/index.php?page=draft');
        exit;
    }

    if ($canManageUsers) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $userAction = $_POST['action'] ?? 'update';

        if ($userAction === 'delete' && $userId > 0 && $canDeleteUsers) {
            $target = $userModel->findById($userId);
            if ((int)$userId === (int)$currentUser['id']) {
                $_SESSION['flash_error'] = 'No puedes eliminar tu propio usuario.';
            } elseif ($target && in_array($target['role'], ['admin', 'superadmin'], true) && $currentUser['role'] !== 'superadmin') {
                $_SESSION['flash_error'] = 'No puedes eliminar a un administrador.';
            } else {
                $userModel->deleteUser($userId);
                $_SESSION['flash_success'] = 'Usuario eliminado correctamente.';
            }
        } elseif ($userAction === 'create') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $password = $_POST['password'] ?? '';

            if ($currentUser['role'] === 'admin' && in_array($role, ['admin', 'superadmin'], true)) {
                $_SESSION['flash_error'] = 'No puedes crear usuarios con rol administrativo.';
            } elseif ($username === '' || $password === '') {
                $_SESSION['flash_error'] = 'El usuario y la contraseña son obligatorios.';
            } elseif (strlen($password) < 6) {
                $_SESSION['flash_error'] = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($userModel->usernameExists($username)) {
                $_SESSION['flash_error'] = 'Ya existe un usuario con ese nombre.';
            } elseif ($email !== '' && $userModel->emailExists($email)) {
                $_SESSION['flash_error'] = 'Ya existe un usuario con ese email.';
            } else {
                $userModel->createUser($username, $email, $role, $password);
                $_SESSION['flash_success'] = 'Usuario creado correctamente.';
            }
        } elseif ($userAction === 'update' && $userId > 0) {
            $target = $userModel->findById($userId);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $password = $_POST['password'] ?? '';

            if (!$target) {
                $_SESSION['flash_error'] = 'No se encontró el usuario.';
            } elseif ((int)$userId === (int)$currentUser['id'] && $role !== $currentUser['role']) {
                $_SESSION['flash_error'] = 'No puedes cambiar tu propio rol.';
            } elseif ($currentUser['role'] === 'admin' && in_array($target['role'], ['admin', 'superadmin'], true)) {
                $_SESSION['flash_error'] = 'No puedes modificar a un administrador.';
            } elseif ($currentUser['role'] === 'admin' && in_array($role, ['admin', 'superadmin'], true)) {
                $_SESSION['flash_error'] = 'No puedes asignar un rol administrativo.';
            } elseif ($username === '') {
                $_SESSION['flash_error'] = 'El nombre de usuario es obligatorio.';
            } elseif ($email !== '' && $userModel->emailExists($email, $userId)) {
                $_SESSION['flash_error'] = 'Ya existe otro usuario con ese email.';
            } elseif ($userModel->usernameExists($username, $userId)) {
                $_SESSION['flash_error'] = 'Ya existe otro usuario con ese nombre.';
            } else {
                $userModel->updateUser($userId, $username, $email, $role, $password !== '' ? $password : null);
                $_SESSION['flash_success'] = 'Usuario actualizado correctamente.';
            }
        }
    }

    if (isset($_SESSION['flash_error']) || isset($_SESSION['flash_success'])) {
        header('Location: /mvc/public/index.php?page=draft');
        exit;
    }
}

$users = $userModel->getAll();
if (!$isSuperAdmin) {
    $users = array_values(array_filter($users, function ($u) {
        return $u['role'] !== 'superadmin';
    }));
}

$sections = $sectionModel->getAll();
if (empty($sections)) {
    $defaultSections = [
        ['title' => 'Vista principal', 'content' => 'Panel principal del dashboard con contenido base.']
    ];

    foreach ($defaultSections as $index => $section) {
        $sectionModel->create($section['title'], $section['content'], (int)$currentUser['id'], $index + 1);
    }
    $sections = $sectionModel->getAll();
}

$pendingChanges = [];
$resolvedChanges = [];
$newUsers = $userModel->getNewUsers(3);

$data = DraftModel::getDatos();
$data['titulo'] = $sectionModel->getRoleHeader($currentUser['role'], 'title', $data['titulo']);
$data['subtitulo'] = $sectionModel->getRoleHeader($currentUser['role'], 'subtitle', 'Tus Drafts');
$data['contenido'] = $sectionModel->getRoleHeader($currentUser['role'], 'content', $data['contenido']);
$titulo = 'Drafts';
$bodyClass = 'draft-view';

$headingText = trim($data['titulo']);
if (!empty($data['subtitulo'])) {
    $headingText .= ' - ' . $data['subtitulo'];
}

$contenido = "<h1>" . htmlspecialchars($headingText, ENT_QUOTES, 'UTF-8') . "</h1><p>" . nl2br(htmlspecialchars($data['contenido'], ENT_QUOTES, 'UTF-8')) . "</p>";

ob_start();
include __DIR__ . '/../views/draft.php';
$contenido .= ob_get_clean();

include __DIR__ . '/../views/layout.php';
