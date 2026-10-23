<?php
session_start();
require_once(__DIR__ . '/../config.php');

$id = $_GET['id'] ?? null;
$analisis = [
    'codigo_analisis' => '',
    'modulo' => '',
    'campo_evaluado' => '',
    'tipo_dato' => 'Numérico',
    'valor_minimo' => '',
    'valor_maximo' => '',
    'unidad_longitud' => '',
    'objetivo' => '',
    'precondiciones' => '',
    'datos_entrada' => '',
    'resultado_esperado' => '',
    'resultado_obtenido' => '',
    'estado' => 'Pendiente',
    'observaciones' => '',
    'evidencia' => ''
];

// Generar código automático si es nuevo
if (!$id) {
    try {
        $stmt = $pdo->query("SELECT codigo_analisis FROM qa_analisis_valor_limite ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last && preg_match('/AVL-(\d+)/', $last['codigo_analisis'], $m)) {
            $num = intval($m[1]) + 1;
            $analisis['codigo_analisis'] = 'AVL-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } else {
            $analisis['codigo_analisis'] = 'AVL-001';
        }
    } catch (Exception $e) {
        $analisis['codigo_analisis'] = 'AVL-001';
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM qa_analisis_valor_limite WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $analisis = $found;
    }
}

$evidencias_actuales = [];
if (!empty($analisis['evidencia'])) {
    $decoded = json_decode($analisis['evidencia'], true);
    if (is_array($decoded)) {
        $evidencias_actuales = array_values(array_filter(array_map('strval', $decoded), function ($valor) {
            return trim($valor) !== '';
        }));
    } elseif (is_string($analisis['evidencia']) && trim($analisis['evidencia']) !== '') {
        $evidencias_actuales = [trim($analisis['evidencia'])];
    }
}

$titulo_pagina = $id ? 'Editar Análisis de Valor Límite' : 'Nuevo Análisis de Valor Límite';
$subtitulo_pagina = 'Complete los campos para documentar la prueba de caja negra.';
include 'includes/header.php';
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $id ? 'Editar Análisis: ' . $analisis['codigo_analisis'] : 'Registrar Nuevo Análisis de Valor Límite'; ?></h2>
        <a href="analisis_valor_limite.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Regresar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="analisis_valor_limite_acciones.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">ID del Análisis <span class="text-danger">*</span></label>
                        <input type="text" name="codigo_analisis" class="form-control" value="<?php echo htmlspecialchars($analisis['codigo_analisis']); ?>" readonly required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Módulo / Funcionalidad <span class="text-danger">*</span></label>
                        <select name="modulo" class="form-select" required>
                            <option value="">Seleccione un módulo real...</option>
                            <option value="Gestión de pacientes" <?php if($analisis['modulo']=='Gestión de pacientes') echo 'selected'; ?>>Gestión de pacientes</option>
                            <option value="Búsqueda de pacientes" <?php if($analisis['modulo']=='Búsqueda de pacientes') echo 'selected'; ?>>Búsqueda de pacientes</option>
                            <option value="Consulta de doctores y especialidades" <?php if($analisis['modulo']=='Consulta de doctores y especialidades') echo 'selected'; ?>>Consulta de doctores y especialidades</option>
                            <option value="Comunicación SOAP (bus.php)" <?php if($analisis['modulo']=='Comunicación SOAP (bus.php)') echo 'selected'; ?>>Comunicación SOAP (bus.php)</option>
                            <option value="Historial de búsquedas" <?php if($analisis['modulo']=='Historial de búsquedas') echo 'selected'; ?>>Historial de búsquedas</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Campo Evaluado <span class="text-danger">*</span></label>
                        <input type="text" name="campo_evaluado" class="form-control" placeholder="Ej. Edad, Cédula, Cantidad" value="<?php echo htmlspecialchars($analisis['campo_evaluado']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Dato</label>
                        <select name="tipo_dato" class="form-select">
                            <option value="Numérico" <?php if($analisis['tipo_dato']=='Numérico') echo 'selected'; ?>>Numérico</option>
                            <option value="Texto / Cadena" <?php if($analisis['tipo_dato']=='Texto / Cadena') echo 'selected'; ?>>Texto / Cadena</option>
                            <option value="Fecha" <?php if($analisis['tipo_dato']=='Fecha') echo 'selected'; ?>>Fecha</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor Mínimo Permitido</label>
                        <input type="text" name="valor_minimo" class="form-control" placeholder="Ej. 0 o Pendiente" value="<?php echo htmlspecialchars($analisis['valor_minimo']); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor Máximo Permitido</label>
                        <input type="text" name="valor_maximo" class="form-control" placeholder="Ej. 120 o Pendiente" value="<?php echo htmlspecialchars($analisis['valor_maximo']); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unidad / Longitud</label>
                        <input type="text" name="unidad_longitud" class="form-control" placeholder="Ej. Caracteres" value="<?php echo htmlspecialchars($analisis['unidad_longitud']); ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Objetivo <span class="text-danger">*</span></label>
                        <textarea name="objetivo" class="form-control" rows="2" required><?php echo htmlspecialchars($analisis['objetivo']); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Precondiciones</label>
                        <textarea name="precondiciones" class="form-control" rows="2"><?php echo htmlspecialchars($analisis['precondiciones']); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Datos de Entrada</label>
                        <textarea name="datos_entrada" class="form-control" rows="2"><?php echo htmlspecialchars($analisis['datos_entrada']); ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Resultado Esperado <span class="text-danger">*</span></label>
                        <textarea name="resultado_esperado" class="form-control" rows="2" required><?php echo htmlspecialchars($analisis['resultado_esperado']); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Resultado Obtenido</label>
                        <textarea name="resultado_obtenido" class="form-control" rows="2"><?php echo htmlspecialchars($analisis['resultado_obtenido']); ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado Inicial <span class="text-danger">*</span></label>
                        <select name="estado" class="form-select" required>
                            <option value="Pendiente" <?php if($analisis['estado']=='Pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="Aprobado" <?php if($analisis['estado']=='Aprobado') echo 'selected'; ?>>Aprobado</option>
                            <option value="Fallido" <?php if($analisis['estado']=='Fallido') echo 'selected'; ?>>Fallido</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Observaciones</label>
                        <input type="text" name="observaciones" class="form-control" value="<?php echo htmlspecialchars($analisis['observaciones']); ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Evidencias</label>
                        <div class="border rounded p-3" style="background: #f8fafc;">
                            <input type="file" id="evidenciasInput" name="evidencias[]" accept=".png,.jpg,.jpeg,image/png,image/jpeg" multiple class="form-control">
                            <small class="text-muted d-block mt-2">Formatos permitidos: PNG, JPG y JPEG. Puede adjuntar una o varias imágenes.</small>
                            <input type="hidden" name="evidencias_actuales" id="evidenciasActuales" value='<?php echo json_encode($evidencias_actuales, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>
                            <div id="evidenciasPreview" class="mt-3 d-flex flex-wrap gap-3"></div>
                        </div>
                    </div>

                    <div class="col-md-12 text-end mt-4">
                        <a href="analisis_valor_limite.php" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Análisis</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById('evidenciasInput');
    const preview = document.getElementById('evidenciasPreview');
    const hidden = document.getElementById('evidenciasActuales');
    const allowedMimeTypes = ['image/png', 'image/jpeg'];
    const state = [];

    const existingItems = <?php echo json_encode($evidencias_actuales, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    existingItems.forEach((path) => {
        if (path) {
            state.push({
                type: 'existing',
                source: path,
                name: path.split('/').pop(),
                file: null
            });
        }
    });

    function syncHidden() {
        const kept = state.filter(item => item.type === 'existing').map(item => item.source);
        hidden.value = JSON.stringify(kept);
    }

    function refreshInputFiles() {
        const dataTransfer = new DataTransfer();
        state.filter(item => item.type === 'new').forEach(item => {
            if (item.file) {
                dataTransfer.items.add(item.file);
            }
        });
        input.files = dataTransfer.files;
    }

    function renderPreview() {
        preview.innerHTML = '';

        state.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-2 bg-white';
            wrapper.style.width = '170px';

            const img = document.createElement('img');
            const path = item.source.startsWith('http') ? item.source : '../' + item.source.replace(/^\/+/, '');
            img.src = path;
            img.alt = item.name;
            img.style.width = '100%';
            img.style.height = '120px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.display = 'block';
            img.style.border = '1px solid #e2e8f0';

            const label = document.createElement('div');
            label.className = 'small mt-2 text-break';
            label.textContent = item.name;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger mt-2 w-100';
            removeBtn.textContent = 'Eliminar';
            removeBtn.addEventListener('click', function () {
                state.splice(index, 1);
                syncHidden();
                refreshInputFiles();
                renderPreview();
            });

            wrapper.appendChild(img);
            wrapper.appendChild(label);
            wrapper.appendChild(removeBtn);
            preview.appendChild(wrapper);
        });
    }

    input.addEventListener('change', function () {
        const files = Array.from(this.files || []);
        const validFiles = [];

        files.forEach((file) => {
            const extensionOk = /\.(png|jpe?g)$/i.test(file.name);
            const typeOk = allowedMimeTypes.includes(file.type);

            if (!extensionOk || !typeOk) {
                alert('Solo se permiten archivos PNG, JPG o JPEG.');
                return;
            }

            validFiles.push(file);
        });

        validFiles.forEach((file) => {
            state.push({
                type: 'new',
                source: URL.createObjectURL(file),
                name: file.name,
                file: file
            });
        });

        this.value = '';
        syncHidden();
        refreshInputFiles();
        renderPreview();
    });

    syncHidden();
    renderPreview();
})();
</script>

<?php include 'includes/footer.php'; ?>