<?php
require "includes/conexion.php";

$buscarRaw = $_GET["buscar"] ?? "";
$buscar    = "%" . $buscarRaw . "%";
$division  = $_GET["division"] ?? "";

// --- ORDENACIÓN SEGURA ---
// El ORDER BY no admite ? (prepared statement), así que usamos LISTA BLANCA:
// solo se permite ordenar por estas columnas. Lo demás se ignora.
$columnasValidas = ["nombre", "division", "objetivo", "politica_edad", "estilo_juego"];
$orden = in_array($_GET["orden"] ?? "", $columnasValidas) ? $_GET["orden"] : "nombre";
$dir   = (($_GET["dir"] ?? "asc") === "desc") ? "desc" : "asc";

$stmt = $conexion->prepare(
    "SELECT * FROM equipos
     WHERE nombre LIKE ? AND (division = ? OR ? = '')
     ORDER BY $orden $dir"
);
$stmt->bind_param("sss", $buscar, $division, $division);
$stmt->execute();
$resultado = $stmt->get_result();

// Pinta una cabecera con enlace para ordenar (conservando los filtros)
function cabecera($col, $texto) {
    global $orden, $dir, $buscarRaw, $division;
    $nuevaDir = ($orden === $col && $dir === "asc") ? "desc" : "asc";
    $flecha   = $orden === $col ? ($dir === "asc" ? " ▲" : " ▼") : "";
    $params = http_build_query([
        "buscar"   => $buscarRaw,
        "division" => $division,
        "orden"    => $col,
        "dir"      => $nuevaDir,
    ]);
    echo "<th><a href='equipos.php?$params'>" . $texto . $flecha . "</a></th>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipos - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=8">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Equipos</h1>

    <form method="get">
        <input type="text" name="buscar" placeholder="Buscar equipo..."
               value="<?= htmlspecialchars($buscarRaw) ?>">
        <select name="division">
            <option value="">Todas las divisiones</option>
            <option value="Primera" <?= $division == "Primera" ? "selected" : "" ?>>Primera</option>
            <option value="Segunda" <?= $division == "Segunda" ? "selected" : "" ?>>Segunda</option>
            <option value="Primera RFEF" <?= $division == "Primera RFEF" ? "selected" : "" ?>>Primera RFEF</option>
        </select>
        <button type="submit">Buscar</button>
    </form>

    <table>
        <tr>
            <?php
            cabecera("nombre", "Nombre");
            cabecera("division", "División");
            cabecera("objetivo", "Objetivo");
            cabecera("politica_edad", "Política de edad");
            cabecera("estilo_juego", "Estilo de juego");
            ?>
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
