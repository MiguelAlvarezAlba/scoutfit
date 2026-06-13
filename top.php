<?php
require "includes/conexion.php";
require "includes/motor.php";

$listaEquipos = $conexion->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre");

$ranking = [];
$equipo = null;

if (isset($_GET["equipo"]) && $_GET["equipo"] != "") {

    $idE = $_GET["equipo"];

    // 1. Cargar el club elegido (con el valor medio de su plantilla)
    $stmt = $conexion->prepare(
        "SELECT e.*,
                (SELECT IFNULL(AVG(valor_mercado), 0) FROM jugadores
                 WHERE id_equipo = e.id_equipo) AS valor_medio_plantilla
         FROM equipos e WHERE e.id_equipo = ?"
    );
    $stmt->bind_param("i", $idE);
    $stmt->execute();
    $equipo = $stmt->get_result()->fetch_assoc();

    // 2. Cargar TODOS los jugadores (con su edad)
    $jugadores = $conexion->query(
        "SELECT j.*, TIMESTAMPDIFF(YEAR, j.fecha_nac, CURDATE()) AS edad,
                IFNULL(e.nombre, 'Agente libre') AS equipo_actual
         FROM jugadores j
         LEFT JOIN equipos e ON j.id_equipo = e.id_equipo"
    );

    // 3. Pasar cada jugador por el motor y guardar su fit
    while ($j = $jugadores->fetch_assoc()) {
        $r = calcularFit($j, $equipo);
        $j["fit"] = $r["fit"];
        $ranking[] = $j;
    }

    // 4. Ordenar de mayor a menor fit
    usort($ranking, function ($a, $b) {
        return $b["fit"] - $a["fit"];
    });

    // 5. Paginación (el ranking vive en PHP, así que cortamos el array)
    $porPagina = 10;
    $totalResultados = count($ranking);
    $totalPaginas = ceil($totalResultados / $porPagina);
    $paginaActual = isset($_GET["pagina"]) ? (int) $_GET["pagina"] : 1;
    $inicio = ($paginaActual - 1) * $porPagina;
    $rankingPagina = array_slice($ranking, $inicio, $porPagina);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mejores fichajes - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=5">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Mejores fichajes para un club</h1>

    <form method="get">
        <select name="equipo" required>
            <option value="">-- Elige club --</option>
            <?php while ($e = $listaEquipos->fetch_assoc()): ?>
            <option value="<?= $e["id_equipo"] ?>" <?= ($equipo && $equipo["id_equipo"] == $e["id_equipo"]) ? "selected" : "" ?>>
                <?= htmlspecialchars($e["nombre"]) ?>
            </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Buscar fichajes</button>
    </form>

    <?php if ($equipo): ?>
        <h2>Jugadores que mejor encajan en <?= htmlspecialchars($equipo["nombre"]) ?></h2>
        <table>
            <tr><th>#</th><th>Jugador</th><th>Pos.</th><th>Club actual</th><th>Valor</th><th>Fit</th></tr>
            <?php $pos = $inicio + 1; foreach ($rankingPagina as $j): ?>
            <?php
                $partes = explode(" ", $j["nombre"]);
                $ini = mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1);
                $color = $j["fit"] >= 70 ? "#2dd486" : ($j["fit"] >= 40 ? "#f0c040" : "#e05252");
            ?>
            <tr>
                <td><?= $pos++ ?></td>
                <td>
                    <span class="avatar"><?= $ini ?></span>
                    <a href="jugador.php?id=<?= $j["id_jugador"] ?>"><?= htmlspecialchars($j["nombre"]) ?></a>
                </td>
                <td><?= htmlspecialchars($j["posicion"]) ?></td>
                <td><?= htmlspecialchars($j["equipo_actual"]) ?></td>
                <td><?= number_format($j["valor_mercado"], 0, ',', '.') ?> €</td>
                <td><strong style="color: <?= $color ?>"><?= $j["fit"] ?>%</strong></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Navegación de páginas -->
        <div class="paginacion">
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                <?php if ($p == $paginaActual): ?>
                    <span class="pagina-actual"><?= $p ?></span>
                <?php else: ?>
                    <a href="top.php?equipo=<?= $equipo["id_equipo"] ?>&pagina=<?= $p ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</body>
</html>
