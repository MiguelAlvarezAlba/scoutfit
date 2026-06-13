<?php
require "includes/sesion.php";
requerirLogin();
require "includes/conexion.php";
require "includes/api_config.php";

// Ligas que podemos importar: id en la API => [nombre visible, division en TU BD]

    $ligas = [
    140 => ["LaLiga (Primera)", "Primera"],
    141 => ["LaLiga 2 (Segunda)", "Segunda"],
    435 => ["Primera RFEF", "Primera RFEF"],   // <- pon aquí el id real que veas
];


$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idLiga   = $_POST["liga"];
    $division = $ligas[$idLiga][1];

    // 1. Pedir los equipos de la liga a la API
    $ch = curl_init("https://" . API_FOOTBALL_HOST . "/teams?league=" . $idLiga . "&season=2023");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-apisports-key: " . API_FOOTBALL_KEY]);
    $datos = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $equiposApi = $datos["response"] ?? [];

    // 2. Dos consultas preparadas: una para comprobar, otra para insertar
    $comprobar = $conexion->prepare("SELECT id_equipo FROM equipos WHERE nombre = ?");
    $insertar  = $conexion->prepare(
        "INSERT INTO equipos (nombre, ciudad, division, presupuesto, estilo_juego, objetivo, politica_edad, caracter, valores)
         VALUES (?, ?, ?, 0, 'posesion', 'media tabla', 'mixta', 'equilibrado', 'Por definir')"
    );

    $nuevos = 0;
    $saltados = 0;
    foreach ($equiposApi as $item) {
        $nombre = $item["team"]["name"];
        $ciudad = $item["venue"]["city"] ?? "";

        // ¿Ya existe un equipo con ese nombre exacto?
        $comprobar->bind_param("s", $nombre);
        $comprobar->execute();
        $existe = $comprobar->get_result()->fetch_assoc();

        if ($existe) {
            $saltados++;                       // ya estaba: no lo duplico
        } else {
            $insertar->bind_param("sss", $nombre, $ciudad, $division);
            $insertar->execute();
            $nuevos++;
        }
    }

    $mensaje = "✅ $nuevos equipos nuevos importados · $saltados ya existían.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar liga - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=7">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Importar equipos de una liga</h1>

    <?php if ($mensaje): ?><p><?= $mensaje ?></p><?php endif; ?>

    <form method="post">
        <label>Liga:</label>
        <select name="liga" required>
            <?php foreach ($ligas as $idLiga => $info): ?>
            <option value="<?= $idLiga ?>"><?= $info[0] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Importar equipos</button>
    </form>
</body>
</html>
