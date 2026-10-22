<?php
/**
 * ServicioUsuarios.php
 * Clase de servicio SOAP para autenticación de usuarios (login).
 *
 * Igual que ServicioPacientes / ServicioDoctores / ServicioLog, las páginas
 * no tocan MySQL directamente: login.php llama al bus y el bus llega aquí.
 *
 * Importante: este servicio NUNCA devuelve el hash de la contraseña, y el bus
 * NO registra autenticar() en log_busquedas (para no guardar contraseñas).
 */

require_once __DIR__ . '/../config.php';

class ServicioUsuarios
{
    // Después de este número de contraseñas incorrectas seguidas, la cuenta
    // se bloquea temporalmente (frena ataques de fuerza bruta).
    const MAX_INTENTOS      = 5;
    const MINUTOS_BLOQUEO   = 5;

    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Valida usuario y contraseña.
     *
     * @return array ['ok'=>bool, 'id'=>int, 'username'=>string, 'rol'=>string, 'mensaje'=>string]
     */
    public function autenticar($username, $password)
    {
        $fallo = [
            'ok' => false, 'id' => 0, 'username' => '', 'rol' => '',
            // Mensaje genérico a propósito: no revela si el usuario existe.
            'mensaje' => 'Usuario o contraseña incorrectos.',
        ];

        $username = trim((string) $username);
        $password = (string) $password;

        if ($username === '' || $password === '' || strlen($username) > 50 || strlen($password) > 200) {
            return $fallo;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT id_usuario, username, password_hash, rol, intentos_fallidos,
                        (bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW()) AS bloqueado
                 FROM usuarios
                 WHERE username = :u
                 LIMIT 1"
            );
            $stmt->execute([':u' => $username]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$u) {
                // Gasta el mismo tiempo que una verificación real, para que no se
                // pueda adivinar qué usuarios existen midiendo la demora.
                password_verify($password, password_hash('relleno', PASSWORD_DEFAULT));
                return $fallo;
            }

            if ((int) $u['bloqueado'] === 1) {
                $fallo['mensaje'] = 'Cuenta bloqueada temporalmente por demasiados intentos fallidos. '
                                  . 'Intenta de nuevo en unos minutos.';
                return $fallo;
            }

            if (!password_verify($password, $u['password_hash'])) {
                $this->registrarFallo((int) $u['id_usuario'], (int) $u['intentos_fallidos']);
                return $fallo;
            }

            // --- Login correcto ---
            $this->pdo->prepare(
                "UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = :id"
            )->execute([':id' => $u['id_usuario']]);

            // Si PHP actualizó su algoritmo por defecto, re-guarda el hash con el nuevo.
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                $this->pdo->prepare("UPDATE usuarios SET password_hash = :h WHERE id_usuario = :id")
                    ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $u['id_usuario']]);
            }

            return [
                'ok'       => true,
                'id'       => (int) $u['id_usuario'],
                'username' => $u['username'],
                'rol'      => $u['rol'],
                'mensaje'  => '',
            ];
        } catch (PDOException $e) {
            $fallo['mensaje'] = 'Error interno al validar el usuario.';
            return $fallo;
        }
    }

    private function registrarFallo($idUsuario, $intentosPrevios)
    {
        $nuevos = $intentosPrevios + 1;

        if ($nuevos >= self::MAX_INTENTOS) {
            $this->pdo->prepare(
                "UPDATE usuarios
                 SET intentos_fallidos = 0,
                     bloqueado_hasta = DATE_ADD(NOW(), INTERVAL " . (int) self::MINUTOS_BLOQUEO . " MINUTE)
                 WHERE id_usuario = :id"
            )->execute([':id' => $idUsuario]);
        } else {
            $this->pdo->prepare("UPDATE usuarios SET intentos_fallidos = :n WHERE id_usuario = :id")
                ->execute([':n' => $nuevos, ':id' => $idUsuario]);
        }
    }
}
