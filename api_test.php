<?php
require "includes/api_config.php";

// 1. Preparar la llamada a la API
$ch = curl_init("https://" . API_FOOTBALL_HOST . "/leagues?country=Spain");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-apisports-key: " . API_FOOTBALL_KEY
]);

// 2. Ejecutar y recoger la respuesta
$respuesta = curl_exec($ch);
curl_close($ch);

// 3. Traducir el JSON a un array de PHP
$datos = json_decode($respuesta, true);

// 4. Mostrarlo en bruto para ver qué llega
echo "<pre>";
print_r($datos);
echo "</pre>";