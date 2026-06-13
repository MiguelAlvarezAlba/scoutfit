<?php
require "includes/conexion.php";
require "includes/sesion.php";

$id = $_GET["id"];

// Datos del club
$stmt = $conexion->prepare("SELECT * FROM equipos WHERE id_equipo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$equipo = $stmt->get_result()->fetch_assoc();
if (!$equipo) {
    die("Equipo no encontrado");
}

// Estadísticas globales de la plantilla
$stmt = $conexion->prepare(
    "SELECT COUNT(*) AS num, IFNULL(SUM(valor_mercado), 0) AS total,
            AVG(TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE())) AS edad_media
     FROM jugadores WHERE id_equipo = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Plantilla (lista de jugadores)
$stmt = $conexion->prepare(
    "SELECT id_jugador, nombre, posicion,
            TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE()) AS edad, valor_mercado
     FROM jugadores WHERE id_equipo = ? ORDER BY nombre"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$plantilla = $stmt->get_result();

// Análisis por posición (todo en una consulta)
$stmt = $conexion->prepare(
    "SELECT posicion, COUNT(*) AS num, AVG(valor_mercado) AS valor_medio,
            AVG(TIMESTAMPDIFF(YEAR, fecha_nac, CURDATE())) AS edad_media
     FROM jugadores WHERE id_equipo = ? GROUP BY posicion"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$porPosicion = $stmt->get_result();

// Iniciales del club para el avatar
$partes = explode(" ", $equipo["nombre"]);
$iniciales = mb_substr($partes[0], 0, 1) . (isset($partes[1]) ? mb_substr($partes[1], 0, 1) : "");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($equipo["nombre"]) ?> - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=7">
</head>
<body>
    <?php require "includes/menu.php"; ?>

    <div class="acciones-ficha">
        <a href="equipos.php">← Volver al listado</a>
        <?php if (estaLogueado()): ?>
        <a href="equipo_editar.php?id=<?= $equipo["id_equipo"] ?>">✏️ Editar</a>
        <a href="borrar_equipo.php?id=<?= $equipo["id_equipo"] ?>"
           onclick="return confirm('¿Seguro que quieres borrar este equipo?')">🗑️ Borrar</a>
        <?php endif; ?>
    </div>

    <!-- Cabecera -->
    <div class="ficha-cabecera">
        <span class="avatar avatar-grande"><?= $iniciales ?></span>
        <div>
            <h1><?= htmlspecialchars($equipo["nombre"]) ?></h1>
            <p class="subtitulo"><?= htmlspecialchars($equipo["division"]) ?> · <?= htmlspecialchars($equipo["ciudad"]) ?></p>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <div class="stat-card"><span><?= $stats["num"] ?></span><label>Jugadores</label></div>
        <div class="stat-card"><span><?= number_format($stats["total"], 0, ',', '.') ?> €</span><label>Valor plantilla</label></div>
        <div class="stat-card"><span><?= $stats["edad_media"] ? number_format($stats["edad_media"], 1, ',', '.') : "-" ?></span><label>Edad media</label></div>
        <div class="stat-card"><span><?= number_format($equipo["presupuesto"], 0, ',', '.') ?> €</span><label>Presupuesto</label></div>
    </div>

    <!-- Dos columnas: perfil del club | análisis por posición -->
    <div class="ficha-cuerpo">

        <div class="ficha-datos">
            <div class="dato"><span>Estilo de juego</span><strong><?= htmlspecialchars($equipo["estilo_juego"]) ?></strong></div>
            <div class="dato"><span>Objetivo</span><strong><?= htmlspecialchars($equipo["objetivo"]) ?></strong></div>
            <div class="dato"><span>Política de edad</span><strong><?= htmlspecialchars($equipo["politica_edad"]) ?></strong></div>
            <div class="dato"><span>Carácter</span><strong><?= htmlspecialchars($equipo["caracter"]) ?></strong></div>
            <div class="dato"><span>Valores</span><strong><?= htmlspecialchars($equipo["valores"]) ?></strong></div>
        </div>

        <div class="ficha-radar">
            <h3 style="margin-top: 0;">Análisis por posición</h3>
            <table>
                <tr><th>Posición</th><th>Nº</th><th>Valor medio</th><th>Edad media</th></tr>
                <?php while ($p = $porPosicion->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($p["posicion"]) ?></td>
                    <td><?= $p["num"] ?></td>
                    <td><?= number_format($p["valor_medio"], 0, ',', '.') ?> €</td>
                    <td><?= number_format($p["edad_media"], 1, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>

    <!-- Plantilla a lo ancho -->
    <div class="ficha-seccion">
        <h2>Plantilla</h2>
        <table>
            <tr><th>Jugador</th><th>Posición</th><th>Edad</th><th>Valor</th></tr>
            <?php while ($j = $plantilla->fetch_assoc()): ?>
            <tr>
                <td><a href="jugador.php?id=<?= $j["id_jugador"] ?>"><?= htmlspecialchars($j["nombre"]) ?></a></td>
                <td><?= htmlspecialchars($j["posicion"]) ?></td>
                <td><?= $j["edad"] ?></td>
                <td><?= number_format($j["valor_mercado"], 0, ',', '.') ?> €</td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
