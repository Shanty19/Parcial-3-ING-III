<?php
session_start();
require_once __DIR__ . '/../config.php';

function normalizarEvidencias($valor) {
    if (is_array($valor)) {
        return array_values(array_filter(array_map('strval', $valor), function ($item) {
            return trim($item) !== '';
        }));
    }

    if (is_string($valor)) {
        $valor = trim($valor);
        if ($valor === '') {
            return [];
        }

        $decoded = json_decode($valor, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), function ($item) {
                return trim($item) !== '';
            }));
        }

        return [$valor];
    }

    return [];
}

function guardarEvidenciasAdjuntas($archivos) {
    $carpeta = __DIR__ . '/../uploads/evidencias';
    if (!is_dir($carpeta) && !mkdir($carpeta, 0777, true) && !is_dir($carpeta)) {
        throw new RuntimeException('No se pudo crear la carpeta de evidencias.');
    }

    if (!isset($archivos['name']) || !is_array($archivos['name'])) {
        return [];
    }

    $guardadas = [];
    foreach ($archivos['name'] as $indice => $nombre) {
        if ($nombre === '') continue;

        $tmp = $archivos['tmp_name'][$indice] ?? '';
        $error = $archivos['error'][$indice] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('La imagen no pudo cargarse correctamente: ' . htmlspecialchars($nombre));
        }
        if (!is_uploaded_file($tmp) || !file_exists($tmp)) {
            throw new RuntimeException('La imagen adjunta no es válida: ' . htmlspecialchars($nombre));
        }

        $tipo = mime_content_type($tmp);
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($tipo, ['image/png', 'image/jpeg'], true) || !in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            throw new RuntimeException('Formato no permitido: ' . htmlspecialchars($nombre) . '. Solo se aceptan PNG, JPG y JPEG.');
        }

        $info = @getimagesize($tmp);
        if ($info === false) {
            throw new RuntimeException('El archivo cargado no es una imagen válida: ' . htmlspecialchars($nombre));
        }

        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($nombre, PATHINFO_FILENAME));
        $base = trim($base, '._');
        if ($base === '') $base = 'evidencia';

        $destino = $carpeta . '/' . $base . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($tmp, $destino)) {
            throw new RuntimeException('No se pudo guardar la imagen: ' . htmlspecialchars($nombre));
        }

        $guardadas[] = 'uploads/evidencias/' . basename($destino);
    }

    return $guardadas;
}

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

    $evidencias = normalizarEvidencias($_POST['evidencias_actuales'] ?? []);

    try {
        $nuevas = guardarEvidenciasAdjuntas($_FILES['evidencias'] ?? []);
        $evidencias = array_values(array_unique(array_merge($evidencias, $nuevas)));
        $evidenciaJson = empty($evidencias) ? '' : json_encode($evidencias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        if (!empty($id)) {
            $sql = "UPDATE qa_plan_pruebas SET nombre_plan=?, proyecto=?, version_sistema=?, responsable=?, fecha_inicio=?, fecha_fin=?, estado=?, objetivo_general=?, alcance=?, fuera_alcance=?, criterios_aceptacion=?, modulos_seleccionados=?, estrategias_seleccionadas=?, entorno_pruebas=?, herramientas=?, requisitos_software=?, criterios_entrada=?, criterios_salida=?, evidencia=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $proyecto, $version, $responsable, $f_inicio, $f_fin, $estado, $obj_gen, $alcance, $fuera_alcance, $criterios_ac, $modulos, $estrategias, $entorno, $herramientas, $req_sw, $criterios_ent, $criterios_sal, $evidenciaJson, $id]);
            $plan_id = $id;
        } else {
            $sql = "INSERT INTO qa_plan_pruebas (codigo_plan, nombre_plan, proyecto, version_sistema, responsable, fecha_elaboracion, fecha_inicio, fecha_fin, estado, objetivo_general, alcance, fuera_alcance, criterios_aceptacion, modulos_seleccionados, estrategias_seleccionadas, entorno_pruebas, herramientas, requisitos_software, criterios_entrada, criterios_salida, evidencia) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $nombre, $proyecto, $version, $responsable, $f_inicio, $f_fin, $estado, $obj_gen, $alcance, $fuera_alcance, $criterios_ac, $modulos, $estrategias, $entorno, $herramientas, $req_sw, $criterios_ent, $criterios_sal, $evidenciaJson]);
            $plan_id = $pdo->lastInsertId();

            $stmt_act = $pdo->prepare("INSERT INTO qa_plan_actividades (plan_id, nombre_actividad, descripcion, responsable, fecha_inicio, fecha_fin, estado_actividad) VALUES (?, 'Configuración de ambiente', 'Preparar herramientas locales', ?, ?, ?, 'Pendiente')");
            $stmt_act->execute([$plan_id, $responsable, $f_inicio, $f_fin]);
        }
    } catch (Throwable $e) {
        header('Location: plan_pruebas_form.php?error=' . rawurlencode($e->getMessage()));
        exit;
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