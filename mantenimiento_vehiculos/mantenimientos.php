<?php
require 'includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    // ACTUALIZAR el estado del mantenimiento
    if (isset($_POST['actualizar'])) {
        $nuevo_estado = $_POST['estado'] ?? '';
        if (in_array($nuevo_estado, $estados, true)) {
            $stmt = $pdo->prepare('UPDATE mantenimientos SET estado = ? WHERE id = ?');
            $stmt->execute([$nuevo_estado, $id]);
            mensaje('ok', "Estado actualizado a: $nuevo_estado.");
        } else {
            mensaje('error', 'Estado no válido.');
        }
    }

    // ELIMINAR un mantenimiento
    if (isset($_POST['eliminar'])) {
        $stmt = $pdo->prepare('DELETE FROM mantenimientos WHERE id = ?');
        $stmt->execute([$id]);
        mensaje('ok', 'Mantenimiento eliminado.');
    }

    header('Location: mantenimientos.php');
    exit;
}

// CONSULTAR mantenimientos (con filtro opcional por estado)
$filtro = $_GET['estado'] ?? '';
$sql = 'SELECT m.*, v.placa, v.marca, v.modelo, v.propietario
        FROM mantenimientos m
        INNER JOIN vehiculos v ON v.id = m.vehiculo_id';
$params = [];
if (in_array($filtro, $estados, true)) {
    $sql .= ' WHERE m.estado = ?';
    $params[] = $filtro;
}
$sql .= ' ORDER BY m.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mantenimientos = $stmt->fetchAll();

// Total de costos mostrados
$total = 0;
foreach ($mantenimientos as $m) { $total += $m['costo']; }

$titulo = 'Mantenimientos';
$pagina = 'mantenimientos';
require 'includes/header.php';
?>

<div class="encabezado-seccion">
  <h1>Mantenimientos</h1>
  <a class="boton" href="mantenimiento_nuevo.php">Registrar mantenimiento</a>
</div>

<div class="filtros">
  <a href="mantenimientos.php" class="<?= $filtro === '' ? 'activo' : '' ?>">Todos</a>
  <?php foreach ($estados as $est): ?>
    <a href="?estado=<?= urlencode($est) ?>" class="<?= $filtro === $est ? 'activo' : '' ?>"><?= e($est) ?></a>
  <?php endforeach; ?>
</div>

<?php if (count($mantenimientos) === 0): ?>
  <div class="vacio">
    <p>No hay mantenimientos para mostrar.</p>
    <a class="boton" href="mantenimiento_nuevo.php">Registrar mantenimiento</a>
  </div>
<?php else: ?>
  <div class="tabla-caja">
    <table>
      <thead>
        <tr>
          <th>Vehículo</th><th>Propietario</th><th>Tipo</th><th>Costo</th>
          <th>Fecha</th><th>Estado</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mantenimientos as $m): ?>
          <tr>
            <td>
              <span class="placa"><?= e($m['placa']) ?></span>
              <small><?= e($m['marca'] . ' ' . $m['modelo']) ?></small>
            </td>
            <td><?= e($m['propietario']) ?></td>
            <td><?= e($m['tipo']) ?></td>
            <td class="num"><?= dinero($m['costo']) ?></td>
            <td><?= e(date('d/m/Y', strtotime($m['fecha']))) ?></td>
            <td>
              <!-- Formulario para cambiar el estado -->
              <form method="post" class="estado-form">
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                <select name="estado" class="estado estado-<?= str_replace(' ', '-', strtolower($m['estado'])) ?>">
                  <?php foreach ($estados as $est): ?>
                    <option value="<?= e($est) ?>" <?= $m['estado'] === $est ? 'selected' : '' ?>><?= e($est) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="actualizar" class="boton pequeno">Actualizar</button>
              </form>
            </td>
            <td>
              <form method="post" onsubmit="return confirm('¿Eliminar este mantenimiento?');">
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                <button type="submit" name="eliminar" class="boton peligro pequeno">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3">Total de los mantenimientos mostrados</td>
          <td class="num"><?= dinero($total) ?></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
