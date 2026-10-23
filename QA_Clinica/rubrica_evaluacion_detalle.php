<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: rubrica_evaluacion.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM rubrica_evaluacion WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: rubrica_evaluacion.php');
    exit;
}

$stmtCriterios = $pdo->prepare('SELECT * FROM rubrica_criterios WHERE rubrica_id = ? ORDER BY id ASC');
$stmtCriterios->execute([$id]);
$criterios = $stmtCriterios->fetchAll(PDO::FETCH_ASSOC);
$totalPuntaje = array_reduce($criterios, fn($carry, $c) => $carry + (int) ($c['puntaje_maximo'] ?? 0), 0);

$codigo = $item['codigo'] ?? '';
$modulo = $item['modulo'] ?? '';
$tituloRubrica = $item['titulo'] ?? '';
$descripcion = $item['descripcion'] ?? '';

$titulo_pagina = 'Detalle de Rúbrica - ' . $codigo;
$subtitulo_pagina = 'Criterios, puntajes y criterios de evaluación del registro seleccionado.';
$pdf_export_data = [
    'title' => 'Detalle de rúbrica: ' . $codigo . ' - ' . $tituloRubrica,
    'subtitle' => 'Módulo: ' . $modulo . ' | Puntaje máximo: ' . $totalPuntaje . ' pts',
    'fileName' => 'reporte_rubrica_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo) . '.pdf',
    'sections' => [
        [
            'title' => 'Descripción',
            'items' => [
                ['label' => 'Código', 'value' => $codigo],
                ['label' => 'Título', 'value' => $tituloRubrica],
                ['label' => 'Módulo', 'value' => $modulo],
                ['label' => 'Puntaje máximo', 'value' => $totalPuntaje . ' pts'],
                ['label' => 'Descripción', 'value' => $descripcion ?: 'Sin descripción']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($codigo); ?> - <?php echo htmlspecialchars($tituloRubrica); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($modulo); ?></strong> | Puntaje máximo: <strong><?php echo htmlspecialchars($totalPuntaje); ?> pts</strong></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="rubrica_evaluacion.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Descripción</h3>
    <p style="margin: 0; color: #334155; line-height: 1.6;"><?php echo !empty($descripcion) ? nl2br(htmlspecialchars($descripcion)) : '<span style="color: #94a3b8;">Sin descripción.</span>'; ?></p>
</div>

<div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px;">Criterios de evaluación</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">#</th>
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Descripción del criterio</th>
                    <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Puntaje máximo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($criterios)): ?>
                    <tr><td colspan="3" style="padding: 16px; text-align: center; color: #64748b;">No hay criterios registrados para esta rúbrica.</td></tr>
                <?php else: foreach ($criterios as $index => $criterio): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #cbd5e1; font-weight: 600;"><?php echo $index + 1; ?></td>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo nl2br(htmlspecialchars($criterio['descripcion_criterio'])); ?></td>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;"><strong><?php echo htmlspecialchars($criterio['puntaje_maximo']); ?> pts</strong></td>
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
