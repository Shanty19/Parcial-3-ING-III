<?php
require_once __DIR__ . '/includes/auth.php';

$titulo_pestana = "Iniciar sesión";

require_once __DIR__ . '/config.php';   // define BUS_URL y BUS_URI
$BUS_URL = BUS_URL;
$BUS_URI = BUS_URI;

// Si ya hay sesión, no tiene sentido mostrar el login.
if ($u = usuario_actual()) {
    // El Tester (Testing) va a su panel de QA; los demás conservan su destino actual.
    header('Location: ' . (in_array($u['rol'], ['personal', 'tester'], true) ? 'QA_Clinica/dashboard.php' : 'pacientes.php'));
    exit;
}

$error = '';
$usernameIngresado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameIngresado = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $error = 'La sesión del formulario expiró. Intenta de nuevo.';
    } elseif ($usernameIngresado === '' || $password === '') {
        $error = 'Escribe tu usuario y tu contraseña.';
    } else {
        try {
            ini_set('default_socket_timeout', 5);
            $cliente = new SoapClient(null, [
                'location'   => $BUS_URL,
                'uri'        => $BUS_URI,
                'exceptions' => true,
            ]);
            $r = $cliente->autenticar($usernameIngresado, $password);

            if (!empty($r['ok'])) {
                // Si venía de una página protegida, vuelve a ella.
                // Por defecto entra a Pacientes; si venía de otra página protegida, vuelve a esa.
                $destino = $_SESSION['volver_a'] ?? 'pacientes.php';   // iniciar_sesion_usuario() lo limpia
                iniciar_sesion_usuario($r);
                registrar_login_exitoso($_SESSION['usuario']);

                // Redirección por rol: el Tester (Testing) entra a su dashboard de QA.
                // El Administrador conserva exactamente el destino de siempre (volver_a o pacientes.php).
                if (in_array($r['rol'], ['personal', 'tester'], true)) {
                    $destino = 'QA_Clinica/dashboard.php';
                }

                $destinoValido = $destino === 'QA_Clinica/dashboard.php'
                    || preg_match('/^[A-Za-z0-9_]+\.php$/', $destino);

                header('Location: ' . ($destinoValido ? $destino : 'pacientes.php'));
                exit;
            }
            $error = $r['mensaje'] ?? 'Usuario o contraseña incorrectos.';
        } catch (SoapFault $fault) {
            $error = 'No se pudo contactar el servicio de autenticación. Intenta más tarde.';
        }
    }
}

// Mensajes informativos (vienen de auth.php / logout.php)
$aviso = '';
$m = $_GET['m'] ?? '';
if ($m === 'expirada')      $aviso = 'Tu sesión se cerró por inactividad. Inicia sesión de nuevo.';
elseif ($m === 'requerido') $aviso = 'Inicia sesión para continuar.';
elseif ($m === 'salio')     $aviso = 'Cerraste sesión correctamente.';

include 'includes/header.php';
?>

<div class="login-wrap">
  <div class="form-card login-card">
    <h1 class="titulo-pagina">Iniciar sesión</h1>
    <p class="subtitulo-pagina">Accede al sistema de gestión de Clínica Salud+.</p>

    <?php if ($error): ?>
      <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($aviso): ?>
      <div class="alerta <?php echo $m === 'salio' ? 'alerta-exito' : 'alerta-info'; ?>">
        <?php echo htmlspecialchars($aviso); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="on">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">

      <div class="campo" style="margin-bottom: 18px;">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username" maxlength="50"
               value="<?php echo htmlspecialchars($usernameIngresado); ?>"
               autocomplete="username" autofocus required>
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" maxlength="200"
               autocomplete="current-password" required>
      </div>
      
      <div class="acciones">
        <button type="submit" class="btn btn-primario">Entrar</button>
      </div>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
