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

$titulo_pagina = 'Detalle de Caso - ' . $tc['codigo'];
$subtitulo_pagina = 'Visualización integral de la especificación y resultado de la prueba.';
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($tc['codigo']); ?> - <?php echo htmlspecialchars($tc['nombre_caso']); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($tc['modulo']); ?></strong> | Fecha de registro: <?php echo htmlspecialchars($tc['fecha_creacion']); ?></p>
    </div>
    <div>
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
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Proyecto</span>
        <strong style="font-size: 1.2rem; color: #1e293b; display: block;">Clínica Salud+</strong>
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

<?php include 'includes/footer.php'; ?>