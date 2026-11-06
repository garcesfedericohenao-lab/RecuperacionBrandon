<?php
/*
 * MIGRACIÓN del sistema de mantenimiento de vehículos
 * ---------------------------------------------------
 * Crea la base de datos y las tablas automáticamente (sin usar phpMyAdmin).
 *
 * Cómo usarlo desde el navegador:
 *   http://localhost/mantenimiento_vehiculos/migracion.php?accion=subir   -> crea la base de datos y las tablas
 *   http://localhost/mantenimiento_vehiculos/migracion.php?accion=datos   -> inserta datos de ejemplo
 *   http://localhost/mantenimiento_vehiculos/migracion.php?accion=bajar   -> BORRA las tablas (cuidado)
 *
 * Cómo usarlo desde la terminal:
 *   php migracion.php subir
 *   php migracion.php datos
 *   php migracion.php bajar
 */

$host    = 'localhost';
$bd      = 'mantenimiento_vehiculos';
$usuario = 'root';
$clave   = '';

// Si se ejecuta en terminal se lee argv, si no, el parámetro ?accion=
$accion = PHP_SAPI === 'cli' ? ($argv[1] ?? '') : ($_GET['accion'] ?? '');

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

try {
    // Nos conectamos al servidor MySQL (todavía sin elegir base de datos)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $usuario, $clave, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    switch ($accion) {
        case 'subir': subir($pdo, $bd); break;
        case 'datos': datos($pdo, $bd); break;
        case 'bajar': bajar($pdo, $bd); break;
        default:
            echo "Acción no válida. Usa: subir, datos o bajar.\n";
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

// ---------- SUBIR: crea la base de datos y las tablas ----------
function subir($pdo, $bd) {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$bd`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `$bd`");
    echo "Base de datos '$bd' lista.\n";

    // Tabla vehiculos (la placa es UNIQUE: no se puede repetir)
    $pdo->exec("CREATE TABLE IF NOT EXISTS vehiculos (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        placa       VARCHAR(10)  NOT NULL UNIQUE,
        marca       VARCHAR(50)  NOT NULL,
        modelo      VARCHAR(50)  NOT NULL,
        propietario VARCHAR(100) NOT NULL
    )");
    echo "Tabla 'vehiculos' lista.\n";

    // Tabla mantenimientos (el costo debe ser mayor que cero)
    $pdo->exec("CREATE TABLE IF NOT EXISTS mantenimientos (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        vehiculo_id INT NOT NULL,
        tipo        VARCHAR(60) NOT NULL,
        costo       DECIMAL(10,2) NOT NULL CHECK (costo > 0),
        estado      ENUM('Pendiente','En proceso','Terminado') NOT NULL DEFAULT 'Pendiente',
        fecha       DATE NOT NULL,
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE
    )");
    echo "Tabla 'mantenimientos' lista.\n";
    echo "\nMigración terminada. Ya puedes abrir index.php\n";
}

// ---------- DATOS: inserta información de ejemplo ----------
function datos($pdo, $bd) {
    $pdo->exec("USE `$bd`");

    $pdo->exec("INSERT IGNORE INTO vehiculos (placa, marca, modelo, propietario) VALUES
        ('ABC123', 'Chevrolet', 'Spark 2018', 'Laura Gómez'),
        ('XYZ789', 'Renault', 'Logan 2020', 'Carlos Ruiz')");

    // Solo agrega mantenimientos de ejemplo si la tabla está vacía
    $total = $pdo->query('SELECT COUNT(*) FROM mantenimientos')->fetchColumn();
    if ($total == 0) {
        $pdo->exec("INSERT INTO mantenimientos (vehiculo_id, tipo, costo, estado, fecha)
            SELECT id, 'Cambio de aceite', 85000, 'Terminado', CURDATE() FROM vehiculos WHERE placa = 'ABC123'");
        $pdo->exec("INSERT INTO mantenimientos (vehiculo_id, tipo, costo, estado, fecha)
            SELECT id, 'Revisión de frenos', 160000, 'En proceso', CURDATE() FROM vehiculos WHERE placa = 'XYZ789'");
    }
    echo "Datos de ejemplo insertados.\n";
}

// ---------- BAJAR: elimina las tablas (deshace la migración) ----------
function bajar($pdo, $bd) {
    $pdo->exec("USE `$bd`");
    // Primero la tabla hija, luego la tabla padre
    $pdo->exec('DROP TABLE IF EXISTS mantenimientos');
    $pdo->exec('DROP TABLE IF EXISTS vehiculos');
    echo "Tablas eliminadas.\n";
}
