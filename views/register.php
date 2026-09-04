<h2>Registro</h2>

<?php if(!empty($error)) echo "<p>$error</p>"; ?>
<?php if(!empty($success)) echo "<p>$success</p>"; ?>

<form method="POST" action="/mvc/public/index.php?page=register">
    <input type="text" name="username" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <input type="password" name="password2" placeholder="Repetir contraseña" required>
    <button type="submit">Registrarse</button>
</form>
<a href="/mvc/public/index.php?page=login">Ya tengo cuenta</a>