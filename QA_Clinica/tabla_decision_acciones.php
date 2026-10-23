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
    if (!is_dir($carpeta)) {
        if (!mkdir($carpeta, 0777, true) && !is_dir($carpeta)) {
            throw new RuntimeException('No se pudo crear la carpeta de evidencias.');
        }
    }

    if (!isset($archivos['name']) || !is_array($archivos['name'])) {
        return [];
    }

    $archivosGuardados = [];
    foreach ($archivos['name'] as $indice => $nombre) {
        if ($nombre === '') {
            continue;
        }

        $tmp = $archivos['tmp_name'][$indice] ?? '';
        $error = $archivos['error'][$indice] ?? UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Hubo un error al cargar una de las imágenes: ' . htmlspecialchars($nombre));
        }

        if (!is_uploaded_file($tmp) || !file_exists($tmp)) {
            throw new RuntimeException('La imagen adjunta no es válida: ' . htmlspecialchars($nombre));
        }

        $tipo = mime_content_type($tmp);
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $permitidos = ['image/png', 'image/jpeg'];

        if (!in_array($tipo, $permitidos, true) || !in_array($extension, ['png', 'jpg', 'jpeg'], true)) {
            throw new RuntimeException('Formato no permitido: ' . htmlspecialchars($nombre) . '. Solo se aceptan PNG, JPG y JPEG.');
        }

        $info = @getimagesize($tmp);
        if ($info === false) {
            throw new RuntimeException('El archivo cargado no corresponde a una imagen válida: ' . htmlspecialchars($nombre));
        }

        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($nombre, PATHINFO_FILENAME));
        $base = trim($base, '._');
        if ($base === '') {
            $base = 'evidencia';
        }

        $destino = $carpeta . '/' . $base . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $destino)) {
            throw new RuntimeException('No se pudo guardar la imagen: ' . htmlspecialchars($nombre));
        }

        $archivosGuardados[] = 'uploads/evidencias/' . basename($destino);
    }

    return $archivosGuardados;
}

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

    $evidencias = normalizarEvidencias($_POST['evidencias_actuales'] ?? []);

    try {
        $nuevas = guardarEvidenciasAdjuntas($_FILES['evidencias'] ?? []);
        $evidencias = array_values(array_unique(array_merge($evidencias, $nuevas)));
        $evidenciaJson = empty($evidencias) ? '' : json_encode($evidencias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        if (!empty($id)) {
            $sql = "UPDATE qa_tabla_decision SET nombre_prueba=?, modulo=?, objetivo=?, precondiciones=?, observaciones=?, evidencia=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $modulo, $objetivo, $precondiciones, $observaciones, $evidenciaJson, $id]);
            $tabla_id = $id;

            $pdo->prepare("DELETE FROM qa_td_reglas_detalle WHERE tabla_id = ?")->execute([$id]);
        } else {
            $sql = "INSERT INTO qa_tabla_decision (codigo_tabla, nombre_prueba, modulo, objetivo, precondiciones, observaciones, evidencia) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $nombre, $modulo, $objetivo, $precondiciones, $observaciones, $evidenciaJson]);
            $tabla_id = $pdo->lastInsertId();
        }

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
    } catch (Throwable $e) {
        $redirect = 'tabla_decision_form.php';
        if (!empty($id)) {
            $redirect .= '?id=' . urlencode((string) $id);
        }
        $redirect .= '&error=' . rawurlencode($e->getMessage());
        header('Location: ' . $redirect);
        exit;
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