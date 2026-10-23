<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: portafolio_evidencias.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM portafolio_evidencias WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: portafolio_evidencias.php');
    exit;
}

$codigo = $item['codigo'] ?? '';
$tituloEvidencia = $item['titulo'] ?? '';
$modulo = $item['modulo'] ?? '';
$tipoEvidencia = $item['tipo_evidencia'] ?? '';
$fasePrueba = $item['fase_prueba'] ?? '';
$enlaceArchivo = $item['enlace_archivo'] ?? '';
$descripcion = $item['descripcion'] ?? '';
$observaciones = $item['observaciones'] ?? '';

$titulo_pagina = 'Detalle de Evidencia - ' . $codigo;
$subtitulo_pagina = 'Información completa de la evidencia registrada para la prueba.';
$pdf_export_data = [
    'title' => 'Detalle de evidencia: ' . $codigo . ' - ' . $tituloEvidencia,
    'subtitle' => 'Módulo: ' . $modulo . ' | Tipo: ' . $tipoEvidencia . ' | Fase: ' . $fasePrueba,
    'fileName' => 'reporte_evidencia_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo) . '.pdf',
    'sections' => [
        [
            'title' => 'Información de la evidencia',
            'items' => [
                ['label' => 'Código', 'value' => $codigo],
                ['label' => 'Título', 'value' => $tituloEvidencia],
                ['label' => 'Módulo', 'value' => $modulo],
                ['label' => 'Tipo', 'value' => $tipoEvidencia],
                ['label' => 'Fase de prueba', 'value' => $fasePrueba],
                ['label' => 'Enlace del recurso', 'value' => $enlaceArchivo ?: 'Sin enlace'],
                ['label' => 'Descripción', 'value' => $descripcion ?: 'Sin descripción'],
                ['label' => 'Observaciones', 'value' => $observaciones ?: 'Sin observaciones']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($codigo); ?> - <?php echo htmlspecialchars($tituloEvidencia); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($modulo); ?></strong> | Tipo: <strong><?php echo htmlspecialchars($tipoEvidencia); ?></strong></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="portafolio_evidencias.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Fase de prueba</span>
        <strong style="font-size: 1.05rem; color: #1e293b; display: block;"><?php echo htmlspecialchars($fasePrueba); ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Enlace</span>
        <a href="<?php echo htmlspecialchars($enlaceArchivo); ?>" target="_blank" style="font-size: 1rem; color: #2563eb; display: block; text-decoration: none;">Ver recurso</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Descripción</h3>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><?php echo !empty($descripcion) ? nl2br(htmlspecialchars($descripcion)) : '<span style="color: #94a3b8;">Sin descripción.</span>'; ?></p>
    </div>
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Observaciones</h3>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><?php echo !empty($observaciones) ? nl2br(htmlspecialchars($observaciones)) : '<span style="color: #94a3b8;">Sin observaciones.</span>'; ?></p>
    </div>
</div>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<?php include 'includes/footer.php'; ?>
