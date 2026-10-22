<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();  // solo usuarios con sesión

$titulo_pestana = "Pacientes";

require_once __DIR__ . '/config.php';   // define BUS_URL y BUS_URI
$BUS_URL = BUS_URL;
$BUS_URI = BUS_URI;

$mensaje = '';
$tipoMensaje = '';
$pacientesPorCategoria = [];
$errorConsulta = '';

try {
    $cliente = new SoapClient(null, [
        'location'   => $BUS_URL,
        'uri'        => $BUS_URI,
        'exceptions' => true,
    ]);

    // --- Eliminar un paciente, si se solicitó ---
    if (isset($_GET['eliminar'])) {
        $respuesta = $cliente->eliminarPaciente((int) $_GET['eliminar']);
        $mensaje = $respuesta;
        $tipoMensaje = (stripos($respuesta, 'error') === 0) ? 'error' : 'exito';
    }

    // --- Listado actual (ya reflejando el borrado, si hubo) ---
    $pacientes = $cliente->listarPacientes();

    foreach ($pacientes as $paciente) {
        $categoria = $paciente['categoria'] ?: 'Sin categoría';
        $pacientesPorCategoria[$categoria][] = $paciente;
    }
} catch (SoapFault $fault) {
    $errorConsulta = "No se pudo consultar el bus de web services: " . $fault->getMessage();
}

include 'includes/header.php';
?>

<h1 class="titulo-pagina">Pacientes registrados</h1>
<p class="subtitulo-pagina">Listado de pacientes registrados en la clínica (obtenido a través del bus SOAP).</p>

<?php if ($mensaje): ?>
  <div class="alerta <?php echo $tipoMensaje === 'exito' ? 'alerta-exito' : 'alerta-error'; ?>">
    <?php echo htmlspecialchars($mensaje); ?>
  </div>
<?php endif; ?>

<?php if ($errorConsulta): ?>
  <div class="alerta alerta-error"><?php echo htmlspecialchars($errorConsulta); ?></div>
<?php endif; ?>

<?php if (empty($pacientesPorCategoria) && !$errorConsulta): ?>
  <div class="sin-datos">
    <p>Aún no hay pacientes registrados.</p>
  </div>
<?php else: ?>
  <?php foreach ($pacientesPorCategoria as $categoria => $listaPacientes): ?>
    <div class="categoria-bloque">
      <div class="categoria-header">
        <span class="badge"><?php echo htmlspecialchars($categoria); ?></span>
        <span class="conteo"><?php echo count($listaPacientes); ?> paciente(s)</span>
      </div>

      <div class="tabla-wrap">
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre del paciente</th>
              <th>Dirección</th>
              <th>Categoría</th>
              <th>Diagnóstico</th>
              <th>Nombre del doctor</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($listaPacientes as $p): ?>
              <tr>
                <td><?php echo (int) $p['id_paciente']; ?></td>
                <td><?php echo htmlspecialchars($p['nombre_paciente']); ?></td>
                <td><?php echo htmlspecialchars($p['direccion']); ?></td>
                <td><?php echo htmlspecialchars($p['categoria']); ?></td>
                <td><?php echo htmlspecialchars($p['diagnostico']); ?></td>
                <td><?php echo htmlspecialchars($p['nombre_doctor']); ?></td>
                <td>
                  <a href="pacientes.php?eliminar=<?php echo (int) $p['id_paciente']; ?>"
                     class="btn-eliminar-fila"
                     onclick="return confirm('¿Eliminar a <?php echo htmlspecialchars(addslashes($p['nombre_paciente'])); ?>? Esta acción no se puede deshacer.');">
                    🗑️ Eliminar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>