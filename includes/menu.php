<?php
// Asegura que la sesión esté disponible (sin duplicar si ya se arrancó)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <a href="index.php" class="logo">⚽ ScoutFit</a>
    <a href="equipos.php">Equipos</a>
    <a href="jugadores.php">Jugadores</a>
    <a href="compatibilidad.php">Compatibilidad</a>
    <a href="top.php">Mejores fichajes</a>

    <?php if (isset($_SESSION["usuario_id"])): ?>
        <a href="logout.php">Salir (<?= $_SESSION["usuario_email"] ?>)</a>
    <?php else: ?>
        <a href="login.php">Entrar</a>
    <?php endif; ?>
</nav>