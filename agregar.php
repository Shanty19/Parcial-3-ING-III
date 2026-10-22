<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();  // solo usuarios con sesión

$titulo_pestana = "Agregar paciente";

$mensaje = '';
$tipoMensaje = '';
$doctores = [];

$nombreIngresado = $_POST['nombre'] ?? '';
$direccionIngresada = $_POST['direccion'] ?? '';
$categoriaSeleccionada = $_POST['categoria'] ?? '';
$diagnosticoIngresado = $_POST['diagnostico'] ?? '';
$doctorSeleccionado = $_POST['id_doctor'] ?? '';

require_once __DIR__ . '/config.php';   // define BUS_URL y BUS_URI
$BUS_URL = BUS_URL;
$BUS_URI = BUS_URI;

$cliente = new SoapClient(null, [
    'location'   => $BUS_URL,
    'uri'        => $BUS_URI,
    'exceptions' => true,
]);

// --- Cargar doctores para el <select>, vía el bus ---
try {
    $doctores = $cliente->listarDoctores();
} catch (SoapFault $fault) {
    $mensaje = "No se pudieron cargar los doctores desde el bus: " . $fault->getMessage();
    $tipoMensaje = 'error';
}

// --- Procesar el envío del formulario, vía el bus ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($nombreIngresado);
    $direccion = trim($direccionIngresada);
    $categoria = trim($categoriaSeleccionada);
    $diagnostico = trim($diagnosticoIngresado);
    $idDoctor = trim($doctorSeleccionado);

    if ($nombre === '') {
        $mensaje = "El nombre del paciente es obligatorio.";
        $tipoMensaje = 'error';
    } elseif ($direccion === '') {
        $mensaje = "La dirección es obligatoria.";
        $tipoMensaje = 'error';
    } elseif ($categoria === '') {
        $mensaje = "Debes seleccionar una categoría.";
        $tipoMensaje = 'error';
    } elseif ($diagnostico === '') {
        $mensaje = "El diagnóstico es obligatorio.";
        $tipoMensaje = 'error';
    } elseif ($idDoctor === '' || !ctype_digit($idDoctor)) {
        $mensaje = "Debes seleccionar un doctor válido.";
        $tipoMensaje = 'error';
    } else {
        try {
            // El bus valida de nuevo internamente y devuelve un mensaje de texto.
            $respuesta = $cliente->agregarPaciente(
                $nombre,
                $direccion,
                $categoria,
                $diagnostico,
                (int) $idDoctor
            );

            $mensaje = $respuesta;
            $tipoMensaje = (stripos($respuesta, 'error') === 0) ? 'error' : 'exito';

            if ($tipoMensaje === 'exito') {
                $nombreIngresado = '';
                $direccionIngresada = '';
                $categoriaSeleccionada = '';
                $diagnosticoIngresado = '';
                $doctorSeleccionado = '';
            }
        } catch (SoapFault $fault) {
            $mensaje = "Error al comunicarse con el bus: " . $fault->getMessage();
            $tipoMensaje = 'error';
        }
    }
}

include 'includes/header.php';
?>

<h1 class="titulo-pagina">Agregar paciente</h1>
<p class="subtitulo-pagina">Registra un nuevo paciente y asígnale un doctor (a través del bus SOAP).</p>

<?php if ($mensaje): ?>
  <div class="alerta <?php echo $tipoMensaje === 'exito' ? 'alerta-exito' : 'alerta-error'; ?>">
    <?php echo htmlspecialchars($mensaje); ?>
  </div>
<?php endif; ?>

<div class="form-card">
  <form action="agregar.php" method="POST">
    <div class="form-seccion-titulo">Datos del paciente</div>
    <div class="form-grid">
      <div class="campo">
        <label for="nombre">Nombre del paciente *</label>
        <input type="text" id="nombre" name="nombre" required
               value="<?php echo htmlspecialchars($nombreIngresado); ?>">
      </div>
      <div class="campo">
        <label for="direccion">Dirección *</label>
        <input type="text" id="direccion" name="direccion" required
               value="<?php echo htmlspecialchars($direccionIngresada); ?>">
      </div>
      <div class="campo">
        <label for="categoria">Categoría *</label>
        <select id="categoria" name="categoria" required>
          <option value="">-- Selecciona --</option>
          <?php
            $opcionesCategoria = ['Asegurado', 'No Asegurado'];
            foreach ($opcionesCategoria as $op) {
                $sel = $categoriaSeleccionada === $op ? 'selected' : '';
                echo "<option value=\"$op\" $sel>$op</option>";
            }
          ?>
        </select>
      </div>
      <div class="campo full">
        <label for="diagnostico">Diagnóstico *</label>
        <input type="text" id="diagnostico" name="diagnostico" required
               value="<?php echo htmlspecialchars($diagnosticoIngresado); ?>">
      </div>
    </div>

    <div class="form-seccion-titulo">Doctor asignado</div>

    <div class="campo full">
      <label for="id_doctor">Doctor *</label>
      <select id="id_doctor" name="id_doctor" required>
        <option value="">-- Selecciona un doctor --</option>
        <?php foreach ($doctores as $doc): ?>
          <option value="<?php echo (int) $doc['id_doctor']; ?>"
            <?php echo $doctorSeleccionado == $doc['id_doctor'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($doc['nombre_doctor']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="acciones">
      <button type="submit" class="btn btn-primario">Guardar paciente</button>
      <button type="reset" class="btn btn-secundario">Limpiar</button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>