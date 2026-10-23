<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
$tabla = [
    'codigo_tabla' => '',
    'nombre_prueba' => '',
    'modulo' => '',
    'objetivo' => '',
    'precondiciones' => '',
    'estado_general' => 'Pendiente',
    'observaciones' => '',
    'evidencia' => ''
];

$reglas_existentes = [];

// Generar código automático TD-001 si es nuevo
if (!$id) {
    try {
        $stmt = $pdo->query("SELECT codigo_tabla FROM qa_tabla_decision ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last && preg_match('/TD-(\d+)/', $last['codigo_tabla'], $m)) {
            $num = intval($m[1]) + 1;
            $tabla['codigo_tabla'] = 'TD-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } else {
            $tabla['codigo_tabla'] = 'TD-001';
        }
    } catch (Exception $e) {
        $tabla['codigo_tabla'] = 'TD-001';
    }

    // Reglas de ejemplo por defecto (basadas en autenticación / reglas de negocio clínicas)
    $reglas_existentes = [
        ['regla_num' => 1, 'cond' => ['Usuario registrado' => 'Sí', 'Contraseña correcta' => 'Sí'], 'accion' => 'Permitir acceso'],
        ['regla_num' => 2, 'cond' => ['Usuario registrado' => 'Sí', 'Contraseña correcta' => 'No'], 'accion' => 'Rechazar acceso'],
        ['regla_num' => 3, 'cond' => ['Usuario registrado' => 'No', 'Contraseña correcta' => 'Sí'], 'accion' => 'Rechazar acceso'],
        ['regla_num' => 4, 'cond' => ['Usuario registrado' => 'No', 'Contraseña correcta' => 'No'], 'accion' => 'Rechazar acceso']
    ];
} else {
    $stmt = $pdo->prepare("SELECT * FROM qa_tabla_decision WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $tabla = $found;
        $stmt_reglas = $pdo->prepare("SELECT * FROM qa_td_reglas_detalle WHERE tabla_id = ? ORDER BY regla_num ASC");
        $stmt_reglas->execute([$id]);
        while ($r = $stmt_reglas->fetch(PDO::FETCH_ASSOC)) {
            $reglas_existentes[] = [
                'regla_num' => $r['regla_num'],
                'cond' => json_decode($r['condiciones_json'], true),
                'accion' => $r['accion_esperada'],
                'resultado' => $r['resultado_obtenido'],
                'estado' => $r['estado_regla']
            ];
        }
    }
}

$titulo_pagina = $id ? 'Editar Tabla de Decisión' : 'Nueva Tabla de Decisión';
$subtitulo_pagina = 'Constructor de condiciones, reglas de decisión y acciones de caja negra.';
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo $id ? 'Editar Tabla: ' . htmlspecialchars($tabla['codigo_tabla']) : 'Crear Nueva Tabla de Decisión'; ?></h2>
    <a href="tabla_decision.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Regresar</a>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <form action="tabla_decision_acciones.php" method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">ID de la Tabla <span style="color: red;">*</span></label>
                <input type="text" name="codigo_tabla" value="<?php echo htmlspecialchars($tabla['codigo_tabla']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Nombre de la Prueba <span style="color: red;">*</span></label>
                <input type="text" name="nombre_prueba" value="<?php echo htmlspecialchars($tabla['nombre_prueba']); ?>" placeholder="Ej. Validación de Credenciales Clínicas" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Módulo / Funcionalidad <span style="color: red;">*</span></label>
                <select name="modulo" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;" required>
                    <option value="">Seleccione un módulo real...</option>
                    <option value="Gestión de pacientes" <?php if($tabla['modulo']=='Gestión de pacientes') echo 'selected'; ?>>Gestión de pacientes</option>
                    <option value="Búsqueda de pacientes" <?php if($tabla['modulo']=='Búsqueda de pacientes') echo 'selected'; ?>>Búsqueda de pacientes</option>
                    <option value="Consulta de doctores y especialidades" <?php if($tabla['modulo']=='Consulta de doctores y especialidades') echo 'selected'; ?>>Consulta de doctores y especialidades</option>
                    <option value="Comunicación SOAP (bus.php)" <?php if($tabla['modulo']=='Comunicación SOAP (bus.php)') echo 'selected'; ?>>Comunicación SOAP (bus.php)</option>
                    <option value="Historial de búsquedas" <?php if($tabla['modulo']=='Historial de búsquedas') echo 'selected'; ?>>Historial de búsquedas</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Objetivo <span style="color: red;">*</span></label>
            <textarea name="objetivo" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required><?php echo htmlspecialchars($tabla['objetivo']); ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Precondiciones</label>
                <textarea name="precondiciones" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($tabla['precondiciones']); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Observaciones</label>
                <textarea name="observaciones" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($tabla['observaciones']); ?></textarea>
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;">

        <!-- CONSTRUCTOR VISUAL DE REGLAS Y CONDICIONES -->
        <h3 style="font-size: 1.1rem; color: #0f172a; margin-bottom: 12px;">Constructor de Reglas de Decisión</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 16px;">Defina las combinaciones de condiciones y la acción esperada para cada regla de la técnica de caja negra.</p>

        <div style="overflow-x: auto; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;" id="tablaConstructor">
                <thead>
                    <tr style="background-color: #f1f5f9; color: #1e293b;">
                        <th style="padding: 10px; border: 1px solid #cbd5e1;">Elemento / Regla</th>
                        <?php foreach ($reglas_existentes as $r): ?>
                            <th style="padding: 10px; border: 1px solid #cbd5e1; text-align: center;">Regla <?php echo $r['regla_num']; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener lista de condiciones dinámicas únicas del arreglo
                    $condiciones_keys = array_keys($reglas_existentes[0]['cond'] ?? ['Condición 1' => 'Sí']);
                    foreach ($condiciones_keys as $c_idx => $c_name):
                    ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #cbd5e1; font-weight: 600; background: #f8fafc;">
                            <input type="text" name="condicion_nombre[]" value="<?php echo htmlspecialchars($c_name); ?>" style="width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px;" required>
                        </td>
                        <?php foreach ($reglas_existentes as $r_idx => $r): ?>
                            <td style="padding: 10px; border: 1px solid #cbd5e1; text-align: center;">
                                <input type="text" name="regla_val[<?php echo $r_idx; ?>][]" value="<?php echo htmlspecialchars($r['cond'][$c_name] ?? 'Sí'); ?>" style="width: 80px; padding: 6px; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px;" required>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                    <!-- Fila de Acción Esperada -->
                    <tr>
                        <td style="padding: 10px; border: 1px solid #cbd5e1; font-weight: 600; background: #e2e8f0;">Acción Esperada</td>
                        <?php foreach ($reglas_existentes as $r_idx => $r): ?>
                            <td style="padding: 10px; border: 1px solid #cbd5e1; text-align: center; background: #f8fafc;">
                                <input type="text" name="accion_esperada[]" value="<?php echo htmlspecialchars($r['accion']); ?>" style="width: 100%; padding: 6px; text-align: center; border: 1px solid #cbd5e1; border-radius: 4px;" required>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <a href="tabla_decision.php" style="background: #f1f5f9; color: #475569; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600;">Cancelar</a>
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Guardar Tabla de Decisión</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>