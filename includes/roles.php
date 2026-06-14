<?php
// Roles de scouting agrupados por posición (estilo Football Manager).
// Se usan en los desplegables de los formularios de jugador.
$ROLES = [
    "Portero" => [
        "Portero clásico",
        "Portero-líbero",
    ],
    "Defensa" => [
        "Central",
        "Central que sale jugando",
        "Central contundente",
        "Líbero",
        "Lateral",
        "Lateral ofensivo",
        "Carrilero",
        "Lateral interior",
    ],
    "Centrocampista" => [
        "Pivote defensivo",
        "Mediocentro destructor",
        "Organizador en salida (Regista)",
        "Box-to-box",
        "Mezzala (interior)",
        "Mediapunta / Enganche",
        "Organizador avanzado",
        "Todoterreno",
    ],
    "Ataque" => [
        "Extremo",
        "Extremo a pie cambiado",
        "Extremo trabajador",
        "Segundo punta",
        "Delantero centro",
        "Killer (rematador)",
        "Falso 9",
        "Hombre objetivo",
        "Delantero de presión",
        "Delantero completo",
    ],
];

// Afinidad ROL -> ESTILO de juego (aproximada). La usa el motor para el bonus.
// Los roles que no aparecen aquí se consideran "neutros" (sin afinidad fuerte).
function rolAEstilo($rol) {
    $map = [
        // posesión
        "Central que sale jugando"         => "posesion",
        "Líbero"                           => "posesion",
        "Portero-líbero"                   => "posesion",
        "Organizador en salida (Regista)"  => "posesion",
        "Organizador avanzado"             => "posesion",
        "Mediapunta / Enganche"            => "posesion",
        "Falso 9"                          => "posesion",
        // presión alta
        "Mediocentro destructor"           => "presion alta",
        "Box-to-box"                       => "presion alta",
        "Todoterreno"                      => "presion alta",
        "Extremo trabajador"               => "presion alta",
        "Delantero de presión"             => "presion alta",
        // contraataque
        "Lateral ofensivo"                 => "contraataque",
        "Carrilero"                        => "contraataque",
        "Mezzala (interior)"               => "contraataque",
        "Extremo"                          => "contraataque",
        "Extremo a pie cambiado"           => "contraataque",
        "Segundo punta"                    => "contraataque",
        // directo
        "Central contundente"              => "directo",
        "Pivote defensivo"                 => "directo",
        "Killer (rematador)"               => "directo",
        "Hombre objetivo"                  => "directo",
    ];
    return $map[$rol] ?? null;   // null = rol neutro o desconocido
}
