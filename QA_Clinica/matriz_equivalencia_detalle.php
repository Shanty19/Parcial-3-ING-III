<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: matriz_equivalencia.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM matriz_equivalencia WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: matriz_equivalencia.php');
    exit;
}

$stmtClases = $pdo->prepare('SELECT * FROM clases_equivalencia WHERE matriz_id = ? ORDER BY id ASC');
$stmtClases->execute([$id]);
$clases = $stmtClases->fetchAll(PDO::FETCH_ASSOC);

$codigo = $item['codigo'] ?? '';
$nombreAnalisis = $item['nombre_analisis'] ?? '';
$modulo = $item['modulo'] ?? '';
$campoValidar = $item['campo_validar'] ?? '';
$reglaValidacion = $item['regla_validacion'] ?? '';
$observaciones = $item['observaciones'] ?? '';

$titulo_pagina = 'Detalle de Matriz - ' . $codigo;
$subtitulo_pagina = 'Desglose de clases válidas e inválidas para la regla evaluada.';
$pdf_export_data = [
    'title' => 'Detalle de matriz: ' . $codigo . ' - ' . $nombreAnalisis,
    'subtitle' => 'Módulo: ' . $modulo . ' | Campo: ' . $campoValidar,
    'fileName' => 'reporte_matriz_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo) . '.pdf',
    'sections' => [
        [
            'title' => 'Información general',
            'items' => [
                ['label' => 'Código', 'value' => $codigo],
                ['label' => 'Nombre del análisis', 'value' => $nombreAnalisis],
                ['label' => 'Módulo', 'value' => $modulo],
                ['label' => 'Campo a validar', 'value' => $campoValidar],
                ['label' => 'Regla de validación', 'value' => $reglaValidacion ?: 'Sin regla'],
                ['label' => 'Observaciones', 'value' => $observaciones ?: 'Sin observaciones']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($codigo); ?> - <?php echo htmlspecialchars($nombreAnalisis); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($modulo); ?></strong> | Campo: <strong><?php echo htmlspecialchars($campoValidar); ?></strong></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="matriz_equivalencia.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Información general</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <p style="margin: 0; color: #334155; line-height: 1.6;"><strong>Regla de validación:</strong> <?php echo nl2br(htmlspecialchars($reglaValidacion)); ?></p>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><strong>Observaciones:</strong> <?php echo !empty($observaciones) ? nl2br(htmlspecialchars($observaciones)) : '<span style="color: #94a3b8;">Sin observaciones.</span>'; ?></p>
    </div>
</div>

<div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px;">Clases de equivalencia</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Clase</th>
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Tipo</th>
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Dato de ejemplo</th>
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Resultado esperado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clases)): ?>
                    <tr><td colspan="4" style="padding: 16px; text-align: center; color: #64748b;">No hay clases registradas para este análisis.</td></tr>
                <?php else: foreach ($clases as $clase): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($clase['nombre_clase']); ?></td>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;">
                            <span style="background: <?php echo ($clase['tipo_clase'] === 'Válida') ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo ($clase['tipo_clase'] === 'Válida') ? '#065f46' : '#991b1b'; ?>; padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">
                                <?php echo htmlspecialchars($clase['tipo_clase']); ?>
                            </span>
                        </td>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo nl2br(htmlspecialchars($clase['dato_ejemplo'])); ?></td>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo nl2br(htmlspecialchars($clase['resultado_esperado'])); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php include 'includes/footer.php'; ?>
