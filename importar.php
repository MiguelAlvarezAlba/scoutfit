<?php
require "includes/sesion.php";
requerirLogin();                 // importar ESCRIBE en la BD -> solo logueado
require "includes/conexion.php";
require "includes/api_config.php";

// Mapa: posición de la API (inglés) -> tus categorías
$mapaPosiciones = [
    "Goalkeeper" => "Portero",
    "Defender"   => "Defensa",
    "Midfielder" => "Centrocampista",
    "Attacker"   => "Delantero",
];

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEquipoApi   = $_POST["team_api"];          // id del equipo EN LA API (ej. 531)
    $idEquipoLocal = $_POST["equipo"] ?: null;    // id del equipo EN TU BD

    // 1. Pedir la plantilla a la API (lo que ya sabes hacer)
    $ch = curl_init("https://" . API_FOOTBALL_HOST . "/players/squads?team=" . $idEquipoApi);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-apisports-key: " . API_FOOTBALL_KEY]);
    $datos = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $jugadoresApi = $datos["response"][0]["players"] ?? [];

    // 2. Preparar el INSERT una sola vez (FUERA del bucle)
    $stmt = $conexion->prepare(
        "INSERT INTO jugadores (id_equipo, nombre, fecha_nac, nacionalidad, posicion, pie_bueno, altura, valor_mercado, salario, fin_contrato, estilo, liderazgo, disciplina, compromiso)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $importados = 0;
    foreach ($jugadoresApi as $jug) {
        $nombre   = $jug["name"];
        $edad     = $jug["age"];
        // Fecha de nacimiento aproximada a partir de la edad
        $fechaNac = $edad ? date("Y-m-d", strtotime("-$edad years")) : "2000-01-01";
        // Traducir la posición; si no la conoce, Centrocampista por defecto
        $posicion = $mapaPosiciones[$jug["position"]] ?? "Centrocampista";

        // Valores por defecto para lo que la API NO da (el club los edita luego)
        $nacionalidad = "Por definir";
        $pie = "derecho"; $altura = 180;
        $valor = 0; $salario = 0; $finContrato = "2027-06-30";
        $estilo = "posesion"; $lid = 5; $dis = 5; $com = 5;

        $stmt->bind_param("isssssiddssiii",
            $idEquipoLocal, $nombre, $fechaNac, $nacionalidad, $posicion,
            $pie, $altura, $valor, $salario, $finContrato,
            $estilo, $lid, $dis, $com
        );
        $stmt->execute();
        $importados++;
    }

    $mensaje = "✅ Importados $importados jugadores.";
}

$listaEquipos = $conexion->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar plantilla - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=6">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Importar plantilla desde API-Football</h1>

    <?php if ($mensaje): ?><p><?= $mensaje ?></p><?php endif; ?>

    <form method="post">
        <label>ID del equipo en la API (ej. 531 = Athletic)</label>
        <input type="number" name="team_api" required>

        <label>Asignar a tu club:</label>
        <select name="equipo" required>
            <?php while ($e = $listaEquipos->fetch_assoc()): ?>
            <option value="<?= $e["id_equipo"] ?>"><?= htmlspecialchars($e["nombre"]) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Importar</button>
    </form>
</body>
</html>