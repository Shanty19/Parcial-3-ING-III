<?php
session_start();
require_once __DIR__ . '/../config.php';

$action = $_REQUEST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? null;
    $codigo = $_POST['codigo_tabla'] ?? '';
    $nombre = $_POST['nombre_prueba'] ?? '';
    $modulo = $_POST['modulo'] ?? '';
    $objetivo = $_POST['objetivo'] ?? '';
    $precondiciones = $_POST['precondiciones'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if (empty($codigo) || empty($nombre) || empty($modulo) || empty($objetivo)) {
        header('Location: tabla_decision_form.php?error=Campos obligatorios vacios');
        exit;
    }

    if (!empty($id)) {
        // Actualizar maestro
        $sql = "UPDATE qa_tabla_decision SET nombre_prueba=?, modulo=?, objetivo=?, precondiciones=?, observaciones=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $modulo, $objetivo, $precondiciones, $observaciones, $id]);
        $tabla_id = $id;

        // Limpiar reglas anteriores para recrearlas desde el constructor
        $pdo->prepare("DELETE FROM qa_td_reglas_detalle WHERE tabla_id = ?")->execute([$id]);
    } else {
        // Insertar maestro
        $sql = "INSERT INTO qa_tabla_decision (codigo_tabla, nombre_prueba, modulo, objetivo, precondiciones, observaciones) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo, $nombre, $modulo, $objetivo, $precondiciones, $observaciones]);
        $tabla_id = $pdo->lastInsertId();
    }

    // Procesar reglas y condiciones dinámicas del formulario
    $cond_nombres = $_POST['condicion_nombre'] ?? [];
    $reglas_vals = $_POST['regla_val'] ?? [];
    $acciones = $_POST['accion_esperada'] ?? [];

    if (!empty($acciones)) {
        foreach ($acciones as $r_idx => $accion) {
            $regla_num = $r_idx + 1;
            $conds_map = [];

            foreach ($cond_nombres as $c_idx => $c_name) {
                $val = $reglas_vals[$r_idx][$c_idx] ?? 'Sí';
                $conds_map[$c_name] = $val;
            }

            $json_conds = json_encode($conds_map);

            $stmt_r = $pdo->prepare("INSERT INTO qa_td_reglas_detalle (tabla_id, regla_num, condiciones_json, accion_esperada, resultado_obtenido, estado_regla) VALUES (?, ?, ?, ?, 'Por evaluar', 'Pendiente')");
            $stmt_r->execute([$tabla_id, $regla_num, $json_conds, $accion]);
        }
    }

    header('Location: tabla_decision.php?success=1');
    exit;
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM qa_tabla_decision WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: tabla_decision.php?deleted=1');
    exit;
}

header('Location: tabla_decision.php');
exit;