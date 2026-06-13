<?php
require "includes/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    // password_hash convierte la contraseña en un hash imposible de revertir
    $hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conexion->prepare(
        "INSERT INTO usuarios (email, password, rol) VALUES (?, ?, 'admin')"
    );
    $stmt->bind_param("ss", $email, $hash);

    if ($stmt->execute()) {
        $mensaje = "✅ Usuario creado. Ya puedes <a href='login.php'>iniciar sesión</a>.";
    } else {
        $mensaje = "⚠️ Ese email ya está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=6">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Crear usuario</h1>

    <?php if ($mensaje): ?><p><?= $mensaje ?></p><?php endif; ?>

    <form method="post" class="formulario" style="max-width: 400px; grid-template-columns: 1fr;">
        <div class="campo">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="campo">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Crear usuario</button>
    </form>
</body>
</html>
