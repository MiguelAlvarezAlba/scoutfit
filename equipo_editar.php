<?php
require "includes/sesion.php";
requerirLogin();
require "includes/conexion.php";

$id = $_GET["id"];

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conexion->prepare(
        "UPDATE equipos SET
            nombre = ?, ciudad = ?, division = ?, presupuesto = ?,
            estilo_juego = ?, objetivo = ?, politica_edad = ?, caracter = ?, valores = ?
         WHERE id_equipo = ?"
    );
    $stmt->bind_param("sssdsssssi",
        $_POST["nombre"],
        $_POST["ciudad"],
        $_POST["division"],
        $_POST["presupuesto"],
        $_POST["estilo_juego"],
        $_POST["objetivo"],
        $_POST["politica_edad"],
        $_POST["caracter"],
        $_POST["valores"],
        $id
    );
    $stmt->execute();
    $mensaje = "✅ Cambios guardados";
}

// Cargar los datos actuales
$stmt = $conexion->prepare("SELECT * FROM equipos WHERE id_equipo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$equipo = $stmt->get_result()->fetch_assoc();
if (!$equipo) {
    die("Equipo no encontrado");
}

// Función rápida para marcar la opción seleccionada
function sel($valor, $actual) {
    return $valor == $actual ? "selected" : "";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar: <?= htmlspecialchars($equipo["nombre"]) ?> - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=7">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Editar: <?= htmlspecialchars($equipo["nombre"]) ?></h1>

    <?php if ($mensaje): ?>
        <p><?= $mensaje ?> &mdash; <a href="equipo.php?id=<?= $id ?>">ver ficha</a></p>
    <?php endif; ?>

    <form method="post" class="formulario">
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($equipo["nombre"]) ?>" required>
        </div>

        <div class="campo">
            <label>Ciudad</label>
            <input type="text" name="ciudad" value="<?= htmlspecialchars($equipo["ciudad"]) ?>">
        </div>

        <div class="campo">
            <label>División</label>
            <select name="division">
                <option value="Primera" <?= sel("Primera", $equipo["division"]) ?>>Primera</option>
                <option value="Segunda" <?= sel("Segunda", $equipo["division"]) ?>>Segunda</option>
                <option value="Primera RFEF" <?= sel("Primera RFEF", $equipo["division"]) ?>>Primera RFEF</option>
            </select>
        </div>

        <div class="campo">
            <label>Presupuesto (€)</label>
            <input type="number" name="presupuesto" min="0" step="100000" value="<?= $equipo["presupuesto"] ?>">
        </div>

        <div class="campo">
            <label>Estilo de juego</label>
            <select name="estilo_juego">
                <option value="posesion" <?= sel("posesion", $equipo["estilo_juego"]) ?>>Posesión</option>
                <option value="contraataque" <?= sel("contraataque", $equipo["estilo_juego"]) ?>>Contraataque</option>
                <option value="presion alta" <?= sel("presion alta", $equipo["estilo_juego"]) ?>>Presión alta</option>
                <option value="directo" <?= sel("directo", $equipo["estilo_juego"]) ?>>Directo</option>
            </select>
        </div>

        <div class="campo">
            <label>Objetivo</label>
            <select name="objetivo">
                <option value="ganar titulos" <?= sel("ganar titulos", $equipo["objetivo"]) ?>>Ganar títulos</option>
                <option value="europa" <?= sel("europa", $equipo["objetivo"]) ?>>Europa</option>
                <option value="ascender" <?= sel("ascender", $equipo["objetivo"]) ?>>Ascender</option>
                <option value="permanencia" <?= sel("permanencia", $equipo["objetivo"]) ?>>Permanencia</option>
                <option value="media tabla" <?= sel("media tabla", $equipo["objetivo"]) ?>>Media tabla</option>
            </select>
        </div>

        <div class="campo">
            <label>Política de edad</label>
            <select name="politica_edad">
                <option value="cantera" <?= sel("cantera", $equipo["politica_edad"]) ?>>Cantera</option>
                <option value="mixta" <?= sel("mixta", $equipo["politica_edad"]) ?>>Mixta</option>
                <option value="veteranos" <?= sel("veteranos", $equipo["politica_edad"]) ?>>Veteranos</option>
            </select>
        </div>

        <div class="campo">
            <label>Carácter</label>
            <select name="caracter">
                <option value="familiar" <?= sel("familiar", $equipo["caracter"]) ?>>Familiar</option>
                <option value="competitivo" <?= sel("competitivo", $equipo["caracter"]) ?>>Competitivo</option>
                <option value="ambicioso" <?= sel("ambicioso", $equipo["caracter"]) ?>>Ambicioso</option>
                <option value="equilibrado" <?= sel("equilibrado", $equipo["caracter"]) ?>>Equilibrado</option>
            </select>
        </div>

        <div class="campo">
            <label>Valores</label>
            <input type="text" name="valores" value="<?= htmlspecialchars($equipo["valores"]) ?>">
        </div>

        <button type="submit">Guardar cambios</button>
    </form>
</body>
</html>
