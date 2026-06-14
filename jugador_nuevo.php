<?php
require "includes/conexion.php";
require "includes/sesion.php";
require_once "includes/roles.php";
requerirLogin();

// Equipos para el desplegable
$listaEquipos = $conexion->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre");

$mensaje = "";

// ¿Han enviado el formulario? (POST = escribir)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Si eligió "Agente libre", el equipo es NULL
    $idEquipo = $_POST["equipo"] ?: null;

    $stmt = $conexion->prepare(
        "INSERT INTO jugadores (id_equipo, nombre, fecha_nac, nacionalidad, posicion, pie_bueno, altura, valor_mercado, salario, fin_contrato, estilo, liderazgo, disciplina, compromiso, rol, posiciones_sec)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssssiddssiiiss",
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
        $_POST["posiciones_sec"]
    );
    $stmt->execute();

    $idNuevo = $conexion->insert_id;
    $mensaje = "✅ Jugador añadido correctamente";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir jugador - ScoutFit</title>
    <link rel="stylesheet" href="css/estilos.css?v=2">
</head>
<body>
    <?php require "includes/menu.php"; ?>
    <h1>Añadir jugador</h1>

    <?php if ($mensaje): ?>
        <p><?= $mensaje ?> &mdash; <a href="jugador.php?id=<?= $idNuevo ?>">ver ficha</a></p>
    <?php endif; ?>

    <form method="post" class="formulario">
        <div class="campo">
            <label>Nombre</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="campo">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nac" required>
        </div>

        <div class="campo">
            <label>Nacionalidad</label>
            <input type="text" name="nacionalidad" required>
        </div>

        <div class="campo">
            <label>Posición</label>
            <select name="posicion" required>
                <option value="Portero">Portero</option>
                <option value="Defensa">Defensa</option>
                <option value="Centrocampista">Centrocampista</option>
                <option value="Delantero">Delantero</option>
            </select>
        </div>

        <div class="campo">
            <label>Rol</label>
            <select name="rol">
                <option value="">-- Sin rol --</option>
                <?php foreach ($ROLES as $grupo => $roles): ?>
                <optgroup label="<?= $grupo ?>">
                    <?php foreach ($roles as $rol): ?>
                    <option value="<?= $rol ?>"><?= $rol ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label>Posiciones secundarias</label>
            <input type="text" name="posiciones_sec" placeholder="Ej. Extremo izq, Mediapunta">
        </div>

        <div class="campo">
            <label>Pie bueno</label>
            <select name="pie_bueno" required>
                <option value="derecho">Derecho</option>
                <option value="izquierdo">Izquierdo</option>
            </select>
        </div>

        <div class="campo">
            <label>Altura (cm)</label>
            <input type="number" name="altura" min="150" max="220" required>
        </div>

        <div class="campo">
            <label>Equipo</label>
            <select name="equipo">
                <option value="">Agente libre</option>
                <?php while ($e = $listaEquipos->fetch_assoc()): ?>
                <option value="<?= $e["id_equipo"] ?>"><?= $e["nombre"] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="campo">
            <label>Valor de mercado (€)</label>
            <input type="number" name="valor_mercado" min="0" step="50000" required>
        </div>

        <div class="campo">
            <label>Salario (€)</label>
            <input type="number" name="salario" min="0" step="10000" required>
        </div>

        <div class="campo">
            <label>Fin de contrato</label>
            <input type="date" name="fin_contrato" required>
        </div>

        <div class="campo">
            <label>Estilo de juego</label>
            <select name="estilo" required>
                <option value="posesion">Posesión</option>
                <option value="contraataque">Contraataque</option>
                <option value="presion alta">Presión alta</option>
                <option value="directo">Directo</option>
            </select>
        </div>

        <div class="campo">
            <label>Liderazgo (0-10)</label>
            <input type="number" name="liderazgo" min="0" max="10" required>
        </div>

        <div class="campo">
            <label>Disciplina (0-10)</label>
            <input type="number" name="disciplina" min="0" max="10" required>
        </div>

        <div class="campo">
            <label>Compromiso (0-10)</label>
            <input type="number" name="compromiso" min="0" max="10" required>
        </div>

        <button type="submit">Guardar jugador</button>
    </form>
</body>
</html>