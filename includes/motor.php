<?php
/* ============================================================
   MOTOR DE COMPATIBILIDAD — reutilizable
   Las 5 dimensiones devuelven una nota de 0 a 100.
   ============================================================ */

function notaEconomica($jugador, $equipo) {
    $porcentaje = $jugador["valor_mercado"] / $equipo["presupuesto"] * 100;
    if ($porcentaje <= 10) return 100;  // compra cómoda
    if ($porcentaje <= 30) return 70;
    if ($porcentaje <= 60) return 40;
    return 10;                          // inviable
}

function notaDeportiva($jugador, $equipo) {
    $estiloJugador = $jugador["estilo"];
    $estiloClub = $equipo["estilo_juego"];

    if ($estiloJugador == $estiloClub) return 100;            // mismo estilo

    // estilos "primos" (en ambos sentidos)
    if ($estiloJugador == "posesion" && $estiloClub == "presion alta") return 65;
    if ($estiloJugador == "presion alta" && $estiloClub == "posesion") return 65;
    if ($estiloJugador == "contraataque" && $estiloClub == "directo") return 65;
    if ($estiloJugador == "directo" && $estiloClub == "contraataque") return 65;

    return 15;                                                // chocan
}

function notaEdad($jugador, $equipo) {
    $edad = $jugador["edad"];
    $politica = $equipo["politica_edad"];

    if ($politica == "cantera") {
        if ($edad <= 18) return 100;
        if ($edad <= 23) return 70;
        if ($edad <= 28) return 40;
        return 10;
    }

    if ($politica == "veteranos") {
        if ($edad <= 24) return 40;   // demasiado joven para club de veteranos
        if ($edad <= 30) return 70;
        return 100;                   // 31+ : experiencia, ideal aquí
    }

    if ($politica == "mixta") {
        if ($edad <= 18) return 60;
        if ($edad >= 34) return 60;
        return 90;
    }

    return 50; // red de seguridad
}

function notaValores($jugador, $equipo) {
    $liderazgo = $jugador["liderazgo"];
    $disciplina = $jugador["disciplina"];
    $compromiso = $jugador["compromiso"];
    $caracter = $equipo["caracter"];

    if ($caracter == "familiar")     return ($liderazgo * 0.5 + $disciplina * 0.3 + $compromiso * 0.2) * 10;
    if ($caracter == "competitivo")  return ($liderazgo * 0.2 + $disciplina * 0.5 + $compromiso * 0.3) * 10;
    if ($caracter == "ambicioso")    return ($liderazgo * 0.3 + $disciplina * 0.2 + $compromiso * 0.5) * 10;
    if ($caracter == "equilibrado")  return ($liderazgo * 0.33 + $disciplina * 0.33 + $compromiso * 0.34) * 10;

    return 50; // red de seguridad
}

function notaNivel($jugador, $equipo) {
    $media = $equipo["valor_medio_plantilla"];
    if ($media == 0) return 50;   // club sin plantilla: no se puede comparar

    $ratio = $jugador["valor_mercado"] / $media;
    if ($ratio < 0.5) return 10;
    if ($ratio < 0.8) return 40;
    if ($ratio <= 1.2) return 70;
    if ($ratio <= 2) return 90;
    return 20;                     // demasiado para este club
}

/* ============================================================
   calcularFit() — junta las 5 notas, aplica pesos y veto,
   y devuelve todo en un array. Esto es lo que reutilizamos.
   ============================================================ */
function calcularFit($jugador, $equipo) {

    $notas = [
        "Encaje deportivo"  => ["nota" => notaDeportiva($jugador, $equipo), "peso" => 0.30],
        "Encaje económico"  => ["nota" => notaEconomica($jugador, $equipo), "peso" => 0.20],
        "Proyecto y edad"   => ["nota" => notaEdad($jugador, $equipo),      "peso" => 0.20],
        "Valores y cultura" => ["nota" => notaValores($jugador, $equipo),   "peso" => 0.15],
        "Nivel"             => ["nota" => notaNivel($jugador, $equipo),     "peso" => 0.15],
    ];

    $fit = 0;
    foreach ($notas as $n) {
        $fit += $n["nota"] * $n["peso"];
    }

    $vetado = false;
    if ($notas["Encaje económico"]["nota"] <= 10) {
        $fit = min($fit, 30);
        $vetado = true;
    }

    return [
        "fit"    => round($fit),
        "notas"  => $notas,
        "vetado" => $vetado,
    ];
}
