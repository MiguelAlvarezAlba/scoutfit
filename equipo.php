<?php
require "includes/conexion.php";
require "includes/menu.php";


$id = $_GET["id"];
$id = $_GET["id"];

$stmt = $conexion->prepare("SELECT * FROM equipos WHERE id_equipo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$equipo = $stmt->get_result()->fetch_assoc();

if (!$equipo) {
    die("Equipo no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $equipo["nombre"] ?> - Scouting</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1><?= $equipo["nombre"] ?></h1>
    <a href="equipos.php">← Volver al listado</a>
    <p><strong>División:</strong> <?= $equipo["division"] ?></p>
    <p><strong>Objetivo:</strong> <?= $equipo["objetivo"] ?></p>
    <p><strong>Política de edad:</strong> <?= $equipo["politica_edad"] ?></p>
    <p><strong>Estilo de juego:</strong> <?= $equipo["estilo_juego"] ?></p>
    <p><strong>Jugadores:</strong></p>
    <ul>
    <?php
    $stmt = $conexion->prepare(
        "SELECT j.id_jugador, j.nombre FROM jugadores j
 WHERE j.id_equipo = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $jugadores = $stmt->get_result();
    while ($jugador = $jugadores->fetch_assoc()) {
        echo "<li><a href='jugador.php?id=" . $jugador["id_jugador"] . "'>" . $jugador["nombre"] . "</a></li>";
    }
    ?>
    </ul>
    <p><strong>Valor total de mercado:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT SUM(valor_mercado) AS total FROM jugadores
         WHERE id_equipo = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()["total"];
    echo number_format($total, 0, ',', '.') . " €";
    ?>
    </p>
    <p><strong>Edad media:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT AVG(TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE())) AS media FROM jugadores
         WHERE id_equipo = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $media = $stmt->get_result()->fetch_assoc()["media"];
    echo number_format($media, 1, ',', '.') . " años";
    ?>
    </p>
    <p><strong>Posiciones:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT posicion, COUNT(*) AS count FROM jugadores
         WHERE id_equipo = ?
         GROUP BY posicion"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $posiciones = $stmt->get_result();
    while ($posicion = $posiciones->fetch_assoc()) {
        echo $posicion["posicion"] . " (" . $posicion["count"] . ") ";
    }   
    ?>
    </p>
    <p><strong>Valor medio por posición:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT posicion, AVG(valor_mercado) AS media FROM jugadores
         WHERE id_equipo = ?
         GROUP BY posicion"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $medias = $stmt->get_result();
    while ($media = $medias->fetch_assoc()) {
        echo $media["posicion"] . ": " . number_format($media["media"], 0, ',', '.') . " € ";
    }
    ?>
    </p>
    <p><strong>Edad media por posición:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT posicion, AVG(TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE())) AS media FROM jugadores
         WHERE id_equipo = ?
         GROUP BY posicion"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $medias = $stmt->get_result();
    while ($media = $medias->fetch_assoc()) {
       echo $media["posicion"] . ": " . number_format($media["media"], 1, ',', '.') . " años ";
    }
    ?>
    </p>
    <p><strong>Valor total por posición:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT posicion, SUM(valor_mercado) AS total FROM jugadores
         WHERE id_equipo = ?
         GROUP BY posicion"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $totales = $stmt->get_result();
    while ($total = $totales->fetch_assoc()) {
        echo $total["posicion"] . ": " . number_format($total["total"], 0, ',', '.') . " € ";
    }
    ?>
    </p>
    <p><strong>Jugadores por política de edad:</strong>
    <?php
    $stmt = $conexion->prepare(
        "SELECT politica_edad, COUNT(*) AS count FROM jugadores j
         JOIN equipos e ON j.id_equipo = e.id_equipo
         WHERE e.id_equipo = ?
         GROUP BY politica_edad"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $politicas = $stmt->get_result();
    while ($politica = $politicas->fetch_assoc()) {
        echo $politica["politica_edad"] . " (" . $politica["count"] . ") ";
    }
    ?>
    </p>
</body>
</html>