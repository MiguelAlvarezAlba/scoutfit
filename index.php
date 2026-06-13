<?php
require "includes/conexion.php";

// Contadores para la portada (uno te lo doy, el otro es tuyo)
$totalEquipos = $conexion->query("SELECT COUNT(*) AS total FROM equipos")->fetch_assoc()["total"];
$totalJugadores = $conexion->query("SELECT COUNT(*) AS total FROM jugadores")->fetch_assoc()["total"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title> ScoutFit ⚽ - Inicio</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php require "includes/menu.php"; ?>

    <h1>ScoutFit ⚽</h1>
    <p>Una herramienta que, antes de fichar a un jugador, te dice en un % si encaja con el estilo de
    juego del club, su presupuesto y sus valores — y te explica por qué. Es, en esencia, data scouting: lo
    que hacen hoy los departamentos de datos de los clubes profesionales.</p>

    <!-- Contadores -->
    <p><strong><?= $totalEquipos ?></strong> clubes · <strong><?= $totalJugadores ?></strong> jugadores en la base de datos</p>

    <!-- Accesos directos a las 3 secciones -->
<ul class="tarjetas">
    <li><a href="equipos.php">Explorar equipos</a></li>
    <li><a href="jugadores.php">Explorar jugadores</a></li>
    <li><a href="compatibilidad.php">Calcular compatibilidad</a></li>
</ul>
</body>
</html>