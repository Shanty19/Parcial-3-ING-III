<?php
session_start();
require_once __DIR__ . '/../config.php';

$titulo_pagina = 'Casos de prueba';
$subtitulo_pagina = 'Gestión y documentación de los casos de prueba de Clínica Salud+.';

include 'includes/header.php';

// Estadísticas reales desde la BD
$stats = ['total' => 0, 'aprobados' => 0, 'fallidos' => 0, 'pendientes' => 0];
try {
    $stmt_stats = $pdo->query("SELECT estado, COUNT(*) as count FROM qa_casos_prueba GROUP BY estado");
    while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
        if ($row['estado'] == 'Aprobado') $stats['aprobados'] += $row['count'];
        if ($row['estado'] == 'Fallido') $stats['fallidos'] += $row['count'];
        if ($row['estado'] == 'Pendiente') $stats['pendientes'] += $row['count'];
        $stats['total'] += $row['count'];
    }
} catch (Exception $e) {}

$aprobado_pct = $stats['total'] > 0 ? round(($stats['aprobados'] / $stats['total']) * 100) : 0;
$fallido_pct = $stats['total'] > 0 ? round(($stats['fallidos'] / $stats['total']) * 100) : 0;

// Generar código automático CP-00X
$proximo_codigo = 'CP-001';
try {
    $stmt_code = $pdo->query("SELECT codigo FROM qa_casos_prueba ORDER BY id DESC LIMIT 1");
    $last_case = $stmt_code->fetch(PDO::FETCH_ASSOC);
    if ($last_case && preg_match('/CP-(\d+)/', $last_case['codigo'], $m)) {
        $next_num = intval($m[1]) + 1;
        $proximo_codigo = 'CP-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
    }
} catch (Exception $e) {}

// Filtros y búsqueda
$search = $_GET['search'] ?? '';
$filtro_modulo = $_GET['modulo'] ?? 'ALL';
$filtro_estado = $_GET['estado'] ?? 'ALL';
$filtro_prioridad = $_GET['prioridad'] ?? 'ALL';

$sql = "SELECT * FROM qa_casos_prueba WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (codigo LIKE ? OR nombre_caso LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filtro_modulo != 'ALL') {
    $sql .= " AND modulo = ?";
    $params[] = $filtro_modulo;
}
if ($filtro_estado != 'ALL') {
    $sql .= " AND estado = ?";
    $params[] = $filtro_estado;
}
if ($filtro_prioridad != 'ALL') {
    $sql .= " AND prioridad = ?";
    $params[] = $filtro_prioridad;
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $casos_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $casos_lista = [];
}
?>

<!-- Content Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 style="margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 700;">Casos de prueba</h1>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Gestión y documentación de los casos de prueba de Clínica Salud+.</p>
    </div>
    <div>
        <button type="button" onclick="openNewModal()" style="background-color: #3b82f6; color: white; padding: 10px 18px; border-radius: 6px; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i class="fa-solid fa-plus"></i> Nuevo caso de prueba
        </button>
    </div>
</div>

<!-- Metrics Cards Summary -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;"><i class="fa-solid fa-list-check" style="color: #3b82f6; margin-right: 6px;"></i> Total de Casos</span>
        <strong style="font-size: 1.8rem; color: #1e293b; display: block;"><?php echo $stats['total']; ?></strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;">Casos documentados</span>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;"><i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 6px;"></i> Aprobados</span>
        <strong style="font-size: 1.8rem; color: #10b981; display: block;"><?php echo $stats['aprobados']; ?></strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;"><?php echo $aprobado_pct; ?>% del total</span>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #ef4444; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;"><i class="fa-solid fa-circle-xmark" style="color: #ef4444; margin-right: 6px;"></i> Fallidos</span>
        <strong style="font-size: 1.8rem; color: #ef4444; display: block;"><?php echo $stats['fallidos']; ?></strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;"><?php echo $fallido_pct; ?>% del total</span>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;"><i class="fa-solid fa-clock" style="color: #f59e0b; margin-right: 6px;"></i> Pendientes</span>
        <strong style="font-size: 1.8rem; color: #f59e0b; display: block;"><?php echo $stats['pendientes']; ?></strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;">Sin ejecutar</span>
    </div>
</div>

<!-- Search and Filters Section -->
<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 240px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-magnifying-glass"></i> Buscar</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Buscar por código o nombre del caso..." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div style="flex: 1; min-width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-cubes"></i> Módulo</label>
            <select name="modulo" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los módulos</option>
                <option value="Gestión de pacientes" <?php if($filtro_modulo=='Gestión de pacientes') echo 'selected'; ?>>Gestión de pacientes</option>
                <option value="Búsqueda de pacientes" <?php if($filtro_modulo=='Búsqueda de pacientes') echo 'selected'; ?>>Búsqueda de pacientes</option>
                <option value="Consulta de doctores y especialidades" <?php if($filtro_modulo=='Consulta de doctores y especialidades') echo 'selected'; ?>>Consulta de doctores y especialidades</option>
                <option value="Comunicación con el servicio SOAP" <?php if($filtro_modulo=='Comunicación con el servicio SOAP') echo 'selected'; ?>>Comunicación con el servicio SOAP</option>
                <option value="Historial de búsquedas" <?php if($filtro_modulo=='Historial de búsquedas') echo 'selected'; ?>>Historial de búsquedas</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-flag"></i> Estado</label>
            <select name="estado" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los estados</option>
                <option value="Pendiente" <?php if($filtro_estado=='Pendiente') echo 'selected'; ?>>Pendiente</option>
                <option value="Aprobado" <?php if($filtro_estado=='Aprobado') echo 'selected'; ?>>Aprobado</option>
                <option value="Fallido" <?php if($filtro_estado=='Fallido') echo 'selected'; ?>>Fallido</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 130px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-arrow-up-wide-short"></i> Prioridad</label>
            <select name="prioridad" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todas</option>
                <option value="Alta" <?php if($filtro_prioridad=='Alta') echo 'selected'; ?>>Alta</option>
                <option value="Media" <?php if($filtro_prioridad=='Media') echo 'selected'; ?>>Media</option>
                <option value="Baja" <?php if($filtro_prioridad=='Baja') echo 'selected'; ?>>Baja</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">Buscar</button>
            <a href="casos_prueba.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center;">Limpiar</a>
        </div>
    </form>
</div>

<!-- Test Cases Table Card -->
<div style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px 16px;">Código</th>
                    <th style="padding: 12px 16px;">Nombre del caso</th>
                    <th style="padding: 12px 16px;">Módulo</th>
                    <th style="padding: 12px 16px;">Prioridad</th>
                    <th style="padding: 12px 16px;">Estado</th>
                    <th style="padding: 12px 16px;">Fecha</th>
                    <th style="padding: 12px 16px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($casos_lista)): ?>
                    <tr>
                        <td colspan="7" style="padding: 32px; text-align: center; color: #94a3b8;">
                            No hay casos de prueba registrados en la base de datos. Haz clic en "<strong>+ Nuevo caso de prueba</strong>" para agregar uno.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($casos_lista as $tc): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($tc['codigo']); ?></td>
                            <td style="padding: 14px 16px; color: #0f172a; font-weight: 500;"><?php echo htmlspecialchars($tc['nombre_caso']); ?></td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($tc['modulo']); ?></td>
                            <td style="padding: 14px 16px;">
                                <?php 
                                    $p_bg = '#f3f4f6'; $p_col = '#4b5563';
                                    if ($tc['prioridad'] == 'Alta') { $p_bg = '#fee2e2'; $p_col = '#991b1b'; }
                                    if ($tc['prioridad'] == 'Media') { $p_bg = '#fef3c7'; $p_col = '#92400e'; }
                                ?>
                                <span style="background: <?php echo $p_bg; ?>; color: <?php echo $p_col; ?>; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;"><?php echo $tc['prioridad']; ?></span>
                            </td>
                            <td style="padding: 14px 16px;">
                                <?php 
                                    $s_bg = '#e0f2fe'; $s_col = '#0369a1';
                                    if ($tc['estado'] == 'Aprobado') { $s_bg = '#d1fae5'; $s_col = '#065f46'; }
                                    if ($tc['estado'] == 'Fallido') { $s_bg = '#fee2e2'; $s_col = '#991b1b'; }
                                ?>
                                <span style="background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>; padding: 3px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600;"><?php echo $tc['estado']; ?></span>
                            </td>
                            <td style="padding: 14px 16px; color: #475569;"><?php echo htmlspecialchars($tc['fecha_creacion']); ?></td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <div class="qa-action-group">
                                    <button type="button" class="qa-action-btn view" onclick="window.location.href='casos_prueba_detalle.php?id=<?php echo $tc['id']; ?>'" aria-label="Ver registro" title="Ver registro">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="qa-action-btn edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($tc)); ?>)" aria-label="Editar registro" title="Editar registro">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </button>
                                    <a href="casos_prueba_acciones.php?action=delete&id=<?php echo $tc['id']; ?>" class="qa-action-btn delete" aria-label="Eliminar registro" title="Eliminar registro" onclick="return confirm('¿Está seguro de eliminar este caso?');">
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

<!-- Modal: Formulario para Crear / Editar Caso -->
<div id="modalForm" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(15, 23, 42, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; padding: 10px;">
    <div style="width: 100%; max-width: 750px; max-height: 92vh; background: #ffffff; border-radius: 10px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; overflow: hidden;">
        <div style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc; flex-shrink: 0;">
            <h3 id="modalFormTitle" style="margin: 0; font-size: 1.1rem; color: #1e293b;"><i class="fa-solid fa-vials"></i> Nuevo caso de prueba</h3>
            <button type="button" onclick="closeModal('modalForm')" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <form action="casos_prueba_acciones.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="caseId">
            <div style="padding: 16px 20px; overflow-y: auto; flex-grow: 1; max-height: calc(92vh - 120px);">
                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div style="flex: 0 0 calc(33.333% - 10px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Código *</label>
                        <input type="text" name="codigo" id="caseCode" value="<?php echo $proximo_codigo; ?>" readonly style="background-color: #f1f5f9; cursor: not-allowed; width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                    <div style="flex: 0 0 calc(66.666% - 5px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Nombre del caso *</label>
                        <input type="text" name="nombre_caso" id="caseName" placeholder="Ej: Registro exitoso de paciente" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div style="flex: 0 0 calc(33.333% - 10px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Módulo *</label>
                        <select name="modulo" id="caseModule" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                            <option value="Gestión de pacientes">Gestión de pacientes</option>
                            <option value="Búsqueda de pacientes">Búsqueda de pacientes</option>
                            <option value="Consulta de doctores y especialidades">Consulta de doctores y especialidades</option>
                            <option value="Comunicación con el servicio SOAP">Comunicación con el servicio SOAP</option>
                            <option value="Historial de búsquedas">Historial de búsquedas</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 calc(33.333% - 10px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Prioridad *</label>
                        <select name="prioridad" id="casePriority" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                            <option value="Alta">Alta</option>
                            <option value="Media" selected>Media</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 calc(33.333% - 10px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Estado *</label>
                        <select name="estado" id="caseStatus" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                            <option value="Pendiente" selected>Pendiente</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Fallido">Fallido</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Fecha</label>
                    <input type="date" name="fecha_creacion" id="caseDate" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Técnica utilizada *</label>
                    <select name="tecnica_utilizada" id="caseTechnique" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                        <option value="">Seleccione una técnica</option>
                        <option value="Caja negra">Caja negra</option>
                        <option value="Caja blanca">Caja blanca</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Objetivo de la prueba *</label>
                    <textarea name="objetivo" id="caseObjective" rows="2" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div style="flex: 0 0 calc(50% - 7.5px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Precondiciones</label>
                        <textarea name="precondiciones" id="casePreconditions" rows="2" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                    </div>
                    <div style="flex: 0 0 calc(50% - 7.5px);">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Datos de prueba</label>
                        <textarea name="datos_prueba" id="caseTestData" rows="2" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Pasos de ejecución *</label>
                    <textarea name="pasos" id="caseSteps" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Resultado esperado *</label>
                    <textarea name="resultado_esperado" id="caseExpected" rows="2" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Evidencias</label>
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px;">
                        <input type="file" id="evidenciasInput" name="evidencias[]" accept=".png,.jpg,.jpeg,image/png,image/jpeg" multiple style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; background: white;">
                        <small style="display: block; margin-top: 8px; color: #64748b;">Formatos permitidos: PNG, JPG y JPEG. Puede adjuntar una o varias imágenes.</small>
                        <input type="hidden" name="evidencias_actuales" id="evidenciasActuales" value='[]'>
                        <div id="evidenciasPreview" style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 12px;"></div>
                    </div>
                </div>
            </div>
            <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px; background-color: #f8fafc; flex-shrink: 0;">
                <button type="button" onclick="closeModal('modalForm')" style="background: #e2e8f0; color: #334155; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" style="background: #3b82f6; color: white; padding: 8px 18px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Guardar Caso de Prueba</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewModal() {
    caseEvidenceState = [];
    document.getElementById('caseId').value = '';
    document.getElementById('caseCode').value = '<?php echo $proximo_codigo; ?>';
    document.getElementById('caseName').value = '';
    document.getElementById('caseDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('caseTechnique').value = '';
    document.getElementById('caseObjective').value = '';
    document.getElementById('casePreconditions').value = '';
    document.getElementById('caseTestData').value = '';
    document.getElementById('caseSteps').value = '';
    document.getElementById('caseExpected').value = '';
    document.getElementById('evidenciasActuales').value = '[]';
    document.getElementById('evidenciasInput').value = '';
    document.getElementById('evidenciasPreview').innerHTML = '';
    document.getElementById('modalFormTitle').innerHTML = '<i class="fa-solid fa-vials"></i> Nuevo caso de prueba';
    document.getElementById('modalForm').style.display = 'flex';
}

function openEditModal(tc) {
    caseEvidenceState = [];
    document.getElementById('caseId').value = tc.id;
    document.getElementById('caseCode').value = tc.codigo;
    document.getElementById('caseName').value = tc.nombre_caso;
    document.getElementById('caseModule').value = tc.modulo;
    document.getElementById('casePriority').value = tc.prioridad;
    document.getElementById('caseStatus').value = tc.estado;
    document.getElementById('caseDate').value = tc.fecha_creacion ? tc.fecha_creacion.split(' ')[0] : '<?php echo date('Y-m-d'); ?>';
    document.getElementById('caseTechnique').value = tc.tecnica_utilizada || '';
    document.getElementById('caseObjective').value = tc.objetivo;
    document.getElementById('casePreconditions').value = tc.preconditions || '';
    document.getElementById('caseTestData').value = tc.datos_prueba || '';
    document.getElementById('caseSteps').value = tc.pasos;
    document.getElementById('caseExpected').value = tc.resultado_esperado;
    let evidencias = [];
    if (tc.evidencia) {
        try {
            evidencias = JSON.parse(tc.evidencia);
        } catch (error) {
            evidencias = [tc.evidencia];
        }
    }
    if (!Array.isArray(evidencias)) {
        evidencias = [evidencias];
    }
    document.getElementById('evidenciasActuales').value = JSON.stringify(evidencias);
    const previewBox = document.getElementById('evidenciasPreview');
    previewBox.innerHTML = '';
    evidencias.forEach((ruta) => {
        if (!ruta) return;
        const wrapper = document.createElement('div');
        wrapper.style.width = '170px';
        wrapper.style.border = '1px solid #e2e8f0';
        wrapper.style.borderRadius = '8px';
        wrapper.style.padding = '8px';
        wrapper.style.background = '#fff';
        const img = document.createElement('img');
        img.src = '../' + ruta.replace(/^\/+/, '');
        img.style.width = '100%';
        img.style.height = '120px';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '6px';
        img.alt = 'Evidencia';
        const label = document.createElement('div');
        label.style.marginTop = '8px';
        label.style.fontSize = '0.75rem';
        label.style.color = '#475569';
        label.style.wordBreak = 'break-word';
        label.textContent = ruta.split('/').pop();
        wrapper.appendChild(img); wrapper.appendChild(label); previewBox.appendChild(wrapper);
    });
    document.getElementById('modalFormTitle').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar caso: ' + tc.codigo;
    document.getElementById('modalForm').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

let caseEvidenceState = [];
(function () {
    const input = document.getElementById('evidenciasInput');
    const preview = document.getElementById('evidenciasPreview');
    const hidden = document.getElementById('evidenciasActuales');
    const allowedMimeTypes = ['image/png', 'image/jpeg'];

    function syncHidden() {
        hidden.value = JSON.stringify(caseEvidenceState.filter(item => item.type === 'existing').map(item => item.source));
    }
    function refreshInputFiles() {
        const dt = new DataTransfer();
        caseEvidenceState.filter(item => item.type === 'new').forEach(item => item.file && dt.items.add(item.file));
        input.files = dt.files;
    }
    function renderPreview() {
        preview.innerHTML = '';
        caseEvidenceState.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.style.width = '170px';
            wrapper.style.border = '1px solid #e2e8f0';
            wrapper.style.borderRadius = '8px';
            wrapper.style.padding = '8px';
            wrapper.style.background = '#fff';

            const img = document.createElement('img');
            const src = item.source.startsWith('http') ? item.source : '../' + item.source.replace(/^\/+/, '');
            img.src = src;
            img.alt = item.name;
            img.style.width = '100%';
            img.style.height = '120px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '6px';
            img.style.display = 'block';
            img.style.border = '1px solid #e2e8f0';

            const label = document.createElement('div');
            label.style.marginTop = '8px';
            label.style.fontSize = '0.75rem';
            label.style.color = '#475569';
            label.style.wordBreak = 'break-word';
            label.textContent = item.name;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = 'Eliminar';
            removeBtn.style.marginTop = '8px';
            removeBtn.style.width = '100%';
            removeBtn.style.border = '1px solid #ef4444';
            removeBtn.style.borderRadius = '6px';
            removeBtn.style.background = '#fff';
            removeBtn.style.color = '#b91c1c';
            removeBtn.style.cursor = 'pointer';
            removeBtn.addEventListener('click', function () {
                caseEvidenceState.splice(index, 1); syncHidden(); refreshInputFiles(); renderPreview();
            });

            wrapper.appendChild(img); wrapper.appendChild(label); wrapper.appendChild(removeBtn); preview.appendChild(wrapper);
        });
    }

    input.addEventListener('change', function () {
        const files = Array.from(this.files || []);
        const validFiles = [];
        files.forEach((file) => {
            const extOk = /\.(png|jpe?g)$/i.test(file.name);
            const typeOk = allowedMimeTypes.includes(file.type);
            if (!extOk || !typeOk) {
                alert('Solo se permiten archivos PNG, JPG o JPEG.');
                return;
            }
            validFiles.push(file);
        });
        validFiles.forEach((file) => caseEvidenceState.push({ type: 'new', source: URL.createObjectURL(file), name: file.name, file }));
        this.value = '';
        syncHidden(); refreshInputFiles(); renderPreview();
    });

    syncHidden(); renderPreview();
})();
</script>

<?php include 'includes/footer.php'; ?>