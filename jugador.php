<?php
require "includes/conexion.php";

$id = $_GET["id"];

$stmt = $conexion->prepare(
    "SELECT j.*,
            TIMESTAMPDIFF(YEAR, j.fecha_nac, CURDATE()) AS edad,
            IFNULL(e.nombre, 'Agente libre') AS equipo
     FROM jugadores j
     LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
     WHERE j.id_jugador = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$jugador = $stmt->get_result()->fetch_assoc();
if (!$jugador) {
    die("Jugador no encontrado");
}
// Trayectoria del jugador
$stmt = $conexion->prepare(
    "SELECT * FROM stats_jugador WHERE id_jugador = ? ORDER BY temporada DESC"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$trayectoria = $stmt->get_result();

$partes = explode(" ", $jugador["nombre"]);
$iniciales = mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($jugador["nombre"]) ?> - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=4">
</head>
<body>
    <?php require "includes/menu.php"; ?>

    <div class="acciones-ficha">
        <a href="jugadores.php">← Volver al listado</a>
        <a href="jugador_editar.php?id=<?= $jugador["id_jugador"] ?>">✏️ Editar jugador</a>
    </div>

    <!-- Cabecera -->
    <div class="ficha-cabecera">
        <span class="avatar avatar-grande"><?= $iniciales ?></span>
        <div>
            <h1><?= htmlspecialchars($jugador["nombre"]) ?></h1>
            <p class="subtitulo"><?= htmlspecialchars($jugador["posicion"]) ?> · <?= htmlspecialchars($jugador["equipo"]) ?></p>
        </div>
    </div>

    <!-- Dos columnas: datos | radar -->
    <div class="ficha-cuerpo">

        <div class="ficha-datos">
            <div class="dato"><span>Edad</span><strong><?= $jugador["edad"] ?> años</strong></div>
            <div class="dato"><span>Nacionalidad</span><strong><?= htmlspecialchars($jugador["nacionalidad"]) ?></strong></div>
            <div class="dato"><span>Altura</span><strong><?= $jugador["altura"] ?> cm</strong></div>
            <div class="dato"><span>Pie bueno</span><strong><?= htmlspecialchars($jugador["pie_bueno"]) ?></strong></div>
            <div class="dato"><span>Estilo</span><strong><?= htmlspecialchars($jugador["estilo"]) ?></strong></div>
            <div class="dato"><span>Valor de mercado</span><strong><?= number_format($jugador["valor_mercado"], 0, ',', '.') ?> €</strong></div>
            <div class="dato"><span>Salario</span><strong><?= number_format($jugador["salario"], 0, ',', '.') ?> €</strong></div>
            <div class="dato"><span>Fin de contrato</span><strong><?= $jugador["fin_contrato"] ?></strong></div>
        </div>

        <div class="ficha-radar">
            <canvas id="radar"></canvas>
        </div>

    </div>

    <!-- Trayectoria a lo ancho -->
    <div class="ficha-seccion">
        <h2>Trayectoria</h2>
        <?php if ($trayectoria->num_rows > 0): ?>
        <table>
            <tr><th>Temporada</th><th>Club</th><th>Partidos</th><th>Goles</th><th>Asist.</th><th>Nota</th></tr>
            <?php while ($t = $trayectoria->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($t["temporada"]) ?></td>
                <td><?= htmlspecialchars($t["club"]) ?></td>
                <td><?= $t["partidos"] ?></td>
                <td><?= $t["goles"] ?></td>
                <td><?= $t["asistencias"] ?></td>
                <td><?= $t["nota_media"] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p class="sin-datos">Todavía no hay datos de trayectoria para este jugador.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById("radar"), {
        type: "radar",
        data: {
            labels: ["Liderazgo", "Disciplina", "Compromiso"],
            datasets: [{
                label: "<?= $jugador["nombre"] ?>",
                data: [<?= $jugador["liderazgo"] ?>, <?= $jugador["disciplina"] ?>, <?= $jugador["compromiso"] ?>],
                backgroundColor: "rgba(45, 212, 134, 0.3)",
                borderColor: "#2dd486",
                pointBackgroundColor: "#2dd486"
            }]
        },
        options: {
            scales: {
                r: {
                    min: 3, max: 10,
                    grid: { color: "#2a3247" },
                    angleLines: { color: "#2a3247" },
                    pointLabels: { color: "#e8e8e8", font: { size: 14 } },
                    ticks: { display: false }
                }
            },
            plugins: { legend: { labels: { color: "#e8e8e8" } } }
        }
    });
    </script>
</body>
</html>
