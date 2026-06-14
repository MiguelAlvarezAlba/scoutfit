<?php
require "includes/conexion.php";
require "includes/menu.php";
require "includes/motor.php";   // el motor vive AQUÍ y solo aquí (DRY)

// Listas para los desplegables
$listaJugadores = $conexion->query("SELECT id_jugador, nombre FROM jugadores ORDER BY nombre");
$listaEquipos   = $conexion->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre");

$resultado = null;
$vetado = false;

if (isset($_GET["jugador"]) && isset($_GET["equipo"])) {

    $idJ = $_GET["jugador"];
    $idE = $_GET["equipo"];

    // Cargar el jugador (con su edad calculada)
    $stmt = $conexion->prepare(
        "SELECT j.*, TIMESTAMPDIFF(YEAR, j.fecha_nac, CURDATE()) AS edad
         FROM jugadores j WHERE j.id_jugador = ?"
    );
    $stmt->bind_param("i", $idJ);
    $stmt->execute();
    $jugador = $stmt->get_result()->fetch_assoc();

    // Cargar el club (con el valor medio de su plantilla)
    $stmt = $conexion->prepare(
        "SELECT e.*,
                (SELECT IFNULL(AVG(valor_mercado), 0) FROM jugadores
                 WHERE id_equipo = e.id_equipo) AS valor_medio_plantilla
         FROM equipos e WHERE e.id_equipo = ?"
    );
    $stmt->bind_param("i", $idE);
    $stmt->execute();
    $equipo = $stmt->get_result()->fetch_assoc();

    if ($jugador && $equipo) {
        // Una sola llamada al motor (la lógica está en includes/motor.php)
        $r = calcularFit($jugador, $equipo);
        $notas     = $r["notas"];
        $resultado = $r["fit"];
        $vetado    = $r["vetado"];

        // Veredicto en texto según el resultado final
        if ($resultado >= 75)      $veredicto = "Fichaje muy recomendado";
        elseif ($resultado >= 50)  $veredicto = "Fichaje viable, con matices";
        elseif ($resultado >= 30)  $veredicto = "Poco recomendable";
        else                       $veredicto = "Descartado";
        if ($vetado)               $veredicto = "Descartado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compatibilidad - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <h1>Motor de compatibilidad</h1>

    <form method="get">
        <select name="jugador" required>
            <option value="">-- Elige jugador --</option>
            <?php while ($j = $listaJugadores->fetch_assoc()): ?>
            <option value="<?= $j["id_jugador"] ?>"><?= htmlspecialchars($j["nombre"]) ?></option>
            <?php endwhile; ?>
        </select>

        <select name="equipo" required>
            <option value="">-- Elige club --</option>
            <?php while ($e = $listaEquipos->fetch_assoc()): ?>
            <option value="<?= $e["id_equipo"] ?>"><?= htmlspecialchars($e["nombre"]) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Calcular encaje</button>
    </form>

    <?php if ($resultado !== null): ?>
        <h2><?= htmlspecialchars($jugador["nombre"]) ?> → <?= htmlspecialchars($equipo["nombre"]) ?></h2>
        <p style="font-size: 48px; margin: 10px 0;"><strong><?= $resultado ?>%</strong></p>
        <p><strong><?= $veredicto ?></strong></p>

        <?php if ($vetado): ?>
            <p>⚠️ Operación inviable económicamente</p>
        <?php endif; ?>

        <div class="desglose">
            <table>
                <tr><th>Dimensión</th><th>Nota</th></tr>
                <?php foreach ($notas as $nombre => $n): ?>
                <tr>
                    <td><?= $nombre ?></td>
                    <td><?= $n["nota"] ?> / 100</td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div class="grafico">
                <canvas id="desglose"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        <?php
        $valoresNotas = [];
        foreach ($notas as $n) {
            $valoresNotas[] = $n["nota"];
        }
        ?>
        const notas = <?= json_encode($valoresNotas) ?>;
        const dimensiones = <?= json_encode(array_keys($notas)) ?>;
        const colores = notas.map(n => n >= 70 ? "#2dd486" : (n >= 40 ? "#f0c040" : "#e05252"));

        new Chart(document.getElementById("desglose"), {
            type: "bar",
            data: {
                labels: dimensiones,
                datasets: [{ data: notas, backgroundColor: colores, borderRadius: 8, barThickness: 38 }]
            },
            options: {
                maintainAspectRatio: false,
                indexAxis: "y",
                scales: {
                    x: { min: 0, max: 100, grid: { color: "#2a3247" }, ticks: { color: "#e8e8e8" } },
                    y: { grid: { display: false }, ticks: { color: "#e8e8e8", font: { size: 16, weight: "bold" } } }
                },
                plugins: { legend: { display: false } }
            }
        });
        </script>
    <?php endif; ?>
</body>
</html>
