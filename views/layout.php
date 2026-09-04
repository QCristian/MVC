<?php if (session_status() === PHP_SESSION_NONE) session_start();
    $dark = isset($_SESSION['darkMode']) && $_SESSION['darkMode'];
    $currentRole = strtolower($_SESSION['role'] ?? 'user');
    $roleClass = 'role-' . $currentRole;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $titulo ?></title>
<link rel="stylesheet" href="/mvc/public/css/estilos.css">
</head>
<body class="<?= trim(($bodyClass ?? '') . ' ' . $roleClass . ($dark ? ' dark-mode' : '')) ?>">
<header class="navbar">
    <a href="/mvc/public/index.php?page=inicio">Inicio</a>
    <a href="/mvc/public/index.php?page=contacto">Contacto</a>

    <div class="right">
        <?php if(!isset($_SESSION['user'])): ?>
            <a href="/mvc/public/index.php?page=login">Iniciar sesión</a>
        <?php else: ?>
            <a href="/mvc/public/index.php?page=draft">Drafts</a>
            <a href="/mvc/public/index.php?page=logout">Cerrar sesión</a>
        <?php endif; ?>

        <button id="theme-toggle" class="btn theme-toggle"><?= $dark ? 'Modo Claro' : 'Modo Oscuro' ?></button>
    </div>
</header>

<?php if(strpos($bodyClass ?? '', 'draft-view') !== false): ?>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">Mi App</div>
            <nav>
                <?php if (!empty($sections)): ?>
                    <?php foreach ($sections as $section): ?>
                        <a href="#section-<?= (int)$section['id'] ?>"><?= htmlspecialchars($section['title']) ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'superadmin'], true)): ?>
                    <a href="#users-section">Usuarios</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'superadmin'], true)): ?>
                    <a href="#panel-header-editor">Texto del panel</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'superadmin'], true)): ?>
                    <a href="#section-create">Nueva sección</a>
                <?php endif; ?>
                <?php /* Solicitudes removed per new requirements */ ?>
            </nav>
            <div class="sidebar-footer">Usuario: <?= htmlspecialchars($_SESSION['user'] ?? '') ?></div>
        </aside>
        <main id="dashboard-main">
            <?= $contenido ?>
        </main>
    </div>
<?php else: ?>
    <main>
        <?= $contenido ?>
    </main>
<?php endif; ?>
<script>
    const body = document.body;
    const toggleBtn = document.getElementById('theme-toggle');

    if (toggleBtn) {
        const setButtonText = (isDark) => toggleBtn.textContent = isDark ? 'Modo Claro' : 'Modo Oscuro';
        setButtonText(body.classList.contains('dark-mode'));

        toggleBtn.addEventListener('click', () => {
            const isDark = body.classList.toggle('dark-mode');
            setButtonText(isDark);
            fetch('/mvc/public/index.php?page=toggle_theme', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({mode: isDark ? 'dark' : 'light'})
            }).catch(e => console.warn('No se pudo guardar preferencia', e));
        });
    }
</script>

<?php if(!empty($extraJs)): ?>
<script src="<?= $extraJs ?>"></script>
<?php endif; ?>

</body>
</html>
