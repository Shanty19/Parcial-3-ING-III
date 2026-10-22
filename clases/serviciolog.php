<?php
/**
 * ServicioLog.php
 * Clase de servicio SOAP para la tabla log_busquedas.
 * Antes, admin.php manipulaba esta tabla directamente con PDO;
 * ahora pasa por el bus, igual que ServicioPacientes y ServicioDoctores.
 */

require_once __DIR__ . '/../config.php';

class ServicioLog
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Devuelve el historial completo de búsquedas, más reciente primero.
     * @return array
     */
    public function listarBusquedas()
    {
        $stmt = $this->pdo->query("SELECT * FROM log_busquedas ORDER BY fecha_hora DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Elimina un registro puntual del historial.
     * @return string Mensaje de éxito o error
     */
    public function eliminarBusqueda($idLog)
    {
        $idLog = (int) $idLog;

        if ($idLog <= 0) {
            return "Error: id de registro inválido.";
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM log_busquedas WHERE id_log = :id");
            $stmt->execute([':id' => $idLog]);

            if ($stmt->rowCount() === 0) {
                return "Error: no se encontró el registro #{$idLog}.";
            }

            return "Registro #{$idLog} eliminado.";
        } catch (PDOException $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }

    /**
     * Vacía por completo el historial de búsquedas.
     * @return string Mensaje de éxito o error
     */
    public function eliminarTodasBusquedas()
    {
        try {
            $this->pdo->exec("TRUNCATE TABLE log_busquedas");
            return "Se borraron todas las búsquedas registradas.";
        } catch (PDOException $e) {
            return "Error al borrar: " . $e->getMessage();
        }
    }
}