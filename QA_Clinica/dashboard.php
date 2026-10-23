<?php
$titulo_pestana   = "Dashboard del Tester";
$titulo_pagina    = "Dashboard del Tester";
$subtitulo_pagina = "Panel de control de calidad del software";

// Incluir la configuración y conexión a la base de datos
require_once __DIR__ . '/../config.php';

// Inicializar variables por defecto
$total_casos = 0;
$aprobadas = 0;
$fallidas = 0;
$pendientes = 0;
$ejecutadas = 0;
$porcentaje_aprobacion = 0;
$stroke_dashoffset = 339.29; // Círculo completo vacío por defecto (Radio r=54 -> 2 * pi * 54 = 339.29)
$casos_recientes = [];

try {
    // Verificar si la conexión ($pdo o $conn) está disponible desde config.php
    $db = isset($pdo) ? $pdo : (isset($conn) ? $conn : null);

    if ($db) {
        // 1. Obtener totales para las tarjetas de estadísticas usando la tabla 'qa_casos_prueba'
        // Total de casos de prueba
        $stmt = $db->query("SELECT COUNT(*) FROM qa_casos_prueba");
        $total_casos = $stmt ? $stmt->fetchColumn() : 0;

        // Pruebas aprobadas (campo 'estado')
        $stmt = $db->query("SELECT COUNT(*) FROM qa_casos_prueba WHERE estado = 'Aprobado'");
        $aprobadas = $stmt ? $stmt->fetchColumn() : 0;

        // Pruebas fallidas (campo 'estado')
        $stmt = $db->query("SELECT COUNT(*) FROM qa_casos_prueba WHERE estado = 'Fallido'");
        $fallidas = $stmt ? $stmt->fetchColumn() : 0;

        // Pruebas pendientes (campo 'estado')
        $stmt = $db->query("SELECT COUNT(*) FROM qa_casos_prueba WHERE estado = 'Pendiente'");
        $pendientes = $stmt ? $stmt->fetchColumn() : 0;

        // 2. Cálculos para el Resumen de Calidad
        $ejecutadas = $aprobadas + $fallidas;
        if ($ejecutadas > 0) {
            $porcentaje_aprobacion = round(($aprobadas / $ejecutadas) * 100);
        }

        // Cálculo dinámico del offset para el gráfico de anillos SVG (Circunferencia = 339.29)
        $circunferencia = 339.29;
        $stroke_dashoffset = $circunferencia - ($circunferencia * $porcentaje_aprobacion / 100);

        // 3. Obtener las pruebas recientes para la tabla (últimos 5 registros ordenados por id o fecha de creación)
        $stmt = $db->query("SELECT * FROM qa_casos_prueba ORDER BY id DESC LIMIT 5");
        if ($stmt) {
            $casos_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    // Manejo de errores de base de datos en caso de que ocurran
    $error_db = "Error al cargar los datos: " . $e->getMessage();
}

include 'includes/header.php';
?>

<!-- Si deseas mostrar algún error de conexión de forma limpia -->
<?php if (isset($error_db)): ?>
  <div class="aviso-demo" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
    <span>⚠️</span> <?= htmlspecialchars($error_db) ?>
  </div>
<?php endif; ?>

<!-- ========== Tarjetas de estadísticas ========== -->
<div class="tarjetas">
  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono">📋</div>
    </div>
    <div class="cifra"><?= htmlspecialchars($total_casos) ?></div>
    <h3>Total de casos de prueba</h3>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono icono-exito">✅</div>
    </div>
    <div class="cifra"><?= htmlspecialchars($aprobadas) ?></div>
    <h3>Pruebas aprobadas</h3>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono icono-error">❌</div>
    </div>
    <div class="cifra"><?= htmlspecialchars($fallidas) ?></div>
    <h3>Pruebas fallidas</h3>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono icono-aviso">⏳</div>
    </div>
    <div class="cifra"><?= htmlspecialchars($pendientes) ?></div>
    <h3>Pruebas pendientes</h3>
  </div>
</div>

<!-- ========== Resumen de calidad + Acciones rápidas ========== -->
<div class="fila-doble">

  <section class="resumen">
    <div class="resumen-cabecera">
      <h2>Resumen de calidad</h2>
    </div>

    <div class="anillo">
      <svg viewBox="0 0 120 120" role="img" aria-label="<?= $porcentaje_aprobacion ?> % de aprobación">
        <circle class="anillo-fondo" cx="60" cy="60" r="54"/>
        <circle class="anillo-valor" cx="60" cy="60" r="54"
                stroke-dasharray="339.29" stroke-dashoffset="<?= $stroke_dashoffset ?>"
                transform="rotate(-90 60 60)"/>
      </svg>
      <div class="anillo-texto">
        <strong><?= $porcentaje_aprobacion ?> %</strong>
        <span>de aprobación</span>
      </div>
    </div>

    <ul class="leyenda">
      <li><span class="punto-color c-exito"></span>Aprobadas<b><?= $aprobadas ?></b></li>
      <li><span class="punto-color c-error"></span>Fallidas<b><?= $fallidas ?></b></li>
      <li><span class="punto-color c-aviso"></span>Pendientes<b><?= $pendientes ?></b></li>
    </ul>
    <p class="resumen-nota"><?= $aprobadas ?> aprobadas de <?= $ejecutadas ?> pruebas ejecutadas</p>
  </section>

  <section class="panel">
    <h2 class="panel-titulo">Acciones rápidas</h2>
    <div class="acciones-rapidas">
      <a href="casos_prueba_form.php" class="btn-accion btn-accion-primaria" style="text-decoration: none;">
        <span class="accion-icono">➕</span>
        <span>Crear caso de prueba<small>Documenta un nuevo caso</small></span>
      </a>
      <a href="casos_prueba.php" class="btn-accion" style="text-decoration: none;">
        <span class="accion-icono">▶️</span>
        <span>Ejecutar prueba<small>Registra un resultado</small></span>
      </a>
      <a href="registro_incidentes.php" class="btn-accion" style="text-decoration: none;">
        <span class="accion-icono">🐞</span>
        <span>Registrar incidente<small>Reporta un defecto</small></span>
      </a>
      <a href="plan_pruebas.php" class="btn-accion" style="text-decoration: none;">
        <span class="accion-icono">🗓️</span>
        <span>Ver plan de pruebas<small>Consulta la planificación</small></span>
      </a>
    </div>
  </section>

</div>

<!-- ========== Pruebas recientes ========== -->
<div class="categoria-bloque">
  <div class="categoria-header">
    <span class="badge">Pruebas recientes</span>
    <span class="conteo">Registros en tiempo real</span>
  </div>

  <div class="tabla-wrap">
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Caso de prueba</th>
          <th>Módulo</th>
          <th>Fecha</th>
          <th>Resultado</th>
          <th class="col-accion">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($casos_recientes)): ?>
          <?php foreach ($casos_recientes as $caso): ?>
            <?php 
              // Asignar la clase CSS según el valor del campo 'estado' en tu base de datos
              $estado_lower = strtolower($caso['estado'] ?? '');
              $clase_estado = 'estado-pendiente';
              if ($estado_lower === 'aprobado') {
                  $clase_estado = 'estado-aprobado';
              } elseif ($estado_lower === 'fallido') {
                  $clase_estado = 'estado-fallido';
              }
              
              // Formatear la fecha de creación de forma limpia si existe
              $fecha_formateada = !empty($caso['fecha_creacion']) ? date('d/m/Y', strtotime($caso['fecha_creacion'])) : '';
            ?>
            <tr>
              <td><span class="codigo-caso"><?= htmlspecialchars($caso['codigo']) ?></span></td>
              <td><?= htmlspecialchars($caso['nombre_caso']) ?></td>
              <td><span class="badge-servicio"><?= htmlspecialchars($caso['modulo']) ?></span></td>
              <td class="fecha-celda"><?= htmlspecialchars($fecha_formateada) ?></td>
              <td><span class="estado <?= $clase_estado ?>"><?= htmlspecialchars($caso['estado']) ?></span></td>
              <td class="col-accion">
                <a href="casos_prueba_detalle.php?id=<?= $caso['id'] ?>" class="btn btn-secundario btn-sm" style="text-decoration: none;">👁️ Ver</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No hay pruebas registradas todavía en la base de datos.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>