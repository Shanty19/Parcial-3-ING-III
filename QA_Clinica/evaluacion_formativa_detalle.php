<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: evaluacion_formativa.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM qa_evaluacion_formativa WHERE id = ?");
$stmt->execute([$id]);
$eval = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$eval) {
    header('Location: evaluacion_formativa.php');
    exit;
}

$criterios = json_decode($eval['criterios_json'], true) ?? [];
$preguntas = json_decode($eval['preguntas_json'], true) ?? [];

$titulo_pagina = 'Resultados - ' . $eval['codigo_evaluacion'];
$subtitulo_pagina = 'Desglose de calificaciones formativas y reflexiones.';
$pdf_export_data = [
    'title' => 'Detalle de evaluación: ' . $eval['codigo_evaluacion'] . ' - ' . $eval['tipo_evaluacion'],
    'subtitle' => 'Evaluador: ' . ($eval['evaluador'] ?? '') . ' | Evaluado: ' . ($eval['integrante_evaluado'] ?? ''),
    'fileName' => 'reporte_evaluacion_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $eval['codigo_evaluacion']) . '.pdf',
    'sections' => [
        [
            'title' => 'Resumen general',
            'items' => [
                ['label' => 'Código', 'value' => $eval['codigo_evaluacion'] ?? ''],
                ['label' => 'Tipo de evaluación', 'value' => $eval['tipo_evaluacion'] ?? ''],
                ['label' => 'Evaluador', 'value' => $eval['evaluador'] ?? ''],
                ['label' => 'Evaluado', 'value' => $eval['integrante_evaluado'] ?? ''],
                ['label' => 'Promedio general', 'value' => number_format($eval['promedio_general'], 2) . ' / 5.0'],
                ['label' => 'Criterios', 'value' => implode(' | ', array_map(fn($nombre, $valor) => $nombre . ': ' . ($valor['nota'] ?? 0) . '/5', array_keys($criterios), array_values($criterios)))]
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($eval['codigo_evaluacion']); ?> - <?php echo htmlspecialchars($eval['tipo_evaluacion']); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Evaluador: <strong><?php echo htmlspecialchars($eval['evaluador']); ?></strong> | Evaluado: <strong><?php echo htmlspecialchars($eval['integrante_evaluado']); ?></strong></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="evaluacion_formativa_form.php?id=<?php echo $eval['id']; ?>" style="background: #d97706; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; margin-right: 8px;">✏️ Editar</a>
        <a href="evaluacion_formativa.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<!-- Tarjeta de Promedio General -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="margin: 0 0 4px 0; font-size: 1.1rem; color: #0f172a;">Promedio General Formativo</h3>
        <p style="margin: 0; color: #64748b; font-size: 0.85rem;">Calculado en base a la escala de 1 a 5 de los criterios evaluados.</p>
    </div>
    <div style="font-size: 2.2rem; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 10px 24px; border-radius: 8px;">
        <?php echo number_format($eval['promedio_general'], 2); ?> <span style="font-size: 1rem; color: #64748b;">/ 5.0</span>
    </div>
</div>

<!-- Desglose por Criterio -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px;">Calificación por Criterio</h3>
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($criterios as $c_name => $c_data): 
            $pct = ($c_data['nota'] / 5) * 100;
        ?>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 4px;">
                    <strong style="color: #1e293b;"><?php echo htmlspecialchars($c_name); ?></strong>
                    <span style="font-weight: 600; color: #2563eb;"><?php echo $c_data['nota']; ?> / 5</span>
                </div>
                <div style="background: #f1f5f9; height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 4px;">
                    <div style="background: #3b82f6; width: <?php echo $pct; ?>%; height: 100%;"></div>
                </div>
                <small style="color: #64748b;"><?php echo htmlspecialchars($c_data['comentario']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Reflexión Cualitativa -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px;">Reflexión y Comentarios Cualitativos</h3>
    <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($preguntas as $p_key => $p_val): ?>
            <div style="background: #f8fafc; padding: 14px; border-radius: 6px; border-left: 4px solid #3b82f6;">
                <p style="margin: 0 0 6px 0; font-size: 0.9rem; color: #475569; font-weight: 600;">Respuesta:</p>
                <p style="margin: 0; font-size: 0.95rem; color: #1e293b;"><?php echo nl2br(htmlspecialchars($p_val)); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php include 'includes/footer.php'; ?>