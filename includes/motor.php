<?php
require_once __DIR__ . "/roles.php";   // afinidad rol -> estilo (función rolAEstilo)

/* ============================================================
   MOTOR DE COMPATIBILIDAD — reutilizable
   Las 5 dimensiones devuelven una nota de 0 a 100.
   ============================================================ */

function notaEconomica($jugador, $equipo) {
    if ($equipo["presupuesto"] <= 0) return 50;   // presupuesto desconocido: nota neutra
    $porcentaje = $jugador["valor_mercado"] / $equipo["presupuesto"] * 100;
    if ($porcentaje <= 10) return 100;  // compra cómoda
    if ($porcentaje <= 30) return 70;
    if ($porcentaje <= 60) return 40;
    return 10;                          // inviable
}

function notaDeportiva($jugador, $equipo) {

    // --- 1. Encaje de ESTILO ---
    $estiloJugador = $jugador["estilo"];
    $estiloClub    = $equipo["estilo_juego"];

    if ($estiloJugador == $estiloClub) {
        $notaEstilo = 100;                                    // mismo estilo
    } elseif (
        ($estiloJugador == "posesion"     && $estiloClub == "presion alta") ||
        ($estiloJugador == "presion alta" && $estiloClub == "posesion")     ||
        ($estiloJugador == "contraataque" && $estiloClub == "directo")      ||
        ($estiloJugador == "directo"      && $estiloClub == "contraataque")
    ) {
        $notaEstilo = 65;                                     // estilos "primos"
    } else {
        $notaEstilo = 15;                                     // chocan
    }

    // --- 2. Encaje de NECESIDAD DE POSICIÓN ---
    $buscada = $equipo["posicion_buscada"] ?? "cualquiera";

    if ($buscada == "cualquiera") {
        $notaPosicion = 70;                                   // el club no busca posición concreta
    } elseif ($jugador["posicion"] == $buscada) {
        $notaPosicion = 100;                                  // cubre la necesidad de titular
    } elseif (strpos($jugador["posiciones_sec"] ?? "", $buscada) !== false) {
        $notaPosicion = 75;                                   // la cubre como secundaria
    } else {
        $notaPosicion = 20;                                   // no cubre lo que el club busca
    }

    // --- 3. Bonus por ROL afín al estilo del club ---
    // Si el rol del jugador "pide" el mismo estilo que juega el club, +10.
    $estiloDelRol = rolAEstilo($jugador["rol"] ?? "");
    $bonusRol = ($estiloDelRol !== null && $estiloDelRol == $estiloClub) ? 10 : 0;

    // --- 4. Combinar: mitad estilo, mitad posición, + bonus de rol (tope 100) ---
    $nota = $notaEstilo * 0.5 + $notaPosicion * 0.5 + $bonusRol;
    return min($nota, 100);
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
