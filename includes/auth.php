<?php
/**
 * includes/auth.php
 * Manejo de sesión y control de acceso.
 *
 * Uso en una página protegida (poner al INICIO del archivo, antes de
 * cualquier acción como eliminar o consultar el bus):
 *
 *     require_once __DIR__ . '/includes/auth.php';
 *     requerir_login();                 // cualquier usuario autenticado
 *     requerir_rol(['admin']);          // solo ciertos roles
 */

// Minutos de inactividad antes de cerrar la sesión automáticamente.
const SESION_INACTIVIDAD_MIN = 30;

if (session_status() === PHP_SESSION_NONE) {
    session_name('clinica_sid');
    session_set_cookie_params([
        'lifetime' => 0,                                   // se borra al cerrar el navegador
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),           // solo por HTTPS cuando haya HTTPS
        'httponly' => true,                                // JavaScript no puede leer la cookie
        'samesite' => 'Lax',                               // no se envía en POST desde otros sitios
    ]);
    session_start();
}

/** Devuelve ['id','username','rol'] del usuario con sesión activa, o null. */
function usuario_actual()
{
    if (empty($_SESSION['usuario'])) {
        return null;
    }

    // Cierre por inactividad
    $ahora = time();
    if (isset($_SESSION['ultimo_acceso']) && ($ahora - $_SESSION['ultimo_acceso']) > SESION_INACTIVIDAD_MIN * 60) {
        if (!empty($_SESSION['usuario'])) {
            registrar_salida_login();
        }
        // Se limpia la sesión pero se conserva la sesión PHP viva (con ID nuevo),
        // para poder avisarle al usuario por qué lo mandamos al login.
        $_SESSION = ['sesion_expirada' => true];
        session_regenerate_id(true);
        return null;
    }
    $_SESSION['ultimo_acceso'] = $ahora;

    return $_SESSION['usuario'];
}

/** Guarda al usuario en la sesión después de un login correcto. */
function iniciar_sesion_usuario(array $u)
{
    session_regenerate_id(true); // evita "session fixation"
    unset($_SESSION['sesion_expirada'], $_SESSION['volver_a']);
    $_SESSION['usuario'] = [
        'id'       => (int) $u['id'],
        'username' => (string) $u['username'],
        'rol'      => (string) $u['rol'],
    ];
    $_SESSION['ultimo_acceso'] = time();
}

/** Registra un login exitoso sin guardar secretos ni repetir el mismo evento en la misma sesión. */
function registrar_login_exitoso(array $u)
{
    if (empty($u['id']) && empty($u['username']) && empty($u['rol'])) {
        return false;
    }

    $usuarioId = (int) ($u['id'] ?? 0);
    $nombreUsuario = trim((string) ($u['username'] ?? ''));
    $rol = trim((string) ($u['rol'] ?? ''));

    if ($usuarioId <= 0 || $nombreUsuario === '' || $rol === '') {
        return false;
    }

    $clave = $usuarioId . '|' . $nombreUsuario . '|' . $rol;
    if (!empty($_SESSION['login_log_registrado']) && $_SESSION['login_log_registrado'] === $clave) {
        return true;
    }

    try {
        require_once __DIR__ . '/../config.php';
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO login_logs (usuario_id, nombre_usuario, rol, fecha_hora) VALUES (:usuario_id, :nombre_usuario, :rol, NOW())'
        );
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':nombre_usuario' => $nombreUsuario,
            ':rol' => $rol,
        ]);

        $_SESSION['login_log_registrado'] = $clave;
        $_SESSION['login_log_id'] = (int) $pdo->lastInsertId();
        return true;
    } catch (Throwable $e) {
        error_log('Error al registrar login_logs: ' . $e->getMessage());
        return false;
    }
}

/** Actualiza la salida del mismo registro de inicio de sesión asociado a esta sesión. */
function registrar_salida_login()
{
    if (empty($_SESSION['usuario']) || empty($_SESSION['login_log_id'])) {
        return false;
    }

    $usuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);
    $loginLogId = (int) $_SESSION['login_log_id'];
    if ($usuarioId <= 0 || $loginLogId <= 0) {
        return false;
    }

    try {
        require_once __DIR__ . '/../config.php';
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare(
            'UPDATE login_logs SET fecha_hora_salida = NOW() WHERE id = :id AND usuario_id = :usuario_id AND fecha_hora_salida IS NULL LIMIT 1'
        );
        $stmt->execute([
            ':id' => $loginLogId,
            ':usuario_id' => $usuarioId,
        ]);

        unset($_SESSION['login_log_id'], $_SESSION['login_log_registrado']);
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        error_log('Error al registrar salida en login_logs: ' . $e->getMessage());
        return false;
    }
}

function cerrar_sesion()
{
    if (!empty($_SESSION['usuario'])) {
        registrar_salida_login();
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Exige un usuario autenticado. Si no lo hay, manda a login.php
 * (o responde 401 en JSON cuando $json = true, para los endpoints AJAX).
 */
function requerir_login($json = false)
{
    $u = usuario_actual();
    if ($u) {
        return $u;
    }

    if ($json) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'mensaje' => 'Tu sesión expiró. Vuelve a iniciar sesión.']);
        exit;
    }

    // Recuerda a qué página quería entrar (solo el nombre del archivo, SIN parámetros:
    // así, por ejemplo, un "?eliminar=5" nunca se repite solo después del login).
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $archivo = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
        if (preg_match('/^[A-Za-z0-9_]+\.php$/', $archivo) && strtolower($archivo) !== 'login.php') {
            $_SESSION['volver_a'] = $archivo;
        }
    }

    header('Location: login.php' . (!empty($_SESSION['sesion_expirada']) ? '?m=expirada' : '?m=requerido'));
    exit;
}

/** Exige que el usuario tenga uno de los roles indicados; si no, responde 403. */
function requerir_rol(array $rolesPermitidos, $json = false)
{
    $u = requerir_login($json);
    if (in_array($u['rol'], $rolesPermitidos, true)) {
        return $u;
    }

    http_response_code(403);
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para realizar esta acción.']);
        exit;
    }

    $titulo_pestana = 'Acceso denegado';
    include __DIR__ . '/header.php';
    echo '<h1 class="titulo-pagina">Acceso denegado</h1>';
    echo '<p class="subtitulo-pagina">Tu usuario no tiene permiso para ver esta sección.</p>';
    echo '<a href="index.php" class="btn btn-primario">Volver al inicio</a>';
    include __DIR__ . '/footer.php';
    exit;
}

// ---------- Token CSRF (para el formulario de login) ----------

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido($token)
{
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}
