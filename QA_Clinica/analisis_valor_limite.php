<?php
session_start();
require_once(__DIR__ . '/../config.php');

// Título de la página que consume el header existente
$titulo_pagina = 'Análisis de Valor Límite';
$subtitulo_pagina = 'Técnica de caja negra para verificar los límites de los datos de entrada';

include 'includes/header.php';

// Consultar estadísticas reales
$stats = ['total' => 0, 'aprobado' => 0, 'fallido' => 0, 'pendiente' => 0];
try {
    $stmt = $pdo->query("SELECT estado, COUNT(*) as count FROM qa_analisis_valor_limite GROUP BY estado");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $estado = strtolower($row['estado']);
        if (isset($stats[$estado])) {
            $stats[$estado] = $row['count'];
        }
        $stats['total'] += $row['count'];
    }
} catch (Exception $e) {
    // Si la tabla no existe aún
}

// Filtros y búsqueda
$search = $_GET['search'] ?? '';
$filtro_modulo = $_GET['modulo'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$sql = "SELECT * FROM qa_analisis_valor_limite WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (codigo_analisis LIKE ? OR campo_evaluado LIKE ? OR modulo LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($filtro_modulo) && $filtro_modulo != 'ALL') {
    $sql .= " AND modulo = ?";
    $params[] = $filtro_modulo;
}
if (!empty($filtro_estado) && $filtro_estado != 'ALL') {
    $sql .= " AND estado = ?";
    $params[] = $filtro_estado;
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $analisis_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $analisis_list = [];
}
?>

<div class="header-pagina" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 700;">Análisis de Valor Límite</h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Técnica de caja negra para verificar los límites de los datos de entrada</p>
    </div>
    <div>
        <a href="analisis_valor_limite_form.php" class="btn btn-primary" style="background-color: #3b82f6; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            + Nuevo análisis
        </a>
    </div>
</div>

<!-- Tarjetas estadísticas idénticas al diseño de la Matriz de Clases -->
<div class="tarjetas-resumen" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="tarjeta" style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Análisis registrados</span>
        <strong style="font-size: 1.8rem; color: #1e293b; display: block;"><?php echo $stats['total']; ?></strong>
    </div>
    <div class="tarjeta" style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Aprobados</span>
        <strong style="font-size: 1.8rem; color: #10b981; display: block;"><?php echo $stats['aprobado']; ?></strong>
    </div>
    <div class="tarjeta" style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #ef4444; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Fallidos</span>
        <strong style="font-size: 1.8rem; color: #ef4444; display: block;"><?php echo $stats['fallido']; ?></strong>
    </div>
    <div class="tarjeta" style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">Pendientes</span>
        <strong style="font-size: 1.8rem; color: #f59e0b; display: block;"><?php echo $stats['pendiente']; ?></strong>
    </div>
</div>

<!-- Bloque de Búsqueda y Filtros -->
<div class="bloque-card" style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 1.1rem; color: #0f172a;">Buscar análisis</h3>
    
    <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 240px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Buscar por código o campo</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ej. AVL-001 o edad" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
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
            <a href="analisis_valor_limite.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center;">Limpiar filtros</a>
        </div>
    </form>
</div>

<!-- Tabla de Registros -->
<div class="bloque-card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
        <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a;">Análisis registrados</h3>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px 16px; font-weight: 600;">Código</th>
                    <th style="padding: 12px 16px; font-weight: 600;">Módulo</th>
                    <th style="padding: 12px 16px; font-weight: 600;">Campo Evaluado</th>
                    <th style="padding: 12px 16px; font-weight: 600;">Mín / Máx</th>
                    <th style="padding: 12px 16px; font-weight: 600;">Estado</th>
                    <th style="padding: 12px 16px; font-weight: 600; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($analisis_list)): ?>
                    <tr>
                        <td colspan="6" style="padding: 32px; text-align: center; color: #94a3b8;">
                            No hay análisis registrados. Haz clic en "<strong>+ Nuevo análisis</strong>" para comenzar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($analisis_list as $row): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($row['codigo_analisis']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['modulo']); ?></td>
                            <td style="padding: 14px 16px; color: #0f172a;"><code><?php echo htmlspecialchars($row['campo_evaluado']); ?></code></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($row['valor_minimo'] ?? 'N/A'); ?> / <?php echo htmlspecialchars($row['valor_maximo'] ?? 'N/A'); ?></td>
                            <td style="padding: 14px 16px;">
                                <?php 
                                    $bg = '#fef3c7'; $color = '#92400e';
                                    if ($row['estado'] == 'Aprobado') { $bg = '#d1fae5'; $color = '#065f46'; }
                                    if ($row['estado'] == 'Fallido') { $bg = '#fee2e2'; $color = '#991b1b'; }
                                ?>
                                <span style="background-color: <?php echo $bg; ?>; color: <?php echo $color; ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($row['estado']); ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <div class="qa-action-group">
                                    <a href="analisis_valor_limite_detalle.php?id=<?php echo $row['id']; ?>" class="qa-action-btn view" aria-label="Ver registro" title="Ver registro">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a href="analisis_valor_limite_form.php?id=<?php echo $row['id']; ?>" class="qa-action-btn edit" aria-label="Editar registro" title="Editar registro">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </a>
                                    <a href="analisis_valor_limite_acciones.php?action=delete&id=<?php echo $row['id']; ?>" class="qa-action-btn delete" aria-label="Eliminar registro" title="Eliminar registro" onclick="return confirm('¿Desea eliminar este registro?');">
                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
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