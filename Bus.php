<?php
/**
 * bus.php
 * Bus de Web Services — punto de entrada único.
 * Expone los métodos de ServicioPacientes y ServicioDoctores
 * a través de una sola clase fachada (BusService), y registra
 * cada llamada en log_busquedas para el panel de administración.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/ServicioPacientes.php';
require_once __DIR__ . '/clases/ServicioDoctores.php';
require_once __DIR__ . '/clases/ServicioLog.php';
require_once __DIR__ . '/clases/ServicioUsuarios.php';

class BusService
{
    private $pdo;
    private $servicioPacientes;
    private $servicioDoctores;
    private $servicioLog;
    private $servicioUsuarios;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->servicioPacientes = new ServicioPacientes();
        $this->servicioDoctores  = new ServicioDoctores();
        $this->servicioLog       = new ServicioLog();
        $this->servicioUsuarios  = new ServicioUsuarios();
    }

    /**
     * Guarda un registro de la búsqueda/llamada en log_busquedas.
     * Si falla el log, no debe tumbar el servicio.
     */
    private function registrarBusqueda($servicio, $metodo, $parametros = '')
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO log_busquedas (servicio, metodo, parametros)
                 VALUES (:servicio, :metodo, :parametros)"
            );
            $stmt->execute([
                ':servicio'   => $servicio,
                ':metodo'     => $metodo,
                ':parametros' => (string) $parametros,
            ]);
        } catch (PDOException $e) {
            // Intencional: un fallo en el log no debe romper la respuesta del bus.
        }
    }

    // ----- Rutas hacia ServicioPacientes -----

    /**
     * Nota: listarPacientes() NO se registra en log_busquedas.
     * Se llama automáticamente cada vez que alguien visita
     * pacientes.php, así que registrarla ensuciaría el historial
     * con cargas de página en vez de búsquedas reales.
     */
    public function listarPacientes()
    {
        return $this->servicioPacientes->listarPacientes();
    }

    public function agregarPaciente($nombre, $direccion, $categoria, $diagnostico, $idDoctor)
    {
        $this->registrarBusqueda('ServicioPacientes', 'agregarPaciente', $nombre);
        return $this->servicioPacientes->agregarPaciente($nombre, $direccion, $categoria, $diagnostico, $idDoctor);
    }

    /**
     * Motor de búsqueda dinámica: el criterio que escribe el usuario
     * queda registrado tal cual en log_busquedas. Esta es la ÚNICA
     * operación de consulta que se considera una "búsqueda real".
     */
    public function buscarPacientes($criterio)
    {
        $this->registrarBusqueda('ServicioPacientes', 'buscarPacientes', $criterio);
        return $this->servicioPacientes->buscarPacientes($criterio);
    }

    /**
     * Eliminar es una acción que modifica datos, así que sí queda
     * registrada en log_busquedas (igual que agregarPaciente).
     */
    public function eliminarPaciente($idPaciente)
    {
        $this->registrarBusqueda('ServicioPacientes', 'eliminarPaciente', $idPaciente);
        return $this->servicioPacientes->eliminarPaciente($idPaciente);
    }

    // ----- Rutas hacia ServicioDoctores -----

    /**
     * Tampoco se registra: se llama automáticamente al visitar
     * pacientes.php (para mostrar el doctor de cada fila) y
     * agregar.php (para llenar el <select>), no es una búsqueda.
     */
    public function listarDoctores()
    {
        return $this->servicioDoctores->listarDoctores();
    }

    // ----- Rutas hacia ServicioUsuarios (login) -----

    /**
     * Valida usuario/contraseña. NO se registra en log_busquedas a propósito:
     * guardaría la contraseña en texto plano en el historial.
     */
    public function autenticar($username, $password)
    {
        return $this->servicioUsuarios->autenticar($username, $password);
    }

    // ----- Rutas hacia ServicioLog (historial del panel de administración) -----

    /**
     * No se registra a sí misma (evitaría un log infinito de "listar el log").
     */
    public function listarBusquedas()
    {
        return $this->servicioLog->listarBusquedas();
    }

    public function eliminarBusqueda($idLog)
    {
        return $this->servicioLog->eliminarBusqueda($idLog);
    }

    public function eliminarTodasBusquedas()
    {
        return $this->servicioLog->eliminarTodasBusquedas();
    }
}

// ---------------------------------------------------------
// Levanta el servidor SOAP sin WSDL (igual que el ejemplo
// de islavisual.com que dio el profesor)
// ---------------------------------------------------------
ini_set('soap.wsdl_cache_enabled', '0');

$server = new SoapServer(null, [
    'uri' => 'urn:busclinica',
]);

$server->setClass('BusService');
$server->handle();