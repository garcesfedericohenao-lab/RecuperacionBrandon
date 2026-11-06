<?php
require 'includes/conexion.php';

$errores = [];
$vehiculo_id = '';
$tipo = '';
$costo = '';

// Vehículos para el selector
$vehiculos = $pdo->query('SELECT id, placa, marca, modelo FROM vehiculos ORDER BY placa')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo_id = (int) ($_POST['vehiculo_id'] ?? 0);
    $tipo        = trim($_POST['tipo'] ?? '');
    $costo       = trim($_POST['costo'] ?? '');

    // Validaciones
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM vehiculos WHERE id = ?');
    $stmt->execute([$vehiculo_id]);
    if ($stmt->fetchColumn() == 0) {
        $errores[] = 'Selecciona un vehículo válido.';
    }
    if (!in_array($tipo, $tipos_mantenimiento, true)) {
        $errores[] = 'Selecciona un tipo de mantenimiento.';
    }
    // El costo debe ser un número mayor que cero
    if ($costo === '' || !is_numeric($costo)) {
        $errores[] = 'El costo debe ser un número.';
    } elseif ((float) $costo <= 0) {
        $errores[] = 'El costo debe ser mayor que cero.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            "INSERT INTO mantenimientos (vehiculo_id, tipo, costo, estado, fecha)
             VALUES (?, ?, ?, 'Pendiente', CURDATE())"
        );
        $stmt->execute([$vehiculo_id, $tipo, $costo]);
        mensaje('ok', 'Mantenimiento registrado con estado Pendiente.');
        header('Location: mantenimientos.php');
        exit;
    }
}

// Últimos mantenimientos registrados (para aprovechar el espacio de la página)
$recientes = $pdo->query('SELECT m.tipo, m.costo, m.estado, v.placa
                          FROM mantenimientos m INNER JOIN vehiculos v ON v.id = m.vehiculo_id
                          ORDER BY m.id DESC LIMIT 5')->fetchAll();

$titulo = 'Registrar mantenimiento';
$pagina = 'mant_nuevo';
require 'includes/header.php';
?>

<h1>Registrar mantenimiento</h1>

<?php foreach ($errores as $error): ?>
  <div class="aviso error"><?= e($error) ?></div>
<?php endforeach; ?>

<?php if (count($vehiculos) === 0): ?>
  <div class="vacio">
    <p>Primero necesitas registrar un vehículo.</p>
    <a class="boton" href="vehiculo_nuevo.php">Registrar vehículo</a>
  </div>
<?php else: ?>
  <form method="post" class="formulario">
    <label>Vehículo
      <select name="vehiculo_id" required>
        <option value="">Selecciona un vehículo</option>
        <?php foreach ($vehiculos as $v): ?>
          <option value="<?= (int) $v['id'] ?>" <?= $vehiculo_id == $v['id'] ? 'selected' : '' ?>>
            <?= e($v['placa'] . ' - ' . $v['marca'] . ' ' . $v['modelo']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Tipo de mantenimiento
      <select name="tipo" required>
        <option value="">Selecciona un tipo</option>
        <?php foreach ($tipos_mantenimiento as $t): ?>
          <option value="<?= e($t) ?>" <?= $tipo === $t ? 'selected' : '' ?>><?= e($t) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Costo
      <input type="number" name="costo" value="<?= e($costo) ?>" min="0.01" step="0.01" placeholder="Ej: 85000" required>
    </label>
    <div class="acciones">
      <button type="submit" class="boton">Guardar mantenimiento</button>
      <a class="boton secundario" href="mantenimientos.php">Cancelar</a>
    </div>
  </form>
<?php endif; ?>

<?php if ($recientes): ?>
  <h2>Últimos mantenimientos registrados</h2>
  <div class="tabla-caja">
    <table>
      <thead><tr><th>Placa</th><th>Tipo</th><th>Costo</th><th>Estado</th></tr></thead>
      <tbody>
        <?php foreach ($recientes as $r): ?>
          <tr>
            <td><span class="placa"><?= e($r['placa']) ?></span></td>
            <td><?= e($r['tipo']) ?></td>
            <td class="num"><?= dinero($r['costo']) ?></td>
            <td><?= e($r['estado']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
