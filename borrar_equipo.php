<?php
require "includes/sesion.php";
requerirLogin();               // borrar es destructivo -> solo logueado
require "includes/conexion.php";

$id = $_GET["id"];

$stmt = $conexion->prepare("DELETE FROM equipos WHERE id_equipo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: equipos.php");   // vuelve al listado
exit;