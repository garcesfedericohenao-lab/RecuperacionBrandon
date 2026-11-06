<?php
require 'includes/conexion.php';

$errores = [];
$placa = $marca = $modelo = $propietario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiamos los datos (la placa siempre en mayúsculas y sin espacios)
    $placa       = strtoupper(str_replace(' ', '', trim($_POST['placa'] ?? '')));
    $marca       = trim($_POST['marca'] ?? '');
    $modelo      = trim($_POST['modelo'] ?? '');
    $propietario = trim($_POST['propietario'] ?? '');

    // Validaciones
    if ($placa === '' || $marca === '' || $modelo === '' || $propietario === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    if ($placa !== '' && !preg_match('/^[A-Z0-9-]{5,10}$/', $placa)) {
        $errores[] = 'La placa debe tener entre 5 y 10 letras o números.';
    }

    // La placa no se puede registrar dos veces
    if (!$errores) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM vehiculos WHERE placa = ?');
        $stmt->execute([$placa]);
        if ($stmt->fetchColumn() > 0) {
            $errores[] = "La placa $placa ya está registrada.";
        }
    }

    // Si todo está bien, guardamos
    if (!$errores) {
        try {
            $stmt = $pdo->prepare('INSERT INTO vehiculos (placa, marca, modelo, propietario) VALUES (?, ?, ?, ?)');
            $stmt->execute([$placa, $marca, $modelo, $propietario]);
            mensaje('ok', "Vehículo $placa registrado correctamente.");
            header('Location: index.php');
            exit;
        } catch (PDOException $ex) {
            $errores[] = 'No se pudo guardar el vehículo. Inténtalo de nuevo.';
        }
    }
}

// Últimos vehículos registrados (para aprovechar el espacio de la página)
$recientes = $pdo->query('SELECT placa, marca, modelo, propietario FROM vehiculos ORDER BY id DESC LIMIT 5')->fetchAll();

$titulo = 'Registrar vehículo';
$pagina = 'nuevo';
require 'includes/header.php';
?>

<h1>Registrar vehículo</h1>

<?php foreach ($errores as $error): ?>
  <div class="aviso error"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" class="formulario">
  <label>Placa
    <input type="text" name="placa" value="<?= e($placa) ?>" maxlength="10" placeholder="Ej: ABC123" required>
  </label>
  <label>Marca
    <input type="text" name="marca" value="<?= e($marca) ?>" maxlength="50" placeholder="Ej: Chevrolet" required>
  </label>
  <label>Modelo
    <input type="text" name="modelo" value="<?= e($modelo) ?>" maxlength="50" placeholder="Ej: Spark 2018" required>
  </label>
  <label>Propietario
    <input type="text" name="propietario" value="<?= e($propietario) ?>" maxlength="100" placeholder="Nombre completo" required>
  </label>
  <div class="acciones">
    <button type="submit" class="boton">Guardar vehículo</button>
    <a class="boton secundario" href="index.php">Cancelar</a>
  </div>
</form>

<?php if ($recientes): ?>
  <h2>Últimos vehículos registrados</h2>
  <div class="tabla-caja">
    <table>
      <thead><tr><th>Placa</th><th>Marca</th><th>Modelo</th><th>Propietario</th></tr></thead>
      <tbody>
        <?php foreach ($recientes as $r): ?>
          <tr>
            <td><span class="placa"><?= e($r['placa']) ?></span></td>
            <td><?= e($r['marca']) ?></td>
            <td><?= e($r['modelo']) ?></td>
            <td><?= e($r['propietario']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
