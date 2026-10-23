<?php
session_start();
require_once __DIR__ . '/../config.php';

$action = $_REQUEST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? null;
    $codigo = $_POST['codigo_plan'] ?? '';
    $nombre = $_POST['nombre_plan'] ?? '';
    $proyecto = $_POST['proyecto'] ?? 'Clínica Salud+';
    $version = $_POST['version_sistema'] ?? 'v1.0';
    $responsable = $_POST['responsable'] ?? '';
    $f_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
    $f_fin = $_POST['fecha_fin'] ?? date('Y-m-d');
    $estado = $_POST['estado'] ?? 'En preparación';
    $obj_gen = $_POST['objetivo_general'] ?? '';
    $alcance = $_POST['alcance'] ?? '';
    $fuera_alcance = $_POST['fuera_alcance'] ?? '';
    $criterios_ac = $_POST['criterios_aceptacion'] ?? '';
    
    $modulos = isset($_POST['modulos']) ? implode(',', $_POST['modulos']) : '';
    $estrategias = isset($_POST['estrategias']) ? implode(',', $_POST['estrategias']) : '';

    $entorno = $_POST['entorno_pruebas'] ?? '';
    $herramientas = $_POST['herramientas'] ?? '';
    $req_sw = $_POST['requisitos_software'] ?? '';
    $criterios_ent = $_POST['criterios_entrada'] ?? '';
    $criterios_sal = $_POST['criterios_salida'] ?? '';

    if (empty($codigo) || empty($nombre) || empty($responsable) || empty($obj_gen)) {
        header('Location: plan_pruebas_form.php?error=Campos obligatorios vacios');
        exit;
    }

    if (!empty($id)) {
        $sql = "UPDATE qa_plan_pruebas SET nombre_plan=?, proyecto=?, version_sistema=?, responsable=?, fecha_inicio=?, fecha_fin=?, estado=?, objetivo_general=?, alcance=?, fuera_alcance=?, criterios_aceptacion=?, modulos_seleccionados=?, estrategias_seleccionadas=?, entorno_pruebas=?, herramientas=?, requisitos_software=?, criterios_entrada=?, criterios_salida=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $proyecto, $version, $responsable, $f_inicio, $f_fin, $estado, $obj_gen, $alcance, $fuera_alcance, $criterios_ac, $modulos, $estrategias, $entorno, $herramientas, $req_sw, $criterios_ent, $criterios_sal, $id]);
        $plan_id = $id;
    } else {
        $sql = "INSERT INTO qa_plan_pruebas (codigo_plan, nombre_plan, proyecto, version_sistema, responsable, fecha_elaboracion, fecha_inicio, fecha_fin, estado, objetivo_general, alcance, fuera_alcance, criterios_aceptacion, modulos_seleccionados, estrategias_seleccionadas, entorno_pruebas, herramientas, requisitos_software, criterios_entrada, criterios_salida) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo, $nombre, $proyecto, $version, $responsable, $f_inicio, $f_fin, $estado, $obj_gen, $alcance, $fuera_alcance, $criterios_ac, $modulos, $estrategias, $entorno, $herramientas, $req_sw, $criterios_ent, $criterios_sal]);
        $plan_id = $pdo->lastInsertId();

        // Insertar actividades iniciales por defecto al crear el plan maestro
        $stmt_act = $pdo->prepare("INSERT INTO qa_plan_actividades (plan_id, nombre_actividad, descripcion, responsable, fecha_inicio, fecha_fin, estado_actividad) VALUES (?, 'Configuración de ambiente', 'Preparar herramientas locales', ?, ?, ?, 'Pendiente')");
        $stmt_act->execute([$plan_id, $responsable, $f_inicio, $f_fin]);
    }

    header('Location: plan_pruebas.php?success=1');
    exit;
} elseif ($action == 'delete') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $pdo->prepare("DELETE FROM qa_plan_pruebas WHERE id = ?")->execute([$id]);
    }
    header('Location: plan_pruebas.php?deleted=1');
    exit;
}

header('Location: plan_pruebas.php');
exit;