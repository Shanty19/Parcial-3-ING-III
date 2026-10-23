<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
$usuario_actual = $_SESSION['usuario_nombre'] ?? 'Tester QA';

$eval = [
    'codigo_evaluacion' => '',
    'tipo_evaluacion' => 'Autoevaluación',
    'evaluador' => $usuario_actual,
    'integrante_evaluado' => $usuario_actual,
    'fecha' => date('Y-m-d'),
    'periodo_evaluado' => 'Parcial 3 / Fase Final',
    'objetivo' => 'Reflexión y valoración formativa del desempeño en QA_Clinica.',
    'estado' => 'Borrador'
];

// Criterios estándar de evaluación (Escala 1 al 5)
$criterios_base = [
    'Participación' => ['nota' => 5, 'comentario' => 'Participación activa en el diseño de pruebas.'],
    'Responsabilidad' => ['nota' => 5, 'comentario' => 'Cumplimiento estricto de entregas.'],
    'Calidad del trabajo' => ['nota' => 5, 'comentario' => 'Código limpio y estructurado con PDO.'],
    'Trabajo en equipo' => ['nota' => 4, 'comentario' => 'Buena coordinación y comunicación.'],
    'Cumplimiento de plazos' => ['nota' => 5, 'comentario' => 'Entregas a tiempo para el parcial.'],
    'Aporte técnico' => ['nota' => 5, 'comentario' => 'Implementación de formularios de caja negra.'],
    'Resolución de problemas' => ['nota' => 4, 'comentario' => 'Corrección oportuna de errores de interfaz.']
];

$preguntas_base = [
    'auto_1' => 'Mis principales aportes fueron el desarrollo de los formularios de caja negra y la integración del menú.',
    'auto_2' => 'Cumplí satisfactoriamente con la creación de los módulos de Valor Límite y Tabla de Decisión.',
    'auto_3' => 'Dificultades técnicas menores al alinear las tablas con el diseño visual.',
    'auto_4' => 'Aspectos a mejorar: Automatización de pruebas unitarias.'
];

// Generar código EV-001 si es nuevo
if (!$id) {
    try {
        $stmt = $pdo->query("SELECT codigo_evaluacion FROM qa_evaluacion_formativa ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last && preg_match('/EV-(\d+)/', $last['codigo_evaluacion'], $m)) {
            $num = intval($m[1]) + 1;
            $eval['codigo_evaluacion'] = 'EV-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } else {
            $eval['codigo_evaluacion'] = 'EV-001';
        }
    } catch (Exception $e) {
        $eval['codigo_evaluacion'] = 'EV-001';
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM qa_evaluacion_formativa WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $eval = $found;
        $criterios_base = json_decode($found['criterios_json'], true) ?? $criterios_base;
        $preguntas_base = json_decode($found['preguntas_json'], true) ?? $preguntas_base;
    }
}

$titulo_pagina = $id ? 'Editar Evaluación Formativa' : 'Nueva Evaluación Formativa';
$subtitulo_pagina = 'Complete la autoevaluación o coevaluación según corresponda.';
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo $id ? 'Editar Evaluación: ' . htmlspecialchars($eval['codigo_evaluacion']) : 'Crear Nueva Evaluación Formativa'; ?></h2>
    <a href="evaluacion_formativa.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Regresar</a>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <form action="evaluacion_formativa_acciones.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">

        <!-- Información General -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Información General</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">ID de Evaluación</label>
                <input type="text" name="codigo_evaluacion" value="<?php echo htmlspecialchars($eval['codigo_evaluacion']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Tipo de Evaluación <span style="color: red;">*</span></label>
                <select name="tipo_evaluacion" id="tipo_eval" onchange="toggleEvaluado()" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;" required>
                    <option value="Autoevaluación" <?php if($eval['tipo_evaluacion']=='Autoevaluación') echo 'selected'; ?>>Autoevaluación</option>
                    <option value="Coevaluación" <?php if($eval['tipo_evaluacion']=='Coevaluación') echo 'selected'; ?>>Coevaluación</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Evaluador</label>
                <input type="text" name="evaluador" value="<?php echo htmlspecialchars($eval['evaluador']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; box-sizing: border-box;" required>
            </div>
            <div id="div_evaluado">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Integrante Evaluado <span style="color: red;">*</span></label>
                <input type="text" name="integrante_evaluado" id="input_evaluado" value="<?php echo htmlspecialchars($eval['integrante_evaluado']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Fecha</label>
                <input type="date" name="fecha" value="<?php echo htmlspecialchars($eval['fecha']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Estado</label>
                <select name="estado" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="Borrador" <?php if($eval['estado']=='Borrador') echo 'selected'; ?>>Borrador</option>
                    <option value="Completada" <?php if($eval['estado']=='Completada') echo 'selected'; ?>>Completada</option>
                    <option value="Pendiente de revisión" <?php if($eval['estado']=='Pendiente de revisión') echo 'selected'; ?>>Pendiente de revisión</option>
                </select>
            </div>
        </div>

        <!-- Criterios de Evaluación (Escala 1 - 5) -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Criterios de Evaluación (Escala 1 a 5)</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">1: Deficiente | 2: Necesita mejorar | 3: Bueno | 4: Muy bueno | 5: Excelente</p>

        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
            <?php foreach ($criterios_base as $crit_name => $crit_data): ?>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: #1e293b;"><?php echo $crit_name; ?></strong>
                        <select name="criterio_nota[<?php echo $crit_name; ?>]" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-weight: 600;" required>
                            <?php for($i=1; $i<=5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if($crit_data['nota'] == $i) echo 'selected'; ?>><?php echo $i; ?> - <?php echo ($i==1?'Deficiente':($i==2?'Necesita mejorar':($i==3?'Bueno':($i==4?'Muy bueno':'Excelente')))); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <input type="text" name="criterio_comentario[<?php echo $crit_name; ?>]" value="<?php echo htmlspecialchars($crit_data['comentario']); ?>" placeholder="Comentario o justificación del criterio..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; box-sizing: border-box;">
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reflexión y Comentarios -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Reflexión y Preguntas Abiertas</h3>
        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">¿Cuáles fueron mis principales aportes o los del compañero al proyecto?</label>
                <textarea name="pregunta_1" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($preguntas_base['auto_1'] ?? ''); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">¿Qué tareas o responsabilidades se cumplieron satisfactoriamente?</label>
                <textarea name="pregunta_2" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($preguntas_base['auto_2'] ?? ''); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">¿Qué dificultades se encontraron y qué aspectos se deben mejorar?</label>
                <textarea name="pregunta_3" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($preguntas_base['auto_3'] ?? ''); ?></textarea>
            </div>
        </div>

        <div style="margin: 24px 0;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Evidencias</label>
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px;">
                <input type="file" id="evidenciasInput" name="evidencias[]" accept=".png,.jpg,.jpeg,image/png,image/jpeg" multiple style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; background: white;">
                <small style="display: block; margin-top: 8px; color: #64748b;">Formatos permitidos: PNG, JPG y JPEG. Puede adjuntar una o varias imágenes.</small>
                <input type="hidden" name="evidencias_actuales" id="evidenciasActuales" value='<?php echo json_encode(!empty($eval['evidencia']) ? (json_decode($eval['evidencia'], true) ?: [$eval['evidencia']]) : [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>
                <div id="evidenciasPreview" style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px;"></div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <a href="evaluacion_formativa.php" style="background: #f1f5f9; color: #475569; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600;">Cancelar</a>
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Guardar Evaluación</button>
        </div>
    </form>
</div>

<script>
(function () {
    const input = document.getElementById('evidenciasInput');
    const preview = document.getElementById('evidenciasPreview');
    const hidden = document.getElementById('evidenciasActuales');
    const allowedMimeTypes = ['image/png', 'image/jpeg'];
    const state = [];

    const existingItems = <?php $decoded = !empty($eval['evidencia']) ? json_decode($eval['evidencia'], true) : []; echo json_encode(is_array($decoded) ? $decoded : (!empty($eval['evidencia']) ? [$eval['evidencia']] : []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    existingItems.forEach((path) => {
        if (path) {
            state.push({ type: 'existing', source: path, name: path.split('/').pop(), file: null });
        }
    });

    function syncHidden() {
        hidden.value = JSON.stringify(state.filter(item => item.type === 'existing').map(item => item.source));
    }
    function refreshInputFiles() {
        const dt = new DataTransfer();
        state.filter(item => item.type === 'new').forEach(item => item.file && dt.items.add(item.file));
        input.files = dt.files;
    }
    function renderPreview() {
        preview.innerHTML = '';
        state.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.style.width = '170px';
            wrapper.style.border = '1px solid #e2e8f0';
            wrapper.style.borderRadius = '8px';
            wrapper.style.padding = '8px';
            wrapper.style.background = '#fff';

            const img = document.createElement('img');
            const src = item.source.startsWith('http') ? item.source : '../' + item.source.replace(/^\/+/, '');
            img.src = src;
            img.alt = item.name;
            img.style.width = '100%';
            img.style.height = '120px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.display = 'block';
            img.style.border = '1px solid #e2e8f0';

            const label = document.createElement('div');
            label.style.marginTop = '8px';
            label.style.fontSize = '0.75rem';
            label.style.color = '#475569';
            label.style.wordBreak = 'break-word';
            label.textContent = item.name;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = 'Eliminar';
            removeBtn.style.marginTop = '8px';
            removeBtn.style.width = '100%';
            removeBtn.style.border = '1px solid #ef4444';
            removeBtn.style.borderRadius = '6px';
            removeBtn.style.background = '#fff';
            removeBtn.style.color = '#b91c1c';
            removeBtn.style.cursor = 'pointer';
            removeBtn.addEventListener('click', function () {
                state.splice(index, 1); syncHidden(); refreshInputFiles(); renderPreview();
            });

            wrapper.appendChild(img); wrapper.appendChild(label); wrapper.appendChild(removeBtn); preview.appendChild(wrapper);
        });
    }

    input.addEventListener('change', function () {
        const files = Array.from(this.files || []);
        const validFiles = [];
        files.forEach((file) => {
            const extOk = /\.(png|jpe?g)$/i.test(file.name);
            const typeOk = allowedMimeTypes.includes(file.type);
            if (!extOk || !typeOk) {
                alert('Solo se permiten archivos PNG, JPG o JPEG.');
                return;
            }
            validFiles.push(file);
        });
        validFiles.forEach((file) => state.push({ type: 'new', source: URL.createObjectURL(file), name: file.name, file }));
        this.value = '';
        syncHidden(); refreshInputFiles(); renderPreview();
    });

    syncHidden(); renderPreview();
})();

function toggleEvaluado() {
    const tipo = document.getElementById('tipo_eval').value;
    const inputEvaluado = document.getElementById('input_evaluado');
    const evaluadorNombre = "<?php echo $eval['evaluador']; ?>";
    
    if (tipo === 'Autoevaluación') {
        inputEvaluado.value = evaluadorNombre;
        inputEvaluado.readOnly = true;
        inputEvaluado.style.background = '#f8fafc';
    } else {
        inputEvaluado.readOnly = false;
        inputEvaluado.style.background = 'white';
        if (inputEvaluado.value === evaluadorNombre) inputEvaluado.value = '';
    }
}
// Ejecutar al cargar si es autoevaluación
window.onload = function() { toggleEvaluado(); };
</script>

<?php include 'includes/footer.php'; ?>