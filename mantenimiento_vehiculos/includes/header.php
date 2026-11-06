<?php
// Encabezado común. Antes de incluirlo hay que definir $titulo y $pagina.
$pagina = $pagina ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> | Mantenimiento de vehículos</title>
  <link rel="stylesheet" href="css/estilos.css?v=<?= filemtime(__DIR__ . '/../css/estilos.css') ?>">
</head>
<body>
  <header class="barra">
    <div class="contenedor barra-interna">
      <a class="marca" href="index.php">Taller<span>Control</span></a>
      <nav>
        <a href="index.php"               class="<?= $pagina === 'vehiculos' ? 'activo' : '' ?>">Vehículos</a>
        <a href="vehiculo_nuevo.php"      class="<?= $pagina === 'nuevo' ? 'activo' : '' ?>">Registrar vehículo</a>
        <a href="mantenimiento_nuevo.php" class="<?= $pagina === 'mant_nuevo' ? 'activo' : '' ?>">Registrar mantenimiento</a>
        <a href="mantenimientos.php"      class="<?= $pagina === 'mantenimientos' ? 'activo' : '' ?>">Mantenimientos</a>
      </nav>
    </div>
  </header>

  <main class="contenedor">
    <?php if (!empty($_SESSION['mensaje'])): ?>
      <div class="aviso <?= e($_SESSION['mensaje']['tipo']) ?>">
        <?= e($_SESSION['mensaje']['texto']) ?>
      </div>
      <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
