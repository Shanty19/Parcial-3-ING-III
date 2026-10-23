<?php
session_start();
require_once __DIR__ . '/../config.php';

$action = $_REQUEST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? null;
    $codigo = $_POST['codigo'] ?? '';
    $nombre = $_POST['nombre_caso'] ?? '';
    $modulo = $_POST['modulo'] ?? '';
    $prioridad = $_POST['prioridad'] ?? 'Media';
    $estado = $_POST['estado'] ?? 'Pendiente';
    $objetivo = $_POST['objetivo'] ?? '';
    $precondiciones = $_POST['precondiciones'] ?? '';
    $datos_prueba = $_POST['datos_prueba'] ?? '';
    $pasos = $_POST['pasos'] ?? '';
    $resultado = $_POST['resultado_esperado'] ?? '';
    $fecha = date('Y-m-d');

    if (empty($codigo) || empty($nombre) || empty($modulo) || empty($objetivo) || empty($pasos) || empty($resultado)) {
        header('Location: casos_prueba.php?error=Campos obligatorios vacios');
        exit;
    }

    if (!empty($id)) {
        // Actualizar caso existente
        $sql = "UPDATE qa_casos_prueba SET nombre_caso=?, modulo=?, prioridad=?, estado=?, objetivo=?, precondiciones=?, datos_prueba=?, pasos=?, resultado_esperado=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $modulo, $prioridad, $estado, $objetivo, $precondiciones, $datos_prueba, $pasos, $resultado, $id]);
    } else {
        // Insertar nuevo caso
        $sql = "INSERT INTO qa_casos_prueba (codigo, nombre_caso, modulo, prioridad, estado, objetivo, precondiciones, datos_prueba, pasos, resultado_esperado, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo, $nombre, $modulo, $prioridad, $estado, $objetivo, $precondiciones, $datos_prueba, $pasos, $resultado, $fecha]);
    }

    header('Location: casos_prueba.php?success=1');
    exit;
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM qa_casos_prueba WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: casos_prueba.php?deleted=1');
    exit;
}

header('Location: casos_prueba.php');
exit;