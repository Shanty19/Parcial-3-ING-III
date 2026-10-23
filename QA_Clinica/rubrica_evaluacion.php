<?php
$titulo_pestana    = "Rúbrica de Evaluación";
$titulo_pagina     = "Rúbrica de Evaluación";
$subtitulo_pagina  = "Criterios de calificación, ponderaciones y niveles de desempeño (Conectado a BD)";
include 'includes/header.php';

// Conexión a la Base de Datos
$host     = 'localhost';
$dbname   = 'clinica_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='padding:20px; color:red;'>Error de conexión a la base de datos: " . $e->getMessage() . "</div>");
}

// Procesar el formulario cuando se envía por POST (Guardar o Eliminar)
$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar') {
        try {
            $pdo->beginTransaction();

            $editId      = $_POST['editId'] ?? '';
            $code        = $_POST['code'];
            $module      = $_POST['module'];
            $title       = $_POST['title'];
            $description = $_POST['description'];

            if (!empty($editId)) {
                // Actualizar cabecera de la rúbrica
                $stmt = $pdo->prepare("UPDATE rubrica_evaluacion SET modulo = ?, titulo = ?, descripcion = ? WHERE id = ?");
                $stmt->execute([$module, $title, $description, $editId]);

                // Borrar criterios anteriores para volver a insertar los actualizados
                $stmtDel = $pdo->prepare("DELETE FROM rubrica_criterios WHERE rubrica_id = ?");
                $stmtDel->execute([$editId]);
                $rubricaId = $editId;
            } else {
                // Insertar nueva cabecera de rúbrica
                $stmt = $pdo->prepare("INSERT INTO rubrica_evaluacion (codigo, modulo, titulo, descripcion) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $module, $title, $description]);
                $rubricaId = $pdo->lastInsertId();
            }

            // Insertar Criterios asociados
            if (isset($_POST['criteria']) && is_array($_POST['criteria'])) {
                $stmtCrit = $pdo->prepare("INSERT INTO rubrica_criterios (rubrica_id, descripcion_criterio, puntaje_maximo) VALUES (?, ?, ?)");
                foreach ($_POST['criteria'] as $c) {
                    if (!empty($c['desc']) && !empty($c['points'])) {
                        $stmtCrit->execute([$rubricaId, $c['desc'], intval($c['points'])]);
                    }
                }
            }

            $pdo->commit();
            echo "<script>window.location.href='rubrica_evaluacion.php';</script>";
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje_alerta = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    } elseif ($_POST['accion'] === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        if (!empty($idEliminar)) {
            $stmt = $pdo->prepare("DELETE FROM rubrica_evaluacion WHERE id = ?");
            $stmt->execute([$idEliminar]);
            echo "<script>window.location.href='rubrica_evaluacion.php';</script>";
            exit;
        }
    }
}

// Consultar todas las rúbricas registradas en la Base de Datos
$rubricasFromDB = [];
try {
    $stmt = $pdo->query("SELECT * FROM rubrica_evaluacion ORDER BY id DESC");
    $rubricas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rubricas as $r) {
        $stmtC = $pdo->prepare("SELECT * FROM rubrica_criterios WHERE rubrica_id = ?");
        $stmtC->execute([$r['id']]);
        $criterios = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        $rubricasFromDB[] = [
            'id'          => $r['id'],
            'code'        => $r['codigo'],
            'module'      => $r['modulo'],
            'title'       => $r['titulo'],
            'description' => $r['descripcion'],
            'criteria'    => array_map(function($c) {
                return [
                    'desc'   => $c['descripcion_criterio'],
                    'points' => intval($c['puntaje_maximo'])
                ];
            }, $criterios)
        ];
    }
} catch (Exception $e) {
    $rubricasFromDB = [];
}
?>

<!-- Estilos específicos para la Rúbrica de Evaluación -->
<style>
:root {
    --navy: #102b49;
    --blue: #3979e8;
    --bg: #f3f6fc;
    --text: #18304d;
    --muted: #68778b;
    --border: #dce4ee;
    --green: #159b78;
    --red: #e34c54;
    --orange: #eea126;
    --purple: #8e44ad;
}

.main-content-inner {
    max-width: 1600px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

h1 {
    font-size: 28px;
    margin: 0 0 8px;
}

.subtitle {
    color: var(--muted);
    margin: 0;
    font-size: 14px;
}

button, input, select, textarea {
    font: inherit;
}

.btn-primary {
    background: var(--blue);
    color: white;
}

.btn-primary:hover {
    background: #2865ce;
}

.btn-light {
    background: #edf2f8;
    color: var(--text);
}

.btn-danger {
    background: #fff0f0;
    color: #c9333b;
    padding: 8px 11px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    border-top: 4px solid var(--blue);
}

.stat-card:nth-child(2) {
    border-top-color: var(--green);
}

.stat-card:nth-child(3) {
    border-top-color: var(--purple);
}

.stat-card:nth-child(4) {
    border-top-color: var(--orange);
}

.stat-label {
    color: var(--muted);
    font-size: 14px;
}

.stat-number {
    font-size: 30px;
    font-weight: 700;
    margin-top: 10px;
}

.panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 22px;
    overflow: hidden;
}

.panel-header {
    padding: 20px;
    border-bottom: 1px solid var(--border);
}

.panel-header h2 {
    font-size: 18px;
    margin: 0;
}

.filters {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 14px;
    padding: 20px;
    align-items: end;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

label {
    font-size: 13px;
    font-weight: 600;
}

input, select, textarea {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: var(--text);
    background: white;
    outline: none;
}

input:focus, select:focus, textarea:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px #3979e81a;
}

textarea {
    min-height: 90px;
    resize: vertical;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

thead {
    background: var(--navy);
    color: white;
}

th, td {
    text-align: left;
    padding: 15px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}

tbody tr:hover {
    background: #f7f9fd;
}

.badge {
    display: inline-block;
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
}

.badge-eval {
    background: #e0f2fe;
    color: #0369a1;
}

.actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 7px 9px;
    background: #edf2f8;
    color: var(--text);
    font-size: 12px;
}

.empty {
    text-align: center;
    padding: 35px;
    color: var(--muted);
}

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(12, 28, 48, .58);
    z-index: 1000;
    padding: 24px;
    overflow-y: auto;
    align-items: flex-start;
    justify-content: center;
}

.modal.open {
    display: flex;
}

.modal-content {
    background: white;
    width: 100%;
    max-width: 950px;
    margin: auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 15px 50px #0002;
}

.modal-header {
    background: var(--navy);
    color: white;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-btn {
    background: transparent;
    color: white;
    font-size: 25px;
    padding: 2px 8px;
    cursor: pointer;
    border: 0;
}

.modal-body {
    padding: 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.full {
    grid-column: 1 / -1;
}

.section-title {
    grid-column: 1 / -1;
    font-size: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
    margin: 5px 0 0;
}

.criteria-box {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 14px;
    background: #fbfcff;
}

.criteria-box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.criteria-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 12px;
}

@media (max-width: 900px) {
    .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .filters, .criteria-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="main-content-inner">

    <?php if (!empty($mensaje_alerta)): ?>
        <div style="background: #ffe5e5; color: #b52e35; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <?= htmlspecialchars($mensaje_alerta) ?>
        </div>
    <?php endif; ?>

    <header class="page-header">
        <div>
            <h1>Rúbrica de Evaluación</h1>
            <p class="subtitle">Definición de criterios de calidad y escalas de puntuación para pruebas</p>
        </div>

        <button class="btn-primary" type="button" onclick="openModal()">
            + Nueva rúbrica
        </button>
    </header>

    <section class="stats">
        <div class="stat-card">
            <div class="stat-label">Rúbricas registradas</div>
            <div class="stat-number" id="totalRubricas">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Criterios totales</div>
            <div class="stat-number" id="totalCriterios">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Puntaje Máximo Promedio</div>
            <div class="stat-number" id="promedioPuntaje">0 pts</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Módulos Evaluados</div>
            <div class="stat-number" id="totalModulos">0</div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Buscar rúbricas</h2>
        </div>

        <div class="filters">
            <div class="field">
                <label for="search">Buscar por código o título</label>
                <input id="search" placeholder="Ej. RB-001 o Evaluación de Casos" oninput="renderTable()">
            </div>

            <div class="field">
                <label for="filterModule">Módulo</label>
                <select id="filterModule" onchange="renderTable()">
                    <option value="">Todos los módulos</option>
                    <option>Gestión de pacientes</option>
                    <option>Pruebas funcionales</option>
                    <option>Control de calidad general</option>
                    <option>Seguridad y accesos</option>
                    <option>Otro</option>
                </select>
            </div>

            <button class="btn-light" type="button" onclick="clearFilters()">
                Limpiar filtros
            </button>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Rúbricas Registradas</h2>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título de la Rúbrica</th>
                        <th>Módulo</th>
                        <th>Nº Criterios</th>
                        <th>Puntaje Máximo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="rubricaTable"></tbody>
            </table>
        </div>
    </section>

</div>

<!-- Modal para crear o editar rúbrica -->
<div class="modal" id="rubricaModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nueva Rúbrica de Evaluación</h2>
            <button class="close-btn" type="button" onclick="closeModal()" aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <form id="rubricaForm" method="POST" action="rubrica_evaluacion.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" id="editId" name="editId">

                <div class="form-grid">
                    <h3 class="section-title">Información General</h3>

                    <div class="field">
                        <label for="code">Código</label>
                        <input id="code" name="code" readonly>
                    </div>

                    <div class="field">
                        <label for="module">Módulo asociado *</label>
                        <select id="module" name="module" required>
                            <option value="">Seleccionar módulo</option>
                            <option>Gestión de pacientes</option>
                            <option>Pruebas funcionales</option>
                            <option>Control de calidad general</option>
                            <option>Seguridad y accesos</option>
                            <option>Otro</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="title">Título de la Rúbrica *</label>
                        <input id="title" name="title" required maxlength="150" placeholder="Ej. Rúbrica de evaluación para Casos de Prueba">
                    </div>

                    <div class="field full">
                        <label for="description">Descripción general / Objetivo *</label>
                        <textarea id="description" name="description" required placeholder="Describe qué aspectos del sistema o entregable evaluará esta rúbrica."></textarea>
                    </div>

                    <h3 class="section-title">Criterios de Evaluación</h3>

                    <div class="full">
                        <p class="subtitle">Agrega los criterios específicos con su respectiva ponderación o puntaje.</p>
                        <div id="criteriaContainer"></div>
                        <button type="button" class="btn-light" onclick="addCriterion()" style="margin-top: 10px;">
                            + Agregar criterio
                        </button>
                    </div>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; padding-top:22px; margin-top:22px; border-top:1px solid var(--border);">
                    <button type="button" class="btn-light" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar rúbrica</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario invisible para eliminar -->
<form id="deleteForm" method="POST" action="rubrica_evaluacion.php" style="display:none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
// Cargamos las rúbricas directamente desde MySQL/PHP
const rubricas = <?= json_encode($rubricasFromDB); ?>;
let criteriaCounter = 0;

function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

function nextCode() {
    const max = rubricas.reduce((highest, item) => {
        const match = String(item.code || '').match(/^RB-(\d+)$/);
        return match ? Math.max(highest, Number(match[1])) : highest;
    }, 0);
    return 'RB-' + String(max + 1).padStart(3, '0');
}

function openModal(id = null) {
    document.getElementById('rubricaForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('criteriaContainer').innerHTML = '';
    criteriaCounter = 0;

    if (id !== null) {
        const item = rubricas.find(r => String(r.id) === String(id));
        if (!item) return;

        document.getElementById('modalTitle').textContent = 'Editar Rúbrica de Evaluación';
        document.getElementById('editId').value = item.id;
        document.getElementById('code').value = item.code;
        document.getElementById('module').value = item.module;
        document.getElementById('title').value = item.title;
        document.getElementById('description').value = item.description;

        (item.criteria || []).forEach(c => addCriterion(c));
    } else {
        document.getElementById('modalTitle').textContent = 'Nueva Rúbrica de Evaluación';
        document.getElementById('code').value = nextCode();
        addCriterion();
        addCriterion();
    }

    document.getElementById('rubricaModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('rubricaModal').classList.remove('open');
    document.body.style.overflow = '';
}

function addCriterion(data = {}) {
    criteriaCounter++;
    const id = criteriaCounter;
    const container = document.getElementById('criteriaContainer');
    const div = document.createElement('div');

    div.className = 'criteria-box';
    div.dataset.criteriaId = id;

    div.innerHTML = `
        <div class="criteria-box-header">
            <strong>Criterio ${id}</strong>
            <button type="button" class="btn-danger" onclick="removeCriterion(${id})">Eliminar</button>
        </div>
        <div class="criteria-grid">
            <div class="field">
                <label>Descripción del Criterio *</label>
                <input class="crit-desc" name="criteria[${id}][desc]" required maxlength="250" value="${escapeHTML(data.desc || '')}" placeholder="Ej. Claridad en los pasos y resultados esperados">
            </div>
            <div class="field">
                <label>Puntaje Máximo *</label>
                <input type="number" class="crit-points" name="criteria[${id}][points]" min="1" required value="${data.points || 5}" placeholder="Ej. 5">
            </div>
        </div>
    `;
    container.appendChild(div);
}

function removeCriterion(id) {
    const element = document.querySelector(`.criteria-box[data-criteria-id="${id}"]`);
    if (element) element.remove();
}

function renderTable() {
    const search = document.getElementById('search').value.trim().toLowerCase();
    const module = document.getElementById('filterModule').value;

    const filtered = rubricas.filter(item => {
        const matchesSearch = item.code.toLowerCase().includes(search) || item.title.toLowerCase().includes(search);
        const matchesModule = !module || item.module === module;
        return matchesSearch && matchesModule;
    });

    const tbody = document.getElementById('rubricaTable');

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="empty">No hay rúbricas registradas que coincidan.</td></tr>`;
    } else {
        tbody.innerHTML = filtered.map(item => {
            const criteria = item.criteria || [];
            const totalPoints = criteria.reduce((sum, c) => sum + c.points, 0);

            return `
                <tr>
                    <td><strong>${escapeHTML(item.code)}</strong></td>
                    <td>
                        <strong>${escapeHTML(item.title)}</strong><br>
                        <small style="color:var(--muted);">${escapeHTML(item.description.substring(0, 60))}...</small>
                    </td>
                    <td><span class="badge badge-eval">${escapeHTML(item.module)}</span></td>
                    <td>${criteria.length} criterios</td>
                    <td><strong>${totalPoints} pts</strong></td>
                    <td>
                        <div class="actions">
                            <button class="action-btn" type="button" onclick="viewRubrica('${item.id}')">Ver</button>
                            <button class="action-btn" type="button" onclick="openModal('${item.id}')">Editar</button>
                            <button class="btn-danger" type="button" onclick="deleteRubrica('${item.id}')">Eliminar</button>
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    updateStats();
}

function updateStats() {
    document.getElementById('totalRubricas').textContent = rubricas.length;

    const allCriteria = rubricas.flatMap(r => r.criteria || []);
    document.getElementById('totalCriterios').textContent = allCriteria.length;

    const modules = new Set(rubricas.map(r => r.module));
    document.getElementById('totalModulos').textContent = modules.size;

    if (rubricas.length === 0) {
        document.getElementById('promedioPuntaje').textContent = '0 pts';
        return;
    }

    const totalMaxPoints = rubricas.reduce((sum, r) => {
        return sum + (r.criteria || []).reduce((s, c) => s + c.points, 0);
    }, 0);
    const avg = Math.round(totalMaxPoints / rubricas.length);
    document.getElementById('promedioPuntaje').textContent = avg + ' pts';
}

function viewRubrica(id) {
    if (!id) return;
    window.location.href = 'rubrica_evaluacion_detalle.php?id=' + encodeURIComponent(id);
}

function deleteRubrica(id) {
    const item = rubricas.find(r => String(r.id) === String(id));
    if (!item) return;

    if (!confirm(`¿Eliminar la rúbrica ${item.code}? Esta acción no se puede deshacer.`)) return;

    document.getElementById('deleteId').value = id;
    document.getElementById('deleteForm').submit();
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filterModule').value = '';
    renderTable();
}

document.getElementById('rubricaModal').addEventListener('click', event => {
    if (event.target.id === 'rubricaModal') closeModal();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeModal();
});

renderTable();
</script>

<?php include 'includes/footer.php'; ?>