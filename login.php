<?php
require "includes/sesion.php";
require "includes/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];

    // Buscar al usuario por su email
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    // password_verify compara la contraseña escrita con el hash guardado
    if ($usuario && password_verify($_POST["password"], $usuario["password"])) {
        // Login correcto: guardamos su identidad en la sesión
        $_SESSION["usuario_id"] = $usuario["id_usuario"];
        $_SESSION["usuario_email"] = $usuario["email"];
        header("Location: index.php");
        exit;
    } else {
        $mensaje = "⚠️ Email o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=6">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Iniciar sesión</h1>

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
        <button type="submit">Entrar</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Crear usuario</a></p>
</body>
</html>
