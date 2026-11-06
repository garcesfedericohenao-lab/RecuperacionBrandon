<?php
require_once __DIR__ . '/config.php';

// Año automático: "2026" o "2026 - 2028" si ya pasaron años
$anio  = (int) date('Y');
$inicio = (int) ($config['anio_inicio'] ?? $anio);
$rango = $inicio < $anio ? $inicio . ' - ' . $anio : $anio;

// Datos que se muestran en la barra (los campos vacíos se omiten)
$datos = [
    'Desarrollado por' => $config['autor'] ?? '',
    ''                 => trim(($config['rol'] ?? '') . (($config['institucion'] ?? '') ? ' · ' . $config['institucion'] : ''), ' ·'),
    'Correo'           => $config['correo'] ?? '',
    'Teléfono'         => $config['telefono'] ?? '',
    'Ubicación'        => $config['ciudad'] ?? '',
];
?>
  </main>

  <footer class="pie">
    <div class="contenedor pie-barra">
      <div class="pie-izq">
        <strong class="pie-marca">Taller<span>Control</span></strong>
        <small>&copy; <?= e($rango) ?> <?= e($config['autor'] ?? '') ?>. Todos los derechos reservados.</small>
      </div>

      <ul class="pie-datos">
        <?php foreach ($datos as $etiqueta => $valor): ?>
          <?php if ($valor !== ''): ?>
            <li>
              <?php if ($etiqueta === 'Desarrollado por'): ?>
                <span><?= e($etiqueta) ?>:</span> <b><?= e($valor) ?></b>
              <?php elseif ($etiqueta === ''): ?>
                <?= e($valor) ?>
              <?php else: ?>
                <span><?= e($etiqueta) ?>:</span> <?= e($valor) ?>
              <?php endif; ?>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </footer>
</body>
</html>
