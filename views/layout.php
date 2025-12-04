<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $titulo ?></title>
<link rel="stylesheet" href="/css/estilos.css">
</head>
<body class="<?= $bodyClass ?? '' ?>">

<header class="navbar">
    <a href="/inicio">Inicio</a>
    <a href="/sobre">Sobre</a>
    <a href="/servicios">Servicios</a>
    <a href="/contacto">Contacto</a>
    <a href="/draft">Drafts</a>
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
