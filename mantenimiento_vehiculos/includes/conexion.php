<?php
// Conexión a la base de datos con PDO
// Si usas XAMPP, normalmente el usuario es "root" y la clave está vacía.
session_start();

$host    = 'localhost';
$bd      = 'mantenimiento_vehiculos';
$usuario = 'root';
$clave   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$bd;charset=utf8mb4", $usuario, $clave, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('No se pudo conectar a la base de datos. Revisa que MySQL esté encendido y que hayas importado sql/database.sql.');
}

// Escapa texto antes de mostrarlo en HTML (evita que se cuele código)
function e($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// Guarda un mensaje para mostrarlo en la siguiente página
function mensaje($tipo, $texto) {
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

// Formatea dinero: 85000 -> $85.000,00
function dinero($valor) {
    return '$' . number_format($valor, 2, ',', '.');
}

// Lista de tipos de mantenimiento disponibles
$tipos_mantenimiento = [
    'Cambio de aceite',
    'Revisión de frenos',
    'Alineación y balanceo',
    'Cambio de llantas',
    'Revisión de batería',
    'Mantenimiento general',
    'Reparación de motor',
    'Otro',
];

$estados = ['Pendiente', 'En proceso', 'Terminado'];
