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
    $tecnica = $_POST['tecnica_utilizada'] ?? '';
    $fecha = date('Y-m-d');

    $tecnicas_validas = ['Caja negra', 'Caja blanca'];
    if (!in_array($tecnica, $tecnicas_validas, true)) {
        header('Location: casos_prueba.php?error=Técnica inválida');
        exit;
    }

    if (empty($codigo) || empty($nombre) || empty($modulo) || empty($objetivo) || empty($pasos) || empty($resultado)) {
        header('Location: casos_prueba.php?error=Campos obligatorios vacios');
        exit;
    }

    $evidencias = normalizarEvidencias($_POST['evidencias_actuales'] ?? []);

    try {
        $nuevas = guardarEvidenciasAdjuntas($_FILES['evidencias'] ?? []);
        $evidencias = array_values(array_unique(array_merge($evidencias, $nuevas)));
        $evidenciaJson = empty($evidencias) ? '' : json_encode($evidencias, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        if (!empty($id)) {
            $sql = "UPDATE qa_casos_prueba SET nombre_caso=?, modulo=?, prioridad=?, estado=?, objetivo=?, precondiciones=?, datos_prueba=?, pasos=?, resultado_esperado=?, tecnica_utilizada=?, evidencia=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $modulo, $prioridad, $estado, $objetivo, $precondiciones, $datos_prueba, $pasos, $resultado, $tecnica, $evidenciaJson, $id]);
        } else {
            $sql = "INSERT INTO qa_casos_prueba (codigo, nombre_caso, modulo, prioridad, estado, objetivo, precondiciones, datos_prueba, pasos, resultado_esperado, tecnica_utilizada, fecha_creacion, evidencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $nombre, $modulo, $prioridad, $estado, $objetivo, $precondiciones, $datos_prueba, $pasos, $resultado, $tecnica, $fecha, $evidenciaJson]);
        }
    } catch (Throwable $e) {
        header('Location: casos_prueba.php?error=' . rawurlencode($e->getMessage()));
        exit;
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