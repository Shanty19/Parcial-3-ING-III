<?php
session_start();
require_once __DIR__ . '/../config.php';

$titulo_pagina = 'Plan de Pruebas del Proyecto';
$subtitulo_pagina = 'Planificación, organización y seguimiento de las pruebas de calidad de Clínica Salud+';

include 'includes/header.php';

// Estadísticas reales para las tarjetas de resumen
$stats = ['total' => 0, 'preparacion' => 0, 'ejecucion' => 0, 'completado' => 0];
try {
    $stmt = $pdo->query("SELECT estado, COUNT(*) as count FROM qa_plan_pruebas GROUP BY estado");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $st = $row['estado'];
        if ($st == 'En preparación') $stats['preparacion'] += $row['count'];
        if ($st == 'En ejecución') $stats['ejecucion'] += $row['count'];
        if ($st == 'Completado') $stats['completado'] += $row['count'];
        $stats['total'] += $row['count'];
    }
} catch (Exception $e) {}

// Buscador y filtros
$search = $_GET['search'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$sql = "SELECT * FROM qa_plan_pruebas WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (codigo_plan LIKE ? OR nombre_plan LIKE ? OR responsable LIKE ?)";
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
    $planes_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $planes_list = [];
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 700;">Plan de Pruebas del Proyecto</h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Planificación, organización y seguimiento de las pruebas de calidad de Clínica Salud+</p>
    </div>
    <div>
        <a href="plan_pruebas_form.php" style="background-color: #3b82f6; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            + Crear plan de pruebas
        </a>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Planes Registrados</span>
        <strong style="font-size: 1.8rem; color: #1e293b; display: block;"><?php echo $stats['total']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">En Preparación</span>
        <strong style="font-size: 1.8rem; color: #f59e0b; display: block;"><?php echo $stats['preparacion']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #0284c7; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">En Ejecución</span>
        <strong style="font-size: 1.8rem; color: #0284c7; display: block;"><?php echo $stats['ejecucion']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Completados</span>
        <strong style="font-size: 1.8rem; color: #10b981; display: block;"><?php echo $stats['completado']; ?></strong>
    </div>
</div>

<!-- Buscador y Filtros -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 1.1rem; color: #0f172a;">Buscar planes de prueba</h3>
    <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 240px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Buscar por código, nombre o responsable</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ej. PP-001 o Tester QA" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Estado</label>
            <select name="estado" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los estados</option>
                <option value="En preparación" <?php if($filtro_estado=='En preparación') echo 'selected'; ?>>En preparación</option>
                <option value="Aprobado" <?php if($filtro_estado=='Aprobado') echo 'selected'; ?>>Aprobado</option>
                <option value="En ejecución" <?php if($filtro_estado=='En ejecución') echo 'selected'; ?>>En ejecución</option>
                <option value="Completado" <?php if($filtro_estado=='Completado') echo 'selected'; ?>>Completado</option>
                <option value="Cancelado" <?php if($filtro_estado=='Cancelado') echo 'selected'; ?>>Cancelado</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">Buscar</button>
            <a href="plan_pruebas.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center;">Limpiar filtros</a>
        </div>
    </form>
</div>

<!-- Tabla de Planes -->
<div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a;">Planes Registrados</h3>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px 16px;">Código</th>
                    <th style="padding: 12px 16px;">Nombre del Plan</th>
                    <th style="padding: 12px 16px;">Responsable</th>
                    <th style="padding: 12px 16px;">Fecha Inicio</th>
                    <th style="padding: 12px 16px;">Fecha Fin</th>
                    <th style="padding: 12px 16px;">Estado</th>
                    <th style="padding: 12px 16px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($planes_list)): ?>
                    <tr>
                        <td colspan="7" style="padding: 32px; text-align: center; color: #94a3b8;">
                            No hay planes de prueba registrados. Haz clic en "<strong>+ Crear plan de pruebas</strong>" para comenzar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($planes_list as $row): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['codigo_plan']); ?></td>
                            <td style="padding: 14px 16px; color: #0f172a; font-weight: 500;"><?php echo htmlspecialchars($row['nombre_plan']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['responsable']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['fecha_inicio']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['fecha_fin']); ?></td>
                            <td style="padding: 14px 16px;">
                                <?php 
                                    $bg = '#fef3c7'; $color = '#92400e';
                                    if ($row['estado'] == 'Completado') { $bg = '#d1fae5'; $color = '#065f46'; }
                                    if ($row['estado'] == 'En ejecución') { $bg = '#e0f2fe'; $color = '#0369a1'; }
                                    if ($row['estado'] == 'Aprobado') { $bg = '#f0fdf4'; $color = '#15803d'; }
                                    if ($row['estado'] == 'Cancelado') { $bg = '#fee2e2'; $color = '#991b1b'; }
                                ?>
                                <span style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['estado']); ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <div class="qa-action-group">
                                    <a href="plan_pruebas_detalle.php?id=<?php echo $row['id']; ?>" class="qa-action-btn view" aria-label="Ver registro" title="Ver registro">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="plan_pruebas_form.php?id=<?php echo $row['id']; ?>" class="qa-action-btn edit" aria-label="Editar registro" title="Editar registro">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                    </a>
                                    <a href="plan_pruebas_acciones.php?action=delete&id=<?php echo $row['id']; ?>" class="qa-action-btn delete" aria-label="Eliminar registro" title="Eliminar registro" onclick="return confirm('¿Está seguro de eliminar este plan de pruebas?');">
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