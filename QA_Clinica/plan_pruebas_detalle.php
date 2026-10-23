<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: plan_pruebas.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM qa_plan_pruebas WHERE id = ?");
$stmt->execute([$id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    header('Location: plan_pruebas.php');
    exit;
}

// Obtener actividades y riesgos
$stmt_act = $pdo->prepare("SELECT * FROM qa_plan_actividades WHERE plan_id = ?");
$stmt_act->execute([$id]);
$actividades = $stmt_act->fetchAll(PDO::FETCH_ASSOC);

$stmt_ries = $pdo->prepare("SELECT * FROM qa_plan_riesgos WHERE plan_id = ?");
$stmt_ries->execute([$id]);
$riesgos = $stmt_ries->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = 'Detalle de Plan - ' . $plan['codigo_plan'];
$subtitulo_pagina = 'Visualización integral de la planificación de calidad.';
$pdf_export_data = [
    'title' => 'Detalle de plan: ' . $plan['codigo_plan'] . ' - ' . $plan['nombre_plan'],
    'subtitle' => 'Proyecto: ' . ($plan['proyecto'] ?? '') . ' | Versión: ' . ($plan['version_sistema'] ?? ''),
    'fileName' => 'reporte_plan_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $plan['codigo_plan']) . '.pdf',
    'sections' => [
        [
            'title' => 'Información general',
            'items' => [
                ['label' => 'Código', 'value' => $plan['codigo_plan'] ?? ''],
                ['label' => 'Nombre', 'value' => $plan['nombre_plan'] ?? ''],
                ['label' => 'Proyecto', 'value' => $plan['proyecto'] ?? ''],
                ['label' => 'Responsable', 'value' => $plan['responsable'] ?? ''],
                ['label' => 'Fechas', 'value' => ($plan['fecha_inicio'] ?? '') . ' al ' . ($plan['fecha_fin'] ?? '')],
                ['label' => 'Estado', 'value' => $plan['estado'] ?? ''],
                ['label' => 'Objetivo general', 'value' => $plan['objetivo_general'] ?? ''],
                ['label' => 'Alcance', 'value' => $plan['alcance'] ?? '']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($plan['codigo_plan']); ?> - <?php echo htmlspecialchars($plan['nombre_plan']); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Proyecto: <strong><?php echo htmlspecialchars($plan['proyecto']); ?></strong> | Versión: <?php echo htmlspecialchars($plan['version_sistema']); ?></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="plan_pruebas_form.php?id=<?php echo $plan['id']; ?>" style="background: #d97706; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; margin-right: 8px;">✏️ Editar</a>
        <a href="plan_pruebas.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<!-- Tarjetas de Resumen General -->
<div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Información General y Fechas</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 12px;">
        <div><strong>Responsable:</strong><br><?php echo htmlspecialchars($plan['responsable']); ?></div>
        <div><strong>Fechas:</strong><br><?php echo htmlspecialchars($plan['fecha_inicio']); ?> al <?php echo htmlspecialchars($plan['fecha_fin']); ?></div>
        <div><strong>Estado Actual:</strong><br>
            <span style="background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 0.8rem;">
                <?php echo htmlspecialchars($plan['estado']); ?>
            </span>
        </div>
    </div>
    <div style="margin-top: 16px;"><strong>Objetivo General:</strong><br><?php echo nl2br(htmlspecialchars($plan['objetivo_general'])); ?></div>
    <div style="margin-top: 12px;"><strong>Alcance:</strong><br><?php echo nl2br(htmlspecialchars($plan['alcance'])); ?></div>
</div>

<!-- Módulos y Estrategias -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a;">Módulos Seleccionados</h3>
        <ul>
            <?php 
            $mods = explode(',', $plan['modulos_seleccionados'] ?? '');
            foreach($mods as $m) { if(trim($m)) echo "<li>".htmlspecialchars(trim($m))."</li>"; }
            ?>
        </ul>
    </div>
    <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a;">Estrategias de Testing</h3>
        <ul>
            <?php 
            $ests = explode(',', $plan['estrategias_seleccionadas'] ?? '');
            foreach($ests as $e) { if(trim($e)) echo "<li>".htmlspecialchars(trim($e))."</li>"; }
            ?>
        </ul>
    </div>
</div>

<!-- Tabla de Actividades -->
<div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; margin-bottom: 12px;">Cronograma de Actividades</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f1f5f9; color: #1e293b;">
                <th style="padding: 10px; border: 1px solid #cbd5e1;">Actividad</th>
                <th style="padding: 10px; border: 1px solid #cbd5e1;">Descripción</th>
                <th style="padding: 10px; border: 1px solid #cbd5e1;">Responsable</th>
                <th style="padding: 10px; border: 1px solid #cbd5e1;">Fechas</th>
                <th style="padding: 10px; border: 1px solid #cbd5e1;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($actividades)): ?>
                <tr><td colspan="5" style="padding: 16px; text-align: center; color: #64748b;">No hay actividades registradas.</td></tr>
            <?php else: foreach($actividades as $act): ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #cbd5e1; font-weight: 600;"><?php echo htmlspecialchars($act['nombre_actividad']); ?></td>
                    <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($act['descripcion']); ?></td>
                    <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($act['responsable']); ?></td>
                    <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($act['fecha_inicio']); ?> al <?php echo htmlspecialchars($act['fecha_fin']); ?></td>
                    <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($act['estado_actividad']); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php include 'includes/footer.php'; ?>