<a data-target="sec1">Sección 1</a>
<a data-target="sec2">Sección 2</a>

<?php if(isset($_SESSION['user'])): ?>
    <a data-target="sec3">Sección 3</a>
    <a data-target="sec4">Sección 4</a>
<?php endif; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $titulo ?></title>
<link rel="stylesheet" href="/mvc/public/css/estilos.css">
</head>
<body class="<?= $bodyClass ?? '' ?>">

<header class="navbar">
    <a href="/mvc/public/index.php?page=inicio">Inicio</a>
    <a href="/mvc/public/index.php?page=sobre">Sobre</a>
    <a href="/mvc/public/index.php?page=servicios">Servicios</a>
    <a href="/mvc/public/index.php?page=contacto">Contacto</a>
    <a href="/mvc/public/index.php?page=draft">Drafts</a>

    <?php if(!isset($_SESSION['user'])): ?>
        <a href="/mvc/public/index.php?page=login">Iniciar sesión</a>
    <?php else: ?>
        <a href="/mvc/public/index.php?page=logout">Cerrar sesión</a>
    <?php endif; ?>
</header>

<main>
    <?= $contenido ?>
</main>

<!-- Botón para cambiar tema -->
<button id="theme-toggle" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    background: #0b9152;
    color: #fff;
    cursor: pointer;
    z-index: 2000;
">Modo Oscuro</button>

<script>
    const body = document.body;
    const toggleBtn = document.getElementById('theme-toggle');

    // Cargar preferencia guardada
    if(localStorage.getItem('darkMode') === 'true') {
        body.classList.add('dark-mode');
        toggleBtn.textContent = 'Modo Claro';
    }

    toggleBtn.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        const isDark = body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark);
        toggleBtn.textContent = isDark ? 'Modo Claro' : 'Modo Oscuro';
    });
</script>

<?php if(!empty($extraJs)): ?>
<script src="<?= $extraJs ?>"></script>
<?php endif; ?>

</body>
</html>
