<?php
session_start();
require_once __DIR__ . '/../config.php';

$titulo_pagina = 'Autoevaluación y Coevaluación';
$subtitulo_pagina = 'Evaluación formativa del desempeño, colaboración y participación del equipo';

include 'includes/header.php';

// Obtener estadísticas reales para las tarjetas de resumen
$stats = ['total' => 0, 'auto' => 0, 'coeval' => 0, 'completadas' => 0];
try {
    $stmt = $pdo->query("SELECT tipo_evaluacion, estado, COUNT(*) as count FROM qa_evaluacion_formativa GROUP BY tipo_evaluacion, estado");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['tipo_evaluacion'] == 'Autoevaluación') $stats['auto'] += $row['count'];
        if ($row['tipo_evaluacion'] == 'Coevaluación') $stats['coeval'] += $row['count'];
        if ($row['estado'] == 'Completada') $stats['completadas'] += $row['count'];
        $stats['total'] += $row['count'];
    }
} catch (Exception $e) {}

// Pestaña activa (Auto o Coeval)
$tab = $_GET['tab'] ?? 'todos';
$search = $_GET['search'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$sql = "SELECT * FROM qa_evaluacion_formativa WHERE 1=1";
$params = [];

if ($tab == 'auto') {
    $sql .= " AND tipo_evaluacion = 'Autoevaluación'";
} elseif ($tab == 'coeval') {
    $sql .= " AND tipo_evaluacion = 'Coevaluación'";
}

if (!empty($search)) {
    $sql .= " AND (codigo_evaluacion LIKE ? OR evaluador LIKE ? OR integrante_evaluado LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($filtro_estado) && $filtro_estado != 'ALL') {
    $sql .= " AND estado = ?";
    $params[] = $filtro_estado;
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $eval_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $eval_list = [];
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 700;">Autoevaluación y Coevaluación</h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Evaluación formativa del desempeño, colaboración y participación del equipo</p>
    </div>
    <div>
        <a href="evaluacion_formativa_form.php" style="background-color: #3b82f6; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            + Nueva evaluación
        </a>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Evaluaciones Realizadas</span>
        <strong style="font-size: 1.8rem; color: #1e293b; display: block;"><?php echo $stats['total']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Autoevaluaciones</span>
        <strong style="font-size: 1.8rem; color: #10b981; display: block;"><?php echo $stats['auto']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #8b5cf6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Coevaluaciones</span>
        <strong style="font-size: 1.8rem; color: #8b5cf6; display: block;"><?php echo $stats['coeval']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Completadas</span>
        <strong style="font-size: 1.8rem; color: #f59e0b; display: block;"><?php echo $stats['completadas']; ?></strong>
    </div>
</div>

<!-- Pestañas de Navegación -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
    <a href="evaluacion_formativa.php?tab=todos" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; background: <?php echo ($tab=='todos')?'#3b82f6':'#f1f5f9'; ?>; color: <?php echo ($tab=='todos')?'white':'#475569'; ?>;">Todas</a>
    <a href="evaluacion_formativa.php?tab=auto" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; background: <?php echo ($tab=='auto')?'#3b82f6':'#f1f5f9'; ?>; color: <?php echo ($tab=='auto')?'white':'#475569'; ?>;">Autoevaluación</a>
    <a href="evaluacion_formativa.php?tab=coeval" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; background: <?php echo ($tab=='coeval')?'#3b82f6':'#f1f5f9'; ?>; color: <?php echo ($tab=='coeval')?'white':'#475569'; ?>;">Coevaluación</a>
</div>

<!-- Buscador y Filtros -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="tab" value="<?php echo $tab; ?>">
        <div style="flex: 2; min-width: 240px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Buscar por código, evaluador o evaluado</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ej. EV-001 o Tester QA" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Estado</label>
            <select name="estado" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los estados</option>
                <option value="Borrador" <?php if($filtro_estado=='Borrador') echo 'selected'; ?>>Borrador</option>
                <option value="Completada" <?php if($filtro_estado=='Completada') echo 'selected'; ?>>Completada</option>
                <option value="Pendiente de revisión" <?php if($filtro_estado=='Pendiente de revisión') echo 'selected'; ?>>Pendiente de revisión</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">Buscar</button>
            <a href="evaluacion_formativa.php?tab=<?php echo $tab; ?>" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center;">Limpiar</a>
        </div>
    </form>
</div>

<!-- Tabla de Evaluaciones -->
<div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a;">Registros de Evaluación Formativa</h3>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px 16px;">Código</th>
                    <th style="padding: 12px 16px;">Tipo</th>
                    <th style="padding: 12px 16px;">Evaluador</th>
                    <th style="padding: 12px 16px;">Integrante Evaluado</th>
                    <th style="padding: 12px 16px;">Promedio (1-5)</th>
                    <th style="padding: 12px 16px;">Estado</th>
                    <th style="padding: 12px 16px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($eval_list)): ?>
                    <tr>
                        <td colspan="7" style="padding: 32px; text-align: center; color: #94a3b8;">
                            No hay evaluaciones registradas. Haz clic en "<strong>+ Nueva evaluación</strong>" para comenzar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($eval_list as $row): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['codigo_evaluacion']); ?></td>
                            <td style="padding: 14px 16px;">
                                <span style="background: <?php echo ($row['tipo_evaluacion']=='Autoevaluación')?'#d1fae5':'#ede9fe'; ?>; color: <?php echo ($row['tipo_evaluacion']=='Autoevaluación')?'#065f46':'#5b21b6'; ?>; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['tipo_evaluacion']); ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['evaluador']); ?></td>
                            <td style="padding: 14px 16px; color: #0f172a; font-weight: 500;"><?php echo htmlspecialchars($row['integrante_evaluado']); ?></td>
                            <td style="padding: 14px 16px; font-weight: 700; color: #2563eb;"><?php echo number_format($row['promedio_general'], 2); ?> / 5.0</td>
                            <td style="padding: 14px 16px;">
                                <?php 
                                    $bg = '#fef3c7'; $color = '#92400e';
                                    if ($row['estado'] == 'Completada') { $bg = '#d1fae5'; $color = '#065f46'; }
                                    if ($row['estado'] == 'Pendiente de revisión') { $bg = '#e0f2fe'; $color = '#0369a1'; }
                                ?>
                                <span style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['estado']); ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <div class="qa-action-group">
                                    <a href="evaluacion_formativa_detalle.php?id=<?php echo $row['id']; ?>" class="qa-action-btn view" aria-label="Ver registro" title="Ver registro">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="evaluacion_formativa_form.php?id=<?php echo $row['id']; ?>" class="qa-action-btn edit" aria-label="Editar registro" title="Editar registro">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                    </a>
                                    <a href="evaluacion_formativa_acciones.php?action=delete&id=<?php echo $row['id']; ?>" class="qa-action-btn delete" aria-label="Eliminar registro" title="Eliminar registro" onclick="return confirm('¿Está seguro de eliminar esta evaluación?');">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>