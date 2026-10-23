<?php
session_start();
require_once __DIR__ . '/../config.php';

$action = $_REQUEST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? null;
    $codigo = $_POST['codigo_evaluacion'] ?? '';
    $tipo = $_POST['tipo_evaluacion'] ?? 'Autoevaluación';
    $evaluador = $_POST['evaluador'] ?? 'Tester QA';
    $evaluado = $_POST['integrante_evaluado'] ?? $evaluador;
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $estado = $_POST['estado'] ?? 'Borrador';

    // Recoger criterios y calcular promedio general
    $notas = $_POST['criterio_nota'] ?? [];
    $comentarios = $_POST['criterio_comentario'] ?? [];
    $criterios_map = [];
    $suma = 0;
    $count = 0;

    foreach ($notas as $crit => $val) {
        $nota_int = intval($val);
        $criterios_map[$crit] = [
            'nota' => $nota_int,
            'comentario' => $comentarios[$crit] ?? ''
        ];
        $suma += $nota_int;
        $count++;
    }

    $promedio = $count > 0 ? ($suma / $count) : 0.00;
    $json_criterios = json_encode($criterios_map);

    // Recoger preguntas reflexivas
    $preguntas_map = [
        'auto_1' => $_POST['pregunta_1'] ?? '',
        'auto_2' => $_POST['pregunta_2'] ?? '',
        'auto_3' => $_POST['pregunta_3'] ?? ''
    ];
    $json_preguntas = json_encode($preguntas_map);

    if (empty($codigo) || empty($evaluador) || empty($evaluado)) {
        header('Location: evaluacion_formativa_form.php?error=Campos obligatorios vacios');
        exit;
    }

    if (!empty($id)) {
        $sql = "UPDATE qa_evaluacion_formativa SET tipo_evaluacion=?, integrante_evaluado=?, fecha=?, estado=?, promedio_general=?, criterios_json=?, preguntas_json=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tipo, $evaluado, $fecha, $estado, $promedio, $json_criterios, $json_preguntas, $id]);
    } else {
        $sql = "INSERT INTO qa_evaluacion_formativa (codigo_evaluacion, tipo_evaluacion, proyecto, evaluador, integrante_evaluado, fecha, estado, promedio_general, criterios_json, preguntas_json) VALUES (?, ?, 'Clínica Salud+', ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo, $tipo, $evaluador, $evaluado, $fecha, $estado, $promedio, $json_criterios, $json_preguntas]);
    }

    header('Location: evaluacion_formativa.php?success=1');
    exit;
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $pdo->prepare("DELETE FROM qa_evaluacion_formativa WHERE id = ?")->execute([$id]);
    }
    header('Location: evaluacion_formativa.php?deleted=1');
    exit;
}

header('Location: evaluacion_formativa.php');
exit;