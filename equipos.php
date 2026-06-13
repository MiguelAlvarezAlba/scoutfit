<?php
require "includes/conexion.php";
require "includes/menu.php";

$resultado = $conexion->query("SELECT * FROM equipos ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipos - Scouting</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1>Equipos</h1>
    <table border="1">
        <tr>
            <th>Nombre</th><th>División</th><th>Objetivo</th><th>Política de edad</th><th>Estilo de juego</th>
        </tr>
        <?php while ($equipo = $resultado->fetch_assoc()): ?>
        <tr>
            <td><a href="equipo.php?id=<?= $equipo["id_equipo"] ?>"><?= htmlspecialchars($equipo["nombre"]) ?></a></td>
            <td><?= htmlspecialchars($equipo["division"]) ?></td>
            <td><?= htmlspecialchars($equipo["objetivo"]) ?></td>
            <td><?= htmlspecialchars($equipo["politica_edad"]) ?></td>
            <td><?= htmlspecialchars($equipo["estilo_juego"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>