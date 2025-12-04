<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title><?= $data['titulo'] ?> - Inicio</title><link rel="stylesheet" href="../public/estilos.css"></head>
<link rel="stylesheet" href="/css/estilos.css">
<body>
<header class="navbar">
<a href="/inicio">Inicio</a>
<a href="/sobre">Sobre</a>
<a href="/servicios">Servicios</a>
<a href="/contacto">Contacto</a>
</header>
<main>
<h1><?= $data['titulo'] ?> - Inicio</h1>
<p><?= $data['contenido'] ?></p>
</main>
</body>
</html>
