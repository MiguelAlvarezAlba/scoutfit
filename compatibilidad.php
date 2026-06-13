<?php
require "includes/conexion.php";
require "includes/menu.php";


// Listas para los desplegables
$listaJugadores = $conexion->query("SELECT id_jugador, nombre FROM jugadores ORDER BY nombre");
$listaEquipos   = $conexion->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre");

/* ============================================================
   LAS 5 DIMENSIONES — cada función devuelve una nota de 0 a 100
   ============================================================ */

// EJEMPLO RESUELTO (mío): encaje económico
function notaEconomica($jugador, $equipo) {
    $porcentaje = $jugador["valor_mercado"] / $equipo["presupuesto"] * 100;

    if ($porcentaje <= 10) return 100;  // compra cómoda
    if ($porcentaje <= 30) return 70;
    if ($porcentaje <= 60) return 40;
    return 10;                          // inviable
}

// TUYA: estilo del jugador vs estilo_juego del club
function notaDeportiva($jugador, $equipo) {
    $estiloJugador = $jugador["estilo"];
    $estiloClub = $equipo["estilo_juego"];
   // Caso 1: mismo estilo
    if ($estiloJugador == $estiloClub) return 100;

    // Caso 2: estilos "primos" (en ambos sentidos, ojo)
    if ($estiloJugador == "posesion" && $estiloClub == "presion alta") return 65;
    if ($estiloJugador == "presion alta" && $estiloClub == "posesion") return 65;
    if ($estiloJugador == "contraataque" && $estiloClub == "directo") return 65;
    if ($estiloJugador == "directo" && $estiloClub == "contraataque") return 65;
    

    // ... ¿qué otros pares se parecen? decide tú y añade sus ifs ...

    // Caso 3: el resto = estilos que chocan
    return 15;
    
}

// TUYA: edad vs politica_edad del club (cantera / mixta / veteranos)
function notaEdad($jugador, $equipo) {
    $edad = $jugador["edad"];
    $politica = $equipo["politica_edad"];

    if ($politica == "cantera") {
        if ($edad <= 18) return 100;   // jovencísimo: perfecto para cantera
        if ($edad <= 23) return 70;    // joven: bien
        if ($edad <= 28) return 40;    // media edad: regular
        return 10;                     // veterano: mal encaje aquí
    }

    if ($politica == "veteranos") {   
        if ($edad <= 24) return 40; 
        if ($edad <= 30) return 70;  
        if ($edad <= 35) return 100;  
         
        return 10;                     
    }
    

    if ($politica == "mixta") {
        if ($edad <= 18) return 60;   // demasiado verde hasta para un mixto
        if ($edad >= 34) return 60;   // demasiado mayor
    return 90;                    // cualquier edad razonable: genial
}
    

    return 50; // red de seguridad: si la política no es ninguna conocida

    }
    


// TUYA: liderazgo, disciplina, compromiso (0-10) vs el carácter del club
function notaValores($jugador, $equipo) {
    $liderazgo = $jugador["liderazgo"];
    $disciplina = $jugador["disciplina"];
    $compromiso = $jugador["compromiso"];
    $caracter = $equipo["caracter"];

    if($caracter == "familiar") {
        return ($liderazgo * 0.5 + $disciplina * 0.3 + $compromiso * 0.2) * 10;
    }
    if($caracter == "competitivo") {
        return ($liderazgo * 0.2 + $disciplina * 0.5 + $compromiso * 0.3) * 10;
    }
    if($caracter == "ambicioso") {
        return ($liderazgo * 0.3 + $disciplina * 0.2 + $compromiso * 0.5) * 10;
    }
    if($caracter == "equilibrado") {
        return ($liderazgo * 0.33 + $disciplina * 0.33 + $compromiso * 0.34) * 10;
        
}
}
// TUYA: valor del jugador vs valor medio de la plantilla del club
function notaNivel($jugador, $equipo) {
    $media = $equipo["valor_medio_plantilla"];

    if ($media == 0) return 0;   // club sin plantilla: no puedo comparar, nota neutra

    $ratio = $jugador["valor_mercado"] / $media;

    // --- TU CASCADA: de menor a mayor ratio ---
    if ($ratio < 0.5) return 10;    // vale muchísimo menos que la media: no da el nivel
    if ($ratio < 0.8) return 40;    // algo por debajo: justito
    if ($ratio <= 1.2) return 70;   // en la zona de la media: encaje ideal
    if ($ratio <= 2) return 90;      // por encima: refuerzo de lujo, aún bien
    return 20;                     // estratosférico: demasiado para este club
}
    



/* ============================================================
   CÁLCULO (cuando el formulario ya se ha enviado)
   ============================================================ */
$resultado = null;
$vetado = false;

if (isset($_GET["jugador"]) && isset($_GET["equipo"])) {

    $idJ = $_GET["jugador"];
    $idE = $_GET["equipo"];

    $stmt = $conexion->prepare(
        "SELECT j.*, TIMESTAMPDIFF(YEAR, j.fecha_nac, CURDATE()) AS edad
         FROM jugadores j WHERE j.id_jugador = ?"
    );
    $stmt->bind_param("i", $idJ);
    $stmt->execute();
    $jugador = $stmt->get_result()->fetch_assoc();

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
        $notas = [
            "Encaje deportivo"   => ["nota" => notaDeportiva($jugador, $equipo), "peso" => 0.30],
            "Encaje económico"   => ["nota" => notaEconomica($jugador, $equipo), "peso" => 0.20],
            "Proyecto y edad"    => ["nota" => notaEdad($jugador, $equipo),      "peso" => 0.20],
            "Valores y cultura"  => ["nota" => notaValores($jugador, $equipo),   "peso" => 0.15],
            "Nivel"              => ["nota" => notaNivel($jugador, $equipo),     "peso" => 0.15],
        ];

        $fit = 0;
        foreach ($notas as $n) {
            $fit += $n["nota"] * $n["peso"];
        }
        // VETO: si el fichaje es económicamente inviable, el encaje global se hunde
       if ($notas["Encaje económico"]["nota"] <= 10) {
    $fit = min($fit, 30);
    $vetado = true;
}

        $resultado = round($fit);  
    }
    // VEREDICTO  ← AQUÍ va lo nuevo
if ($resultado >= 75)      $veredicto = "Fichaje muy recomendado";
elseif ($resultado >= 50)  $veredicto = "Fichaje viable, con matices";
if ($vetado) $veredicto = "Descartado";
elseif ($resultado >= 30)  $veredicto = "Poco recomendable";
else                       $veredicto = "Descartado";   
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compatibilidad - Scouting</title>
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
                

 <div style="max-width: 700px;">
         <canvas id="desglose"></canvas>
 </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
<?php
// Convertir las notas PHP a arrays simples
$valoresNotas = [];
foreach ($notas as $n) {
    $valoresNotas[] = $n["nota"];
}
?>
const notas = <?= json_encode($valoresNotas) ?>;
const dimensiones = <?= json_encode(array_keys($notas)) ?>;

// Semáforo: color según la nota
const colores = notas.map(n => n >= 70 ? "#2dd486" : (n >= 40 ? "#f0c040" : "#e05252"));

new Chart(document.getElementById("desglose"), {
    type: "bar",
    data: {
        labels: dimensiones,
        datasets: [{ data: notas, backgroundColor: colores, borderRadius: 6 }]
    },
    options: {
        maintainAspectRatio: false,      
        indexAxis: "y",
        scales: {
            x: { min: 0, max: 100, grid: { color: "#2a3247" }, ticks: { color: "#e8e8e8" } },
            y: { grid: { display: false }, ticks: { color: "#e8e8e8", font: { size: 14 } } }
        },
        plugins: { legend: { display: false } }
    }
});
        </script>

    <?php endif; ?>      
</body>
</html>