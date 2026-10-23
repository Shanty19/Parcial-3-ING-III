<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: casos_prueba.php');
    exit;
}

// Consultar el caso de prueba específico por ID
$stmt = $pdo->prepare("SELECT * FROM qa_casos_prueba WHERE id = ?");
$stmt->execute([$id]);
$tc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tc) {
    header('Location: casos_prueba.php');
    exit;
}

$evidencias = [];
if (!empty($tc['evidencia'])) {
    $decoded = json_decode($tc['evidencia'], true);
    if (is_array($decoded)) {
        $evidencias = array_values(array_filter(array_map('strval', $decoded), function ($valor) {
            return trim($valor) !== '';
        }));
    } elseif (is_string($tc['evidencia']) && trim($tc['evidencia']) !== '') {
        $evidencias = [trim($tc['evidencia'])];
    }
}

$titulo_pagina = 'Detalle de Caso - ' . $tc['codigo'];
$subtitulo_pagina = 'Visualización integral de la especificación y resultado de la prueba.';
$pdf_export_data = [
    'title' => 'Detalle de caso: ' . $tc['codigo'] . ' - ' . $tc['nombre_caso'],
    'subtitle' => 'Módulo: ' . $tc['modulo'] . ' | Fecha de registro: ' . $tc['fecha_creacion'],
    'fileName' => 'reporte_caso_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $tc['codigo']) . '.pdf',
    'sections' => [
        [
            'title' => 'Información general',
            'items' => [
                ['label' => 'Código', 'value' => $tc['codigo'] ?? ''],
                ['label' => 'Nombre del caso', 'value' => $tc['nombre_caso'] ?? ''],
                ['label' => 'Técnica utilizada', 'value' => $tc['tecnica_utilizada'] ?? 'No especificada'],
                ['label' => 'Prioridad', 'value' => $tc['prioridad'] ?? ''],
                ['label' => 'Estado', 'value' => $tc['estado'] ?? ''],
                ['label' => 'Objetivo', 'value' => $tc['objetivo'] ?? ''],
                ['label' => 'Precondiciones', 'value' => $tc['precondiciones'] ?: 'Ninguna'],
                ['label' => 'Datos de prueba', 'value' => $tc['datos_prueba'] ?: 'No aplica'],
                ['label' => 'Pasos de ejecución', 'value' => $tc['pasos'] ?? ''],
                ['label' => 'Resultado esperado', 'value' => $tc['resultado_esperado'] ?? '']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($tc['codigo']); ?> - <?php echo htmlspecialchars($tc['nombre_caso']); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($tc['modulo']); ?></strong> | Fecha de registro: <?php echo htmlspecialchars($tc['fecha_creacion']); ?></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="casos_prueba.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver al listado</a>
    </div>
</div>

<!-- Tarjetas de Resumen del Caso -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Prioridad</span>
        <strong style="font-size: 1.2rem; color: #1e293b; display: block;"><?php echo htmlspecialchars($tc['prioridad']); ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Estado Actual</span>
        <?php 
            $s_bg = '#e0f2fe'; $s_col = '#0369a1';
            if ($tc['estado'] == 'Aprobado') { $s_bg = '#d1fae5'; $s_col = '#065f46'; }
            if ($tc['estado'] == 'Fallido') { $s_bg = '#fee2e2'; $s_col = '#991b1b'; }
        ?>
        <span style="background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>; padding: 4px 12px; border-radius: 12px; font-size: 0.9rem; font-weight: 600;">
            <?php echo htmlspecialchars($tc['estado']); ?>
        </span>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #8b5cf6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Técnica utilizada</span>
        <strong style="font-size: 1.2rem; color: #1e293b; display: block;"><?php echo htmlspecialchars($tc['tecnica_utilizada'] ?? 'No especificada'); ?></strong>
    </div>
</div>

<!-- Bloque Principal de Especificación -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Objetivo de la Prueba</h3>
    <p style="margin: 0; color: #334155; line-height: 1.6; font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($tc['objetivo'])); ?></p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Precondiciones</h3>
        <p style="margin: 0; color: #334155; line-height: 1.5; font-size: 0.9rem;"><?php echo !empty($tc['precondiciones']) ? nl2br(htmlspecialchars($tc['precondiciones'])) : '<span style="color: #94a3b8;">Ninguna precondición especificada.</span>'; ?></p>
    </div>
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Datos de Prueba</h3>
        <p style="margin: 0; color: #334155; line-height: 1.5; font-size: 0.9rem;"><?php echo !empty($tc['datos_prueba']) ? nl2br(htmlspecialchars($tc['datos_prueba'])) : '<span style="color: #94a3b8;">No se requieren datos de prueba específicos.</span>'; ?></p>
    </div>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Pasos de Ejecución</h3>
    <pre style="background: #f8fafc; padding: 16px; border-radius: 6px; border: 1px solid #e2e8f0; font-family: inherit; font-size: 0.9rem; color: #1e293b; white-space: pre-wrap; margin: 0;"><?php echo htmlspecialchars($tc['pasos']); ?></pre>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Resultado Esperado</h3>
    <div style="background: #f0fdf4; padding: 16px; border-radius: 6px; border: 1px solid #bbf7d0; color: #166534; font-size: 0.95rem; line-height: 1.5;">
        <?php echo nl2br(htmlspecialchars($tc['resultado_esperado'])); ?>
    </div>
</div>

<?php if (!empty($evidencias)): ?>
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Evidencias</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 16px;">
        <?php foreach ($evidencias as $evidencia): ?>
            <?php $src = preg_match('/^https?:\/\//i', $evidencia) ? $evidencia : '../' . ltrim($evidencia, '/'); ?>
            <div style="width: 180px;">
                <img src="<?php echo htmlspecialchars($src); ?>" alt="Evidencia" style="width: 100%; height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; display: block;">
                <div style="margin-top: 8px; font-size: 0.75rem; color: #475569; word-break: break-word;"><?php echo htmlspecialchars(basename($evidencia)); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php include 'includes/footer.php'; ?>