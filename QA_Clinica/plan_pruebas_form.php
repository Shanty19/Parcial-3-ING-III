<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
$plan = [
    'codigo_plan' => '',
    'nombre_plan' => '',
    'proyecto' => 'Clínica Salud+',
    'version_sistema' => 'v1.0',
    'responsable' => 'Tester QA',
    'fecha_elaboracion' => date('Y-m-d'),
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin' => date('Y-m-d', strtotime('+7 days')),
    'estado' => 'En preparación',
    'objetivo_general' => '',
    'objetivos_especificos' => '',
    'alcance' => '',
    'fuera_alcance' => '',
    'criterios_aceptacion' => '',
    'entorno_pruebas' => 'Ambiente Local / XAMPP',
    'herramientas' => 'PHPUnit, Postman, Navegador Web',
    'requisitos_software' => 'PHP 8.x, MySQL, Servidor Apache',
    'requisitos_hardware' => 'PC Estándar de desarrollo',
    'datos_prueba' => 'Pacientes de prueba precargados en BD',
    'observaciones_entorno' => '',
    'criterios_entrada' => 'Servidor activo, base de datos migrada y casos documentados.',
    'criterios_salida' => '100% de casos críticos ejecutados y revisión de incidencias.'
];

$actividades = [];
$riesgos = [];

// Generar código automático PP-001 si es nuevo
if (!$id) {
    try {
        $stmt = $pdo->query("SELECT codigo_plan FROM qa_plan_pruebas ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($last && preg_match('/PP-(\d+)/', $last['codigo_plan'], $m)) {
            $num = intval($m[1]) + 1;
            $plan['codigo_plan'] = 'PP-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } else {
            $plan['codigo_plan'] = 'PP-001';
        }
    } catch (Exception $e) {
        $plan['codigo_plan'] = 'PP-001';
    }

    // Actividades iniciales por defecto
    $actividades = [
        ['nombre' => 'Preparación de entorno', 'desc' => 'Configurar servidor y bases de datos', 'resp' => 'Tester QA', 'inicio' => date('Y-m-d'), 'fin' => date('Y-m-d'), 'estado' => 'Pendiente'],
        ['nombre' => 'Ejecución de pruebas funcionales', 'desc' => 'Probar módulos de pacientes y SOAP', 'resp' => 'Tester QA', 'inicio' => date('Y-m-d'), 'fin' => date('Y-m-d', strtotime('+3 days')), 'estado' => 'Pendiente']
    ];

    $riesgos = [
        ['codigo' => 'R-01', 'desc' => 'Inestabilidad en el servicio web SOAP externo', 'prob' => 'Media', 'impacto' => 'Alto', 'mitigacion' => 'Utilizar mocks o entorno local de respaldo', 'resp' => 'Tester QA', 'estado' => 'Abierto']
    ];
} else {
    $stmt = $pdo->prepare("SELECT * FROM qa_plan_pruebas WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $plan = $found;
        
        $stmt_act = $pdo->prepare("SELECT * FROM qa_plan_actividades WHERE plan_id = ?");
        $stmt_act->execute([$id]);
        while($a = $stmt_act->fetch(PDO::FETCH_ASSOC)) {
            $actividades[] = ['nombre' => $a['nombre_actividad'], 'desc' => $a['descripcion'], 'resp' => $a['responsable'], 'inicio' => $a['fecha_inicio'], 'fin' => $a['fecha_fin'], 'estado' => $a['estado_actividad']];
        }

        $stmt_ries = $pdo->prepare("SELECT * FROM qa_plan_riesgos WHERE plan_id = ?");
        $stmt_ries->execute([$id]);
        while($r = $stmt_ries->fetch(PDO::FETCH_ASSOC)) {
            $riesgos[] = ['codigo' => $r['codigo_riesgo'], 'desc' => $r['descripcion_riesgo'], 'prob' => $r['probabilidad'], 'impacto' => $r['impacto'], 'mitigacion' => $r['accion_mitigacion'], 'resp' => $r['responsable'], 'estado' => $r['estado_riesgo']];
        }
    }
}

$titulo_pagina = $id ? 'Editar Plan de Pruebas' : 'Crear Plan de Pruebas';
$subtitulo_pagina = 'Formulario maestro de planificación y gestión de calidad.';
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo $id ? 'Editar Plan: ' . htmlspecialchars($plan['codigo_plan']) : 'Nuevo Plan de Pruebas del Proyecto'; ?></h2>
    <a href="plan_pruebas.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Regresar</a>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <form action="plan_pruebas_acciones.php" method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">

        <!-- SECCIÓN A: Información General -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">A. Información General</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">ID del Plan</label>
                <input type="text" name="codigo_plan" value="<?php echo htmlspecialchars($plan['codigo_plan']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Nombre del Plan <span style="color: red;">*</span></label>
                <input type="text" name="nombre_plan" value="<?php echo htmlspecialchars($plan['nombre_plan']); ?>" placeholder="Ej. Plan de Pruebas Funcionales v1.0" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Proyecto</label>
                <input type="text" name="proyecto" value="<?php echo htmlspecialchars($plan['proyecto']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Versión del Sistema</label>
                <input type="text" name="version_sistema" value="<?php echo htmlspecialchars($plan['version_sistema']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Responsable del Plan</label>
                <input type="text" name="responsable" value="<?php echo htmlspecialchars($plan['responsable']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Fecha de Inicio</label>
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($plan['fecha_inicio']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Fecha de Finalización</label>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($plan['fecha_fin']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Estado del Plan</label>
                <select name="estado" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                    <option value="En preparación" <?php if($plan['estado']=='En preparación') echo 'selected'; ?>>En preparación</option>
                    <option value="Aprobado" <?php if($plan['estado']=='Aprobado') echo 'selected'; ?>>Aprobado</option>
                    <option value="En ejecución" <?php if($plan['estado']=='En ejecución') echo 'selected'; ?>>En ejecución</option>
                    <option value="Completado" <?php if($plan['estado']=='Completado') echo 'selected'; ?>>Completado</option>
                    <option value="Cancelado" <?php if($plan['estado']=='Cancelado') echo 'selected'; ?>>Cancelado</option>
                </select>
            </div>
        </div>

        <!-- SECCIÓN B: Objetivos y Alcance -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">B. Objetivos y Alcance</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Objetivo General <span style="color: red;">*</span></label>
                <textarea name="objetivo_general" rows="3" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required><?php echo htmlspecialchars($plan['objetivo_general']); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Alcance</label>
                <textarea name="alcance" rows="3" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required><?php echo htmlspecialchars($plan['alcance']); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Fuera del Alcance</label>
                <textarea name="fuera_alcance" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($plan['fuera_alcance']); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Criterios de Aceptación</label>
                <textarea name="criterios_aceptacion" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($plan['criterios_aceptacion']); ?></textarea>
            </div>
        </div>

        <!-- SECCIÓN C & D: Módulos y Estrategias -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">C & D. Módulos y Estrategias de Testing</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Módulos del Sistema a Evaluar</label>
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; background: #f8fafc;">
                    <?php 
                    $modulos_posibles = ['Gestión de pacientes', 'Búsqueda de pacientes', 'Consulta de doctores y especialidades', 'Comunicación SOAP (bus.php)', 'Historial de búsquedas'];
                    $modulos_guardados = explode(',', $plan['modulos_seleccionados'] ?? '');
                    foreach ($modulos_posibles as $mod):
                    ?>
                        <label style="display: block; margin-bottom: 6px; font-size: 0.9rem;">
                            <input type="checkbox" name="modulos[]" value="<?php echo $mod; ?>" <?php if(in_array($mod, $modulos_guardados)) echo 'checked'; ?>> <?php echo $mod; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Estrategias y Técnicas de Pruebas</label>
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; background: #f8fafc;">
                    <?php 
                    $estrategias_posibles = ['Pruebas Funcionales', 'Partición de Equivalencia', 'Análisis de Valor Límite', 'Tabla de Decisión', 'Pruebas de Integración SOAP'];
                    $est_guardadas = explode(',', $plan['estrategias_seleccionadas'] ?? '');
                    foreach ($estrategias_posibles as $est):
                    ?>
                        <label style="display: block; margin-bottom: 6px; font-size: 0.9rem;">
                            <input type="checkbox" name="estrategias[]" value="<?php echo $est; ?>" <?php if(in_array($est, $est_guardadas)) echo 'checked'; ?>> <?php echo $est; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- SECCIÓN E: Recursos y Entorno -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">E. Recursos y Entorno de Pruebas</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Entorno de Pruebas</label>
                <input type="text" name="entorno_pruebas" value="<?php echo htmlspecialchars($plan['entorno_pruebas']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Herramientas</label>
                <input type="text" name="herramientas" value="<?php echo htmlspecialchars($plan['herramientas']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Requisitos de Software</label>
                <input type="text" name="requisitos_software" value="<?php echo htmlspecialchars($plan['requisitos_software']); ?>" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
            </div>
        </div>

        <!-- SECCIÓN H: Criterios de Entrada y Salida -->
        <h3 style="font-size: 1.1rem; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">H. Criterios de Entrada y Salida</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Criterios de Entrada</label>
                <textarea name="criterios_entrada" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($plan['criterios_entrada']); ?></textarea>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Criterios de Salida</label>
                <textarea name="criterios_salida" rows="2" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"><?php echo htmlspecialchars($plan['criterios_salida']); ?></textarea>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <a href="plan_pruebas.php" style="background: #f1f5f9; color: #475569; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600;">Cancelar</a>
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">Guardar Plan de Pruebas</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>