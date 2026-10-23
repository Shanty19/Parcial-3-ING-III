<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: tabla_decision.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM qa_tabla_decision WHERE id = ?");
$stmt->execute([$id]);
$tabla = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tabla) {
    header('Location: tabla_decision.php');
    exit;
}

// Obtener reglas de detalle
$stmt_reglas = $pdo->prepare("SELECT * FROM qa_td_reglas_detalle WHERE tabla_id = ? ORDER BY regla_num ASC");
$stmt_reglas->execute([$id]);
$reglas = $stmt_reglas->fetchAll(PDO::FETCH_ASSOC);

$titulo_pagina = 'Detalle de Tabla - ' . $tabla['codigo_tabla'];
$subtitulo_pagina = 'Matriz de decisión, condiciones y ejecución de reglas.';
$pdf_export_data = [
    'title' => 'Detalle de tabla: ' . $tabla['codigo_tabla'] . ' - ' . $tabla['nombre_prueba'],
    'subtitle' => 'Módulo: ' . ($tabla['modulo'] ?? '') . ' | Estado: ' . ($tabla['estado_general'] ?? ''),
    'fileName' => 'reporte_tabla_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $tabla['codigo_tabla']) . '.pdf',
    'sections' => [
        [
            'title' => 'Información general',
            'items' => [
                ['label' => 'Código', 'value' => $tabla['codigo_tabla'] ?? ''],
                ['label' => 'Nombre de la prueba', 'value' => $tabla['nombre_prueba'] ?? ''],
                ['label' => 'Módulo', 'value' => $tabla['modulo'] ?? ''],
                ['label' => 'Objetivo', 'value' => $tabla['objetivo'] ?? ''],
                ['label' => 'Precondiciones', 'value' => $tabla['precondiciones'] ?: 'Ninguna'],
                ['label' => 'Estado general', 'value' => $tabla['estado_general'] ?? '']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;">Tabla: <?php echo htmlspecialchars($tabla['codigo_tabla']); ?> - <?php echo htmlspecialchars($tabla['nombre_prueba']); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($tabla['modulo']); ?></strong></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="tabla_decision_form.php?id=<?php echo $tabla['id']; ?>" style="background: #d97706; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; margin-right: 8px;">✏️ Editar</a>
        <a href="tabla_decision.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<!-- Información General -->
<div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a;">Información de la Prueba</h3>
    <p><strong>Objetivo:</strong> <?php echo nl2br(htmlspecialchars($tabla['objetivo'])); ?></p>
    <p><strong>Precondiciones:</strong> <?php echo htmlspecialchars($tabla['precondiciones'] ?: 'Ninguna'); ?></p>
    <p><strong>Estado General:</strong> 
        <span style="background: <?php echo ($tabla['estado_general']=='Aprobado')?'#d1fae5':(($tabla['estado_general']=='Fallido')?'#fee2e2':'#fef3c7'); ?>; color: <?php echo ($tabla['estado_general']=='Aprobado')?'#065f46':(($tabla['estado_general']=='Fallido')?'#991b1b':'#92400e'); ?>; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 0.8rem;">
            <?php echo htmlspecialchars($tabla['estado_general']); ?>
        </span>
    </p>
</div>

<!-- Matriz de Decisión y Evaluación de Reglas -->
<div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px;">Matriz de Reglas y Ejecución</h3>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: center;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px; border: 1px solid #334155;">Condiciones / Reglas</th>
                    <?php foreach ($reglas as $r): ?>
                        <th style="padding: 12px; border: 1px solid #334155;">Regla <?php echo $r['regla_num']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($reglas)) {
                    $first_conds = json_decode($reglas[0]['condiciones_json'], true);
                    foreach (array_keys($first_conds) as $c_name):
                ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #cbd5e1; text-align: left; font-weight: 600; background: #f8fafc;"><?php echo htmlspecialchars($c_name); ?></td>
                        <?php foreach ($reglas as $r): 
                            $c_map = json_decode($r['condiciones_json'], true);
                        ?>
                            <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($c_map[$c_name] ?? '-'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; } ?>

                <!-- Fila Acción Esperada -->
                <tr style="background: #f1f5f9; font-weight: 600;">
                    <td style="padding: 10px; border: 1px solid #cbd5e1; text-align: left;">Acción Esperada</td>
                    <?php foreach ($reglas as $r): ?>
                        <td style="padding: 10px; border: 1px solid #cbd5e1; color: #2563eb;"><?php echo htmlspecialchars($r['accion_esperada']); ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Fila Resultado Obtenido -->
                <tr>
                    <td style="padding: 10px; border: 1px solid #cbd5e1; text-align: left; font-weight: 600;">Resultado Obtenido</td>
                    <?php foreach ($reglas as $r): ?>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($r['resultado_obtenido']); ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Fila Estado de Regla -->
                <tr>
                    <td style="padding: 10px; border: 1px solid #cbd5e1; text-align: left; font-weight: 600;">Estado</td>
                    <?php foreach ($reglas as $r): ?>
                        <td style="padding: 10px; border: 1px solid #cbd5e1;">
                            <span style="background: <?php echo ($r['estado_regla']=='Aprobado')?'#d1fae5':(($r['estado_regla']=='Fallido')?'#fee2e2':'#fef3c7'); ?>; color: <?php echo ($r['estado_regla']=='Aprobado')?'#065f46':(($r['estado_regla']=='Fallido')?'#991b1b':'#92400e'); ?>; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">
                                <?php echo htmlspecialchars($r['estado_regla']); ?>
                            </span>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php include 'includes/footer.php'; ?>