<h2>Cambiar contraseña</h2>
<?php if(!empty($error)) echo "<p>$error</p>"; ?>
<?php if(!empty($success)) echo "<p>$success</p>"; ?>
<form method="POST" action="/mvc/public/index.php?page=change_password">
    <input type="password" name="password" placeholder="Nueva contraseña" required>
    <input type="password" name="password2" placeholder="Repetir contraseña" required>
    <button type="submit">Guardar</button>
</form>
