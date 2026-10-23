<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();  // solo usuarios con sesión

$titulo_pestana = "Buscar pacientes";

require_once __DIR__ . '/config.php';   // define BUS_URL y BUS_URI
$BUS_URL = BUS_URL;
$BUS_URI = BUS_URI;

$criterio = trim($_GET['criterio'] ?? '');
$resultados = [];
$error = '';
$mensaje = '';
$tipoMensaje = '';
$seBusco = isset($_GET['criterio']);

ini_set('default_socket_timeout', 5);
$cliente = new SoapClient(null, [
    'location'   => $BUS_URL,
    'uri'        => $BUS_URI,
    'exceptions' => true,
]);

// --- Eliminar un paciente desde los resultados de búsqueda ---
if (isset($_GET['eliminar'])) {
    try {
        $respuesta = $cliente->eliminarPaciente((int) $_GET['eliminar']);
        $mensaje = $respuesta;
        $tipoMensaje = (stripos($respuesta, 'error') === 0) ? 'error' : 'exito';
    } catch (SoapFault $fault) {
        $mensaje = "No se pudo eliminar: " . $fault->getMessage();
        $tipoMensaje = 'error';
    }
}

if ($seBusco) {
    try {
        $resultados = $cliente->buscarPacientes($criterio);
    } catch (SoapFault $fault) {
        $error = "No se pudo consultar el bus de web services: " . $fault->getMessage();
    }
}

include 'includes/header.php';
?>

<h1 class="titulo-pagina">Buscar pacientes</h1>
<p class="subtitulo-pagina">Escribe un nombre, categoría, diagnóstico o doctor para buscar en tiempo real a través del bus.</p>

<form method="GET" action="buscar.php" class="form-card" style="margin-bottom: 20px;">
    <div class="campo full">
        <label for="criterio">Buscar</label>
        <input type="text" id="criterio" name="criterio"
               placeholder="Ej: Asegurado, Ana, Cardiología..."
               value="<?php echo htmlspecialchars($criterio); ?>">
    </div>
    <div class="acciones">
        <button type="submit" class="btn btn-primario">Buscar</button>
        <a href="buscar.php" class="btn btn-secundario">Limpiar</a>
    </div>
</form>

<?php if ($mensaje): ?>
    <div class="alerta <?php echo $tipoMensaje === 'exito' ? 'alerta-exito' : 'alerta-error'; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
<?php elseif ($seBusco): ?>
    <p class="subtitulo-pagina">
        <?php echo count($resultados); ?> resultado(s) para "<?php echo htmlspecialchars($criterio); ?>"
    </p>

    <?php if (empty($resultados)): ?>
        <div class="sin-datos"><p>No se encontraron pacientes con ese criterio.</p></div>
    <?php else: ?>
        <div class="tabla-wrap">
            <table>
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
                    <?php foreach ($resultados as $p): ?>
                        <tr>
                            <td><?php echo (int) $p['id_paciente']; ?></td>
                            <td><?php echo htmlspecialchars($p['nombre_paciente']); ?></td>
                            <td><?php echo htmlspecialchars($p['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($p['categoria']); ?></td>
                            <td><?php echo htmlspecialchars($p['diagnostico']); ?></td>
                            <td><?php echo htmlspecialchars($p['nombre_doctor']); ?></td>
                            <td>
                                <a href="buscar.php?criterio=<?php echo urlencode($criterio); ?>&eliminar=<?php echo (int) $p['id_paciente']; ?>"
                                   class="btn-eliminar-fila"
                                   onclick="return confirm('¿Eliminar a <?php echo htmlspecialchars(addslashes($p['nombre_paciente'])); ?>?');">
                                    🗑️ Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

<p style="margin-top: 20px;">
    Cada búsqueda realizada aquí queda registrada y puede administrarse (o borrarse) desde
    <a href="admin.php">el panel de administración</a>.
</p>

<?php include 'includes/footer.php'; ?>