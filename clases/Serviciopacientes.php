<?php
/**
 * ServicioPacientes.php
 * Clase de servicio SOAP para la entidad Paciente.
 * Cada método público queda expuesto como operación del servicio
 * cuando el bus la registre con SoapServer.
 */

require_once __DIR__ . '/../config.php';

class ServicioPacientes
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Devuelve todos los pacientes junto con el nombre de su doctor.
     * @return array Lista de pacientes (cada uno como array asociativo)
     */
    public function listarPacientes()
    {
        $sql = "SELECT p.id_paciente,
                       p.nombre_paciente,
                       p.direccion,
                       p.categoria,
                       p.diagnostico,
                       d.nombre_doctor
                FROM paciente p
                INNER JOIN doctor d ON p.id_doctor = d.id_doctor
                ORDER BY p.nombre_paciente ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo paciente.
     * @return string Mensaje de éxito o error
     */
    public function agregarPaciente($nombre, $direccion, $categoria, $diagnostico, $idDoctor)
    {
        $nombre     = trim((string) $nombre);
        $direccion  = trim((string) $direccion);
        $categoria  = trim((string) $categoria);
        $diagnostico = trim((string) $diagnostico);
        $idDoctor   = (int) $idDoctor;

        if ($nombre === '' || $direccion === '' || $categoria === '' || $diagnostico === '' || $idDoctor <= 0) {
            return "Error: todos los campos son obligatorios y el doctor debe ser válido.";
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO paciente (nombre_paciente, direccion, categoria, diagnostico, id_doctor)
                 VALUES (:nombre_paciente, :direccion, :categoria, :diagnostico, :id_doctor)"
            );
            $stmt->execute([
                ':nombre_paciente' => $nombre,
                ':direccion'       => $direccion,
                ':categoria'       => $categoria,
                ':diagnostico'     => $diagnostico,
                ':id_doctor'       => $idDoctor,
            ]);

            return "Paciente registrado correctamente.";
        } catch (PDOException $e) {
            return "Error al guardar: " . $e->getMessage();
        }
    }

    /**
     * Motor de búsqueda dinámica de pacientes.
     * Busca coincidencias parciales en nombre, dirección, categoría,
     * diagnóstico o nombre del doctor.
     * @return array Lista de pacientes que coinciden con el criterio
     */
    public function buscarPacientes($criterio)
    {
        $criterio = trim((string) $criterio);

        if ($criterio === '') {
            // Sin criterio, se comporta igual que listarPacientes()
            return $this->listarPacientes();
        }

        $sql = "SELECT p.id_paciente,
                       p.nombre_paciente,
                       p.direccion,
                       p.categoria,
                       p.diagnostico,
                       d.nombre_doctor
                FROM paciente p
                INNER JOIN doctor d ON p.id_doctor = d.id_doctor
                WHERE p.nombre_paciente LIKE :criterio
                   OR p.direccion      LIKE :criterio
                   OR p.categoria      LIKE :criterio
                   OR p.diagnostico    LIKE :criterio
                   OR d.nombre_doctor  LIKE :criterio
                ORDER BY p.nombre_paciente ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':criterio' => '%' . $criterio . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Elimina un paciente por su ID.
     * @return string Mensaje de éxito o error
     */
    public function eliminarPaciente($idPaciente)
    {
        $idPaciente = (int) $idPaciente;

        if ($idPaciente <= 0) {
            return "Error: id de paciente inválido.";
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM paciente WHERE id_paciente = :id");
            $stmt->execute([':id' => $idPaciente]);

            if ($stmt->rowCount() === 0) {
                return "Error: no se encontró ningún paciente con ese id.";
            }

            return "Paciente eliminado correctamente.";
        } catch (PDOException $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }
}