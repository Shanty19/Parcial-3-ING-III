<?php
session_start();
require_once __DIR__ . '/../config.php';

$titulo_pagina = 'Tabla de Decisión';
$subtitulo_pagina = 'Técnica de caja negra para validar combinaciones de condiciones y acciones';

include 'includes/header.php';

// Obtener estadísticas reales para las tarjetas de resumen
$stats = ['total' => 0, 'aprobado' => 0, 'fallido' => 0, 'pendiente' => 0];
try {
    $stmt = $pdo->query("SELECT estado_general, COUNT(*) as count FROM qa_tabla_decision GROUP BY estado_general");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $st = strtolower($row['estado_general']);
        if (isset($stats[$st])) $stats[$st] = $row['count'];
        $stats['total'] += $row['count'];
    }
} catch (Exception $e) {}

// Buscador y filtros
$search = $_GET['search'] ?? '';
$filtro_modulo = $_GET['modulo'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$sql = "SELECT * FROM qa_tabla_decision WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (codigo_tabla LIKE ? OR nombre_prueba LIKE ? OR modulo LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($filtro_modulo) && $filtro_modulo != 'ALL') {
    $sql .= " AND modulo = ?";
    $params[] = $filtro_modulo;
}
if (!empty($filtro_estado) && $filtro_estado != 'ALL') {
    $sql .= " AND estado_general = ?";
    $params[] = $filtro_estado;
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tablas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tablas_list = [];
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 700;">Tabla de Decisión</h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Técnica de caja negra para validar combinaciones de condiciones y acciones</p>
    </div>
    <div>
        <a href="tabla_decision_form.php" class="btn btn-primary" style="background-color: #3b82f6; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            + Nueva tabla de decisión
        </a>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Total de Tablas</span>
        <strong style="font-size: 1.8rem; color: #1e293b; display: block;"><?php echo $stats['total']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Aprobados</span>
        <strong style="font-size: 1.8rem; color: #10b981; display: block;"><?php echo $stats['aprobado']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #ef4444; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Fallidos</span>
        <strong style="font-size: 1.8rem; color: #ef4444; display: block;"><?php echo $stats['fallido']; ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Pendientes</span>
        <strong style="font-size: 1.8rem; color: #f59e0b; display: block;"><?php echo $stats['pendiente']; ?></strong>
    </div>
</div>

<!-- Buscador y Filtros -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 1.1rem; color: #0f172a;">Buscar tabla de decisión</h3>
    <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 240px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Buscar por código, nombre o módulo</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ej. TD-001 o Inicio de sesión" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Módulo</label>
            <select name="modulo" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los módulos</option>
                <option value="Gestión de pacientes" <?php if($filtro_modulo=='Gestión de pacientes') echo 'selected'; ?>>Gestión de pacientes</option>
                <option value="Búsqueda de pacientes" <?php if($filtro_modulo=='Búsqueda de pacientes') echo 'selected'; ?>>Búsqueda de pacientes</option>
                <option value="Consulta de doctores y especialidades" <?php if($filtro_modulo=='Consulta de doctores y especialidades') echo 'selected'; ?>>Consulta de doctores y especialidades</option>
                <option value="Comunicación SOAP (bus.php)" <?php if($filtro_modulo=='Comunicación SOAP (bus.php)') echo 'selected'; ?>>Comunicación SOAP (bus.php)</option>
                <option value="Historial de búsquedas" <?php if($filtro_modulo=='Historial de búsquedas') echo 'selected'; ?>>Historial de búsquedas</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">Buscar</button>
            <a href="tabla_decision.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center;">Limpiar filtros</a>
        </div>
    </form>
</div>

<!-- Tabla de Registros -->
<div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a;">Tablas de Decisión Registradas</h3>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px 16px;">Código</th>
                    <th style="padding: 12px 16px;">Nombre de la Prueba</th>
                    <th style="padding: 12px 16px;">Módulo</th>
                    <th style="padding: 12px 16px;">Objetivo</th>
                    <th style="padding: 12px 16px;">Estado</th>
                    <th style="padding: 12px 16px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tablas_list)): ?>
                    <tr>
                        <td colspan="6" style="padding: 32px; text-align: center; color: #94a3b8;">
                            No hay tablas de decisión registradas. Haz clic en "<strong>+ Nueva tabla de decisión</strong>" para crear una.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tablas_list as $row): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['codigo_tabla']); ?></td>
                            <td style="padding: 14px 16px; color: #0f172a; font-weight: 500;"><?php echo htmlspecialchars($row['nombre_prueba']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['modulo']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars(substr($row['objetivo'], 0, 45)); ?>...</td>
                            <td style="padding: 14px 16px;">
                                <?php 
                                    $bg = '#fef3c7'; $color = '#92400e';
                                    if ($row['estado_general'] == 'Aprobado') { $bg = '#d1fae5'; $color = '#065f46'; }
                                    if ($row['estado_general'] == 'Fallido') { $bg = '#fee2e2'; $color = '#991b1b'; }
                                ?>
                                <span style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['estado_general']); ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <a href="tabla_decision_detalle.php?id=<?php echo $row['id']; ?>" style="color: #0284c7; text-decoration: none; margin: 0 6px;" title="Ver detalle">👁️</a>
                                <a href="tabla_decision_form.php?id=<?php echo $row['id']; ?>" style="color: #d97706; text-decoration: none; margin: 0 6px;" title="Editar">✏️</a>
                                <a href="tabla_decision_acciones.php?action=delete&id=<?php echo $row['id']; ?>" style="color: #dc2626; text-decoration: none; margin: 0 6px;" onclick="return confirm('¿Está seguro de eliminar esta tabla de decisión?');" title="Eliminar">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>