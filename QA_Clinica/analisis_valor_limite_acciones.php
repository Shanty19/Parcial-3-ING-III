<?php
session_start();
require_once(__DIR__ . '/../config.php');

$action = $_REQUEST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? null;
    $codigo = $_POST['codigo_analisis'] ?? '';
    $modulo = $_POST['modulo'] ?? '';
    $campo = $_POST['campo_evaluado'] ?? '';
    $tipo = $_POST['tipo_dato'] ?? '';
    $min = $_POST['valor_minimo'] ?? null;
    $max = $_POST['valor_maximo'] ?? null;
    $unidad = $_POST['unidad_longitud'] ?? '';
    $objetivo = $_POST['objetivo'] ?? '';
    $precond = $_POST['precondiciones'] ?? '';
    $datos = $_POST['datos_entrada'] ?? '';
    $res_esp = $_POST['resultado_esperado'] ?? '';
    $res_obt = $_POST['resultado_obtenido'] ?? '';
    $estado = $_POST['estado'] ?? 'Pendiente';
    $obs = $_POST['observaciones'] ?? '';
    $evidencia = $_POST['evidencia'] ?? '';

    if (empty($codigo) || empty($modulo) || empty($campo) || empty($objetivo) || empty($res_esp)) {
        header('Location: analisis_valor_limite_form.php?error=Campos obligatorios vacios');
        exit;
    }

    if (!empty($id)) {
        // Actualizar
        $sql = "UPDATE qa_analisis_valor_limite SET modulo=?, campo_evaluado=?, tipo_dato=?, valor_minimo=?, valor_maximo=?, unidad_longitud=?, objetivo=?, precondiciones=?, datos_entrada=?, resultado_esperado=?, resultado_obtenido=?, estado=?, observaciones=?, evidencia=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$modulo, $campo, $tipo, $min, $max, $unidad, $objetivo, $precond, $datos, $res_esp, $res_obt, $estado, $obs, $evidencia, $id]);
    } else {
        // Insertar
        $sql = "INSERT INTO qa_analisis_valor_limite (codigo_analisis, modulo, campo_evaluado, tipo_dato, valor_minimo, valor_maximo, unidad_longitud, objetivo, precondiciones, datos_entrada, resultado_esperado, resultado_obtenido, estado, observaciones, evidencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo, $modulo, $campo, $tipo, $min, $max, $unidad, $objetivo, $precond, $datos, $res_esp, $res_obt, $estado, $obs, $evidencia]);
    }

    header('Location: analisis_valor_limite.php?success=1');
    exit;
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM qa_analisis_valor_limite WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: analisis_valor_limite.php?deleted=1');
    exit;
}

header('Location: analisis_valor_limite.php');
exit;