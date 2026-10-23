<?php
session_start();
require_once(__DIR__ . '/../config.php');

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

        $rutaPublica = 'uploads/evidencias/' . basename($destino);
        $archivosGuardados[] = $rutaPublica;
    }

    return $archivosGuardados;
}

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

    if (empty($codigo) || empty($modulo) || empty($campo) || empty($objetivo) || empty($res_esp)) {
        header('Location: analisis_valor_limite_form.php?error=Campos obligatorios vacios');
        exit;
    }

    $evidencias = normalizarEvidencias($_POST['evidencias_actuales'] ?? []);

    try {
        $nuevas = guardarEvidenciasAdjuntas($_FILES['evidencias'] ?? []);
        $evidencias = array_values(array_unique(array_merge($evidencias, $nuevas)));
        $evidenciaJson = empty($evidencias) ? '' : json_encode($evidencias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        if (!empty($id)) {
            $sql = "UPDATE qa_analisis_valor_limite SET modulo=?, campo_evaluado=?, tipo_dato=?, valor_minimo=?, valor_maximo=?, unidad_longitud=?, objetivo=?, precondiciones=?, datos_entrada=?, resultado_esperado=?, resultado_obtenido=?, estado=?, observaciones=?, evidencia=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$modulo, $campo, $tipo, $min, $max, $unidad, $objetivo, $precond, $datos, $res_esp, $res_obt, $estado, $obs, $evidenciaJson, $id]);
        } else {
            $sql = "INSERT INTO qa_analisis_valor_limite (codigo_analisis, modulo, campo_evaluado, tipo_dato, valor_minimo, valor_maximo, unidad_longitud, objetivo, precondiciones, datos_entrada, resultado_esperado, resultado_obtenido, estado, observaciones, evidencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $modulo, $campo, $tipo, $min, $max, $unidad, $objetivo, $precond, $datos, $res_esp, $res_obt, $estado, $obs, $evidenciaJson]);
        }
    } catch (Throwable $e) {
        $redirect = 'analisis_valor_limite_form.php';
        if (!empty($id)) {
            $redirect .= '?id=' . urlencode((string) $id);
        }
        $redirect .= '&error=' . rawurlencode($e->getMessage());
        header('Location: ' . $redirect);
        exit;
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