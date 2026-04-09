<h2>Iniciar sesión</h2>

<?php if (!empty($error)): ?>
    <p><?= $error ?></p>
<?php endif; ?>

<form method="POST" action="/mvc/public/index.php?page=login">
    <input type="text" name="username" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
</form>

<p>¿No tienes cuenta? <a href="/mvc/public/index.php?page=register">Regístrate aquí</a></p>