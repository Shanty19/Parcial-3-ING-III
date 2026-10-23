<?php
$titulo_pestana    = "Registro de Incidentes";
$titulo_pagina     = "Registro de Incidentes";
$subtitulo_pagina  = "Control de bugs, anomalías y seguimiento de correcciones de software";
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

// Procesar formulario por POST (Guardar o Eliminar)
$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar') {
        try {
            $editId            = $_POST['editId'] ?? '';
            $code              = $_POST['code'];
            $module            = $_POST['module'];
            $titleIncident     = $_POST['titleIncident'];
            $severity          = $_POST['severity'];
            $status            = $_POST['status'];
            $stepsToReproduce  = $_POST['stepsToReproduce'];
            $expectedResult    = $_POST['expectedResult'];
            $obtainedResult    = $_POST['obtainedResult'];
            $observations      = $_POST['observations'] ?? '';

            if (!empty($editId)) {
                $stmt = $pdo->prepare("UPDATE registro_incidentes SET modulo = ?, titulo_incidente = ?, severidad = ?, estado = ?, pasos_reproducir = ?, resultado_esperado = ?, resultado_obtenido = ?, observaciones = ? WHERE id = ?");
                $stmt->execute([$module, $titleIncident, $severity, $status, $stepsToReproduce, $expectedResult, $obtainedResult, $observations, $editId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO registro_incidentes (codigo, modulo, titulo_incidente, severidad, estado, pasos_reproducir, resultado_esperado, resultado_obtenido, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $module, $titleIncident, $severity, $status, $stepsToReproduce, $expectedResult, $obtainedResult, $observations]);
            }

            echo "<script>window.location.href='registro_incidentes.php';</script>";
            exit;
        } catch (Exception $e) {
            $mensaje_alerta = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    } elseif ($_POST['accion'] === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        if (!empty($idEliminar)) {
            $stmt = $pdo->prepare("DELETE FROM registro_incidentes WHERE id = ?");
            $stmt->execute([$idEliminar]);
            echo "<script>window.location.href='registro_incidentes.php';</script>";
            exit;
        }
    }
}

// Consultar registros desde la Base de Datos
$incidentsFromDB = [];
try {
    $stmt = $pdo->query("SELECT * FROM registro_incidentes ORDER BY id DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($registros as $r) {
        $incidentsFromDB[] = [
            'id'               => $r['id'],
            'code'             => $r['codigo'],
            'module'           => $r['modulo'],
            'titleIncident'    => $r['titulo_incidente'],
            'severity'         => $r['severidad'],
            'status'           => $r['estado'],
            'stepsToReproduce' => $r['pasos_reproducir'],
            'expectedResult'   => $r['resultado_esperado'],
            'obtainedResult'   => $r['resultado_obtenido'],
            'observations'     => $r['observaciones']
        ];
    }
} catch (Exception $e) {
    $incidentsFromDB = [];
}
?>

<!-- Estilos específicos para el Registro de Incidentes -->
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

.btn-primary,
.btn-success {
    background: #0b2545;
    color: #ffffff;
    border: 1px solid #0b2545;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 700;
    transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
}

.btn-primary:hover,
.btn-success:hover {
    background: #1c4e80;
    border-color: #1c4e80;
    transform: translateY(-1px);
}

.btn-secondary,
.btn-light {
    background: #edf2f8;
    color: #0b2545;
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 700;
}

.btn-secondary:hover,
.btn-light:hover {
    background: #dfeaf6;
}

.btn-danger {
    background: #fff1f2;
    color: #c53030;
    border: 1px solid #f5b8b8;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 700;
}

.btn-danger:hover {
    background: #c53030;
    color: #ffffff;
    border-color: #c53030;
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
    border-top-color: var(--orange);
}

.stat-card:nth-child(3) {
    border-top-color: var(--red);
}

.stat-card:nth-child(4) {
    border-top-color: var(--green);
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

.sev-critica { background: #fee2e2; color: #b91c1c; }
.sev-alta { background: #ffedd5; color: #c2410c; }
.sev-media { background: #fef9c3; color: #854d0e; }
.sev-baja { background: #f1f5f9; color: #475569; }

.status-abierto { background: #e0f2fe; color: #0369a1; }
.status-revision { background: #f3e8ff; color: #6b21a8; }
.status-resuelto { background: #dcf7eb; color: #08764f; }
.status-cerrado { background: #f1f5f9; color: #475569; }

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

@media (max-width: 900px) {
    .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .filters {
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
            <h1>Registro de Incidentes</h1>
            <p class="subtitle">Seguimiento de errores y fallos detectados en el sistema</p>
        </div>

        <button class="btn-primary" type="button" onclick="openModal()">
            + Nuevo incidente
        </button>
    </header>

    <section class="stats">
        <div class="stat-card">
            <div class="stat-label">Total de Incidentes</div>
            <div class="stat-number" id="totalIncidentes">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Abiertos / En Revisión</div>
            <div class="stat-number" id="incidentesAbiertos">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Críticos / Altos</div>
            <div class="stat-number" id="incidentesCriticos">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Resueltos / Cerrados</div>
            <div class="stat-number" id="incidentesResueltos">0</div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Buscar incidentes</h2>
        </div>

        <div class="filters">
            <div class="field">
                <label for="search">Buscar por código o título</label>
                <input id="search" placeholder="Ej. INC-001 o error en login" oninput="renderTable()">
            </div>

            <div class="field">
                <label for="filterModule">Módulo</label>
                <select id="filterModule" onchange="renderTable()">
                    <option value="">Todos los módulos</option>
                    <option>Gestión de pacientes</option>
                    <option>Búsqueda de pacientes</option>
                    <option>Médicos y citas</option>
                    <option>Facturación y pagos</option>
                    <option>Autenticación y roles</option>
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
            <h2>Incidentes Registrados</h2>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Incidente / Módulo</th>
                        <th>Severidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="incidentTable"></tbody>
            </table>
        </div>
    </section>

</div>

<!-- Modal para crear o editar incidente -->
<div class="modal" id="incidentModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Incidente</h2>
            <button class="close-btn" type="button" onclick="closeModal()" aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <form id="incidentForm" method="POST" action="registro_incidentes.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" id="editId" name="editId">

                <div class="form-grid">
                    <h3 class="section-title">Información del Incidente</h3>

                    <div class="field">
                        <label for="code">Código</label>
                        <input id="code" name="code" readonly>
                    </div>

                    <div class="field">
                        <label for="module">Módulo del sistema *</label>
                        <select id="module" name="module" required>
                            <option value="">Seleccionar módulo</option>
                            <option>Gestión de pacientes</option>
                            <option>Búsqueda de pacientes</option>
                            <option>Médicos y citas</option>
                            <option>Facturación y pagos</option>
                            <option>Autenticación y roles</option>
                            <option>Otro</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="titleIncident">Título del Incidente / Bug *</label>
                        <input id="titleIncident" name="titleIncident" required maxlength="150" placeholder="Ej. El sistema no valida campos vacíos en el registro de pacientes">
                    </div>

                    <div class="field">
                        <label for="severity">Severidad *</label>
                        <select id="severity" name="severity" required>
                            <option value="Baja">Baja</option>
                            <option value="Media">Media</option>
                            <option value="Alta">Alta</option>
                            <option value="Crítica">Crítica</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="status">Estado *</label>
                        <select id="status" name="status" required>
                            <option value="Abierto">Abierto</option>
                            <option value="En revisión">En revisión</option>
                            <option value="Resuelto">Resuelto</option>
                            <option value="Cerrado">Cerrado</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="stepsToReproduce">Pasos para reproducir el fallo *</label>
                        <textarea id="stepsToReproduce" name="stepsToReproduce" required placeholder="1. Ir al módulo de pacientes&#10;2. Hacer clic en Nuevo Paciente&#10;3. Dejar el nombre en blanco y guardar..."></textarea>
                    </div>

                    <div class="field">
                        <label for="expectedResult">Resultado Esperado *</label>
                        <textarea id="expectedResult" name="expectedResult" required placeholder="Mostrar mensaje de error indicando que el campo es obligatorio."></textarea>
                    </div>

                    <div class="field">
                        <label for="obtainedResult">Resultado Obtenido *</label>
                        <textarea id="obtainedResult" name="obtainedResult" required placeholder="La pantalla se queda en blanco y arroja error 500."></textarea>
                    </div>

                    <div class="field full">
                        <label for="observations">Observaciones / Notas adicionales</label>
                        <textarea id="observations" name="observations" placeholder="Notas para el equipo de desarrollo..."></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; padding-top:22px; margin-top:22px; border-top:1px solid var(--border);">
                    <button type="button" class="btn-light" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar incidente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" action="registro_incidentes.php" style="display:none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
const incidents = <?= json_encode($incidentsFromDB); ?>;

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
    const max = incidents.reduce((highest, item) => {
        const match = String(item.code || '').match(/^INC-(\d+)$/);
        return match ? Math.max(highest, Number(match[1])) : highest;
    }, 0);
    return 'INC-' + String(max + 1).padStart(3, '0');
}

function openModal(id = null) {
    document.getElementById('incidentForm').reset();
    document.getElementById('editId').value = '';

    if (id !== null) {
        const item = incidents.find(i => String(i.id) === String(id));
        if (!item) return;

        document.getElementById('modalTitle').textContent = 'Editar Incidente';
        document.getElementById('editId').value = item.id;
        document.getElementById('code').value = item.code;
        document.getElementById('module').value = item.module;
        document.getElementById('titleIncident').value = item.titleIncident;
        document.getElementById('severity').value = item.severity;
        document.getElementById('status').value = item.status;
        document.getElementById('stepsToReproduce').value = item.stepsToReproduce;
        document.getElementById('expectedResult').value = item.expectedResult;
        document.getElementById('obtainedResult').value = item.obtainedResult;
        document.getElementById('observations').value = item.observations || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Nuevo Incidente';
        document.getElementById('code').value = nextCode();
    }

    document.getElementById('incidentModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('incidentModal').classList.remove('open');
    document.body.style.overflow = '';
}

function renderTable() {
    const search = document.getElementById('search').value.trim().toLowerCase();
    const module = document.getElementById('filterModule').value;

    const filtered = incidents.filter(item => {
        const matchesSearch = item.code.toLowerCase().includes(search) || item.titleIncident.toLowerCase().includes(search);
        const matchesModule = !module || item.module === module;
        return matchesSearch && matchesModule;
    });

    const tbody = document.getElementById('incidentTable');

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="empty">No hay incidentes registrados que coincidan.</td></tr>`;
    } else {
        tbody.innerHTML = filtered.map(item => {
            let sevClass = 'sev-baja';
            if (item.severity === 'Crítica') sevClass = 'sev-critica';
            else if (item.severity === 'Alta') sevClass = 'sev-alta';
            else if (item.severity === 'Media') sevClass = 'sev-media';

            let statusClass = 'status-abierto';
            if (item.status === 'En revisión') statusClass = 'status-revision';
            else if (item.status === 'Resuelto') statusClass = 'status-resuelto';
            else if (item.status === 'Cerrado') statusClass = 'status-cerrado';

            return `
                <tr>
                    <td><strong>${escapeHTML(item.code)}</strong></td>
                    <td>
                        <strong>${escapeHTML(item.titleIncident)}</strong><br>
                        <small style="color:var(--muted);">${escapeHTML(item.module)}</small>
                    </td>
                    <td><span class="badge ${sevClass}">${escapeHTML(item.severity)}</span></td>
                    <td><span class="badge ${statusClass}">${escapeHTML(item.status)}</span></td>
                    <td>
                        <div class="actions">
                            <div class="qa-action-group">
                                <button class="qa-action-btn view" type="button" onclick="viewIncident('${item.id}')" aria-label="Ver registro" title="Ver registro">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </button>
                                <button class="qa-action-btn edit" type="button" onclick="openModal('${item.id}')" aria-label="Editar registro" title="Editar registro">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                </button>
                                <button class="qa-action-btn delete" type="button" onclick="deleteIncident('${item.id}')" aria-label="Eliminar registro" title="Eliminar registro">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    updateStats();
}

function updateStats() {
    document.getElementById('totalIncidentes').textContent = incidents.length;

    const abiertos = incidents.filter(i => i.status === 'Abierto' || i.status === 'En revisión').length;
    document.getElementById('incidentesAbiertos').textContent = abiertos;

    const criticos = incidents.filter(i => i.severity === 'Crítica' || i.severity === 'Alta').length;
    document.getElementById('incidentesCriticos').textContent = criticos;

    const resueltos = incidents.filter(i => i.status === 'Resuelto' || i.status === 'Cerrado').length;
    document.getElementById('incidentesResueltos').textContent = resueltos;
}

function viewIncident(id) {
    if (!id) return;
    window.location.href = 'registro_incidentes_detalle.php?id=' + encodeURIComponent(id);
}

function deleteIncident(id) {
    const item = incidents.find(i => String(i.id) === String(id));
    if (!item) return;

    if (!confirm(`¿Eliminar el incidente ${item.code}? Esta acción no se puede deshacer.`)) return;

    document.getElementById('deleteId').value = id;
    document.getElementById('deleteForm').submit();
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filterModule').value = '';
    renderTable();
}

document.getElementById('incidentModal').addEventListener('click', event => {
    if (event.target.id === 'incidentModal') closeModal();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeModal();
});

renderTable();
</script>

<?php include 'includes/footer.php'; ?>