<?php
require "includes/conexion.php";
require "includes/sesion.php";
require_once "includes/roles.php";
requerirLogin();

$id = $_GET["id"];

// Equipos para el desplegable
$listaEquipos = $conexion->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre");

$mensaje = "";

// ¿Han enviado el formulario? (POST = guardar cambios -> UPDATE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idEquipo = $_POST["equipo"] ?: null;

    $stmt = $conexion->prepare(
        "UPDATE jugadores SET
            id_equipo = ?, nombre = ?, fecha_nac = ?, nacionalidad = ?, posicion = ?,
            pie_bueno = ?, altura = ?, valor_mercado = ?, salario = ?, fin_contrato = ?,
            estilo = ?, liderazgo = ?, disciplina = ?, compromiso = ?, rol = ?, posiciones_sec = ?
         WHERE id_jugador = ?"
    );
    $stmt->bind_param("isssssiddssiiissi",
        $idEquipo,
        $_POST["nombre"],
        $_POST["fecha_nac"],
        $_POST["nacionalidad"],
        $_POST["posicion"],
        $_POST["pie_bueno"],
        $_POST["altura"],
        $_POST["valor_mercado"],
        $_POST["salario"],
        $_POST["fin_contrato"],
        $_POST["estilo"],
        $_POST["liderazgo"],
        $_POST["disciplina"],
        $_POST["compromiso"],
        $_POST["rol"],
        $_POST["posiciones_sec"],
        $id
    );
    $stmt->execute();

    $mensaje = "✅ Cambios guardados";
}

// Cargar los datos actuales del jugador para rellenar el formulario
$stmt = $conexion->prepare("SELECT * FROM jugadores WHERE id_jugador = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$jugador = $stmt->get_result()->fetch_assoc();

if (!$jugador) {
    die("Jugador no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar jugador - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=3">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Editar: <?= $jugador["nombre"] ?></h1>

    <?php if ($mensaje): ?>
        <p><?= $mensaje ?> &mdash; <a href="jugador.php?id=<?= $id ?>">ver ficha</a></p>
    <?php endif; ?>

    <form method="post" class="formulario">
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= $jugador["nombre"] ?>" required>
        </div>

        <div class="campo">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nac" value="<?= $jugador["fecha_nac"] ?>" required>
        </div>

        <div class="campo">
            <label>Nacionalidad</label>
            <input type="text" name="nacionalidad" value="<?= $jugador["nacionalidad"] ?>" required>
        </div>

        <div class="campo">
            <label>Posición</label>
            <select name="posicion" required>
                <option value="Portero" <?= $jugador["posicion"] == "Portero" ? "selected" : "" ?>>Portero</option>
                <option value="Defensa" <?= $jugador["posicion"] == "Defensa" ? "selected" : "" ?>>Defensa</option>
                <option value="Centrocampista" <?= $jugador["posicion"] == "Centrocampista" ? "selected" : "" ?>>Centrocampista</option>
                <option value="Delantero" <?= $jugador["posicion"] == "Delantero" ? "selected" : "" ?>>Delantero</option>
            </select>
        </div>

        <div class="campo">
            <label>Rol</label>
            <select name="rol">
                <option value="">-- Sin rol --</option>
                <?php foreach ($ROLES as $grupo => $roles): ?>
                <optgroup label="<?= $grupo ?>">
                    <?php foreach ($roles as $rol): ?>
                    <option value="<?= $rol ?>" <?= ($jugador["rol"] ?? "") == $rol ? "selected" : "" ?>><?= $rol ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label>Posiciones secundarias</label>
            <input type="text" name="posiciones_sec" value="<?= htmlspecialchars($jugador["posiciones_sec"] ?? "") ?>" placeholder="Ej. Extremo izq, Mediapunta">
        </div>

        <div class="campo">
            <label>Pie bueno</label>
            <select name="pie_bueno" required>
                <option value="derecho" <?= $jugador["pie_bueno"] == "derecho" ? "selected" : "" ?>>Derecho</option>
                <option value="izquierdo" <?= $jugador["pie_bueno"] == "izquierdo" ? "selected" : "" ?>>Izquierdo</option>
            </select>
        </div>

        <div class="campo">
            <label>Altura (cm)</label>
            <input type="number" name="altura" min="150" max="220" value="<?= $jugador["altura"] ?>" required>
        </div>

        <div class="campo">
            <label>Equipo</label>
            <select name="equipo">
                <option value="">Agente libre</option>
                <?php while ($e = $listaEquipos->fetch_assoc()): ?>
                <option value="<?= $e["id_equipo"] ?>" <?= $jugador["id_equipo"] == $e["id_equipo"] ? "selected" : "" ?>><?= $e["nombre"] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="campo">
            <label>Valor de mercado (€)</label>
            <input type="number" name="valor_mercado" min="0" step="50000" value="<?= $jugador["valor_mercado"] ?>" required>
        </div>

        <div class="campo">
            <label>Salario (€)</label>
            <input type="number" name="salario" min="0" step="10000" value="<?= $jugador["salario"] ?>" required>
        </div>

        <div class="campo">
            <label>Fin de contrato</label>
            <input type="date" name="fin_contrato" value="<?= $jugador["fin_contrato"] ?>" required>
        </div>

        <div class="campo">
            <label>Estilo de juego</label>
            <select name="estilo" required>
                <option value="posesion" <?= $jugador["estilo"] == "posesion" ? "selected" : "" ?>>Posesión</option>
                <option value="contraataque" <?= $jugador["estilo"] == "contraataque" ? "selected" : "" ?>>Contraataque</option>
                <option value="presion alta" <?= $jugador["estilo"] == "presion alta" ? "selected" : "" ?>>Presión alta</option>
                <option value="directo" <?= $jugador["estilo"] == "directo" ? "selected" : "" ?>>Directo</option>
            </select>
        </div>

        <div class="campo">
            <label>Liderazgo (0-10)</label>
            <input type="number" name="liderazgo" min="0" max="10" value="<?= $jugador["liderazgo"] ?>" required>
        </div>

        <div class="campo">
            <label>Disciplina (0-10)</label>
            <input type="number" name="disciplina" min="0" max="10" value="<?= $jugador["disciplina"] ?>" required>
        </div>

        <div class="campo">
            <label>Compromiso (0-10)</label>
            <input type="number" name="compromiso" min="0" max="10" value="<?= $jugador["compromiso"] ?>" required>
        </div>

        <button type="submit">Guardar cambios</button>
    </form>
</body>
</html>
