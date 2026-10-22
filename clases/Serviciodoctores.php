<?php
/**
 * ServicioDoctores.php
 * Clase de servicio SOAP para la entidad Doctor.
 */

require_once __DIR__ . '/../config.php';

class ServicioDoctores
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Devuelve todos los doctores registrados.
     * @return array Lista de doctores (cada uno como array asociativo)
     */
    public function listarDoctores()
    {
        $stmt = $this->pdo->query(
            "SELECT id_doctor, nombre_doctor, especialidad
             FROM doctor
             ORDER BY nombre_doctor ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}