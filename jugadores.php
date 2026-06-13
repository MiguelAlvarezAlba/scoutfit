<?php
require "includes/conexion.php";



$busqueda = "%" . ($_GET["buscar"] ?? "") . "%";

$stmt = $conexion->prepare(
    "SELECT j.*, TIMESTAMPDIFF(YEAR, j.fecha_nac, CURDATE()) AS edad,
            IFNULL(e.nombre, 'Agente libre') AS equipo
     FROM jugadores j
     LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
     WHERE j.nombre LIKE ?
     ORDER BY j.nombre"
);
$stmt->bind_param("s", $busqueda);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Jugadores - Scouting</title>
        <link rel="stylesheet" href="css/estilos.css">
    </head>
    <body>
        <?php require "includes/menu.php"; ?>
        <h1>Jugadores</h1>
        <a href="jugador_nuevo.php">➕ Añadir jugador</a>
        <form method="get">
             <input type="text" name="buscar" placeholder="Buscar jugador..."
           value="<?= $_GET["buscar"] ?? "" ?>">
            <button type="submit">Buscar</button>
        </form>
        
        <table>
            <tr>
                <th>Nombre</th><th>Edad</th><th>Posición</th><th>Equipo</th><th>Valor de mercado</th>
            </tr>
            <?php while ($jugador = $resultado->fetch_assoc()): ?>
           


<tr>
    <td>
        <?php
$partes = explode(" ", $jugador["nombre"]);
  $iniciales = mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1);
?>
        <span class="avatar"><?= $iniciales ?></span>
        <a href="jugador.php?id=<?= $jugador["id_jugador"] ?>"><?= $jugador["nombre"] ?></a>
    </td>
    <td><?= $jugador["edad"] ?></td>
    <td><?= $jugador["posicion"] ?></td>
    <td><?= $jugador["equipo"] ?></td>
    <td><?= number_format($jugador["valor_mercado"], 0, ',', '.') ?> €</td>
</tr>
                
            <?php endwhile; ?>
        </table>
    </body>
</html>
