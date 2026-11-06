<?php
require 'includes/conexion.php';

// ELIMINAR un vehículo (también se borran sus mantenimientos)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $stmt = $pdo->prepare('DELETE FROM vehiculos WHERE id = ?');
    $stmt->execute([(int) $_POST['eliminar_id']]);
    mensaje('ok', 'Vehículo eliminado junto con sus mantenimientos.');
    header('Location: index.php');
    exit;
}

// CONSULTAR vehículos (con buscador opcional por placa, marca o propietario)
$buscar = trim($_GET['q'] ?? '');
$sql = 'SELECT v.*, COUNT(m.id) AS total_mant
        FROM vehiculos v
        LEFT JOIN mantenimientos m ON m.vehiculo_id = v.id';
$params = [];
if ($buscar !== '') {
    $sql .= ' WHERE v.placa LIKE ? OR v.marca LIKE ? OR v.propietario LIKE ?';
    $params = ["%$buscar%", "%$buscar%", "%$buscar%"];
}
$sql .= ' GROUP BY v.id ORDER BY v.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

// Datos para las tarjetas de resumen
$resumen = $pdo->query("SELECT
    (SELECT COUNT(*) FROM vehiculos) AS vehiculos,
    (SELECT COUNT(*) FROM mantenimientos WHERE estado <> 'Terminado') AS abiertos,
    (SELECT COALESCE(SUM(costo), 0) FROM mantenimientos) AS total")->fetch();

$titulo = 'Vehículos';
$pagina = 'vehiculos';
require 'includes/header.php';
?>

<div class="encabezado-seccion">
  <h1>Vehículos registrados</h1>
  <a class="boton" href="vehiculo_nuevo.php">Registrar vehículo</a>
</div>

<div class="resumen">
  <div class="dato"><span>Vehículos registrados</span><strong><?= (int) $resumen['vehiculos'] ?></strong></div>
  <div class="dato"><span>Mantenimientos sin terminar</span><strong><?= (int) $resumen['abiertos'] ?></strong></div>
  <div class="dato"><span>Costo total de mantenimientos</span><strong><?= dinero($resumen['total']) ?></strong></div>
</div>

<form method="get" class="buscador">
  <input type="text" name="q" value="<?= e($buscar) ?>" placeholder="Buscar por placa, marca o propietario">
  <button type="submit" class="boton">Buscar</button>
  <?php if ($buscar !== ''): ?><a class="boton secundario" href="index.php">Limpiar</a><?php endif; ?>
</form>

<?php if (count($vehiculos) === 0): ?>
  <div class="vacio">
    <p>No hay vehículos para mostrar.</p>
    <a class="boton" href="vehiculo_nuevo.php">Registrar el primero</a>
  </div>
<?php else: ?>
  <div class="tabla-caja">
    <table>
      <thead>
        <tr>
          <th>Placa</th><th>Marca</th><th>Modelo</th><th>Propietario</th>
          <th>Mantenimientos</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vehiculos as $v): ?>
          <tr>
            <td><span class="placa"><?= e($v['placa']) ?></span></td>
            <td><?= e($v['marca']) ?></td>
            <td><?= e($v['modelo']) ?></td>
            <td><?= e($v['propietario']) ?></td>
            <td><?= (int) $v['total_mant'] ?></td>
            <td>
              <form method="post" onsubmit="return confirm('¿Eliminar este vehículo y todos sus mantenimientos?');">
                <input type="hidden" name="eliminar_id" value="<?= (int) $v['id'] ?>">
                <button type="submit" class="boton peligro pequeno">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
