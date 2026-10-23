<?php
$titulo_pestana    = "Portafolio de Evidencias";
$titulo_pagina     = "Portafolio de Evidencias";
$subtitulo_pagina  = "Registro y control de documentos, capturas, logs y reportes de prueba";
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
            $editId        = $_POST['editId'] ?? '';
            $code          = $_POST['code'];
            $module        = $_POST['module'];
            $title         = $_POST['title'];
            $evidenceType  = $_POST['evidenceType'];
            $testPhase     = $_POST['testPhase'];
            $fileUrl       = $_POST['fileUrl'];
            $description   = $_POST['description'];
            $observations  = $_POST['observations'] ?? '';

            if (!empty($editId)) {
                $stmt = $pdo->prepare("UPDATE portafolio_evidencias SET modulo = ?, titulo = ?, tipo_evidencia = ?, fase_prueba = ?, enlace_archivo = ?, descripcion = ?, observaciones = ? WHERE id = ?");
                $stmt->execute([$module, $title, $evidenceType, $testPhase, $fileUrl, $description, $observations, $editId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO portafolio_evidencias (codigo, modulo, titulo, tipo_evidencia, fase_prueba, enlace_archivo, descripcion, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $module, $title, $evidenceType, $testPhase, $fileUrl, $description, $observations]);
            }

            echo "<script>window.location.href='portafolio_evidencias.php';</script>";
            exit;
        } catch (Exception $e) {
            $mensaje_alerta = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    } elseif ($_POST['accion'] === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        if (!empty($idEliminar)) {
            $stmt = $pdo->prepare("DELETE FROM portafolio_evidencias WHERE id = ?");
            $stmt->execute([$idEliminar]);
            echo "<script>window.location.href='portafolio_evidencias.php';</script>";
            exit;
        }
    }
}

// Consultar registros desde la Base de Datos
$evidencesFromDB = [];
try {
    $stmt = $pdo->query("SELECT * FROM portafolio_evidencias ORDER BY id DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($registros as $r) {
        $evidencesFromDB[] = [
            'id'           => $r['id'],
            'code'         => $r['codigo'],
            'module'       => $r['modulo'],
            'title'        => $r['titulo'],
            'evidenceType' => $r['tipo_evidencia'],
            'testPhase'    => $r['fase_prueba'],
            'fileUrl'      => $r['enlace_archivo'],
            'description'  => $r['descripcion'],
            'observations' => $r['observaciones']
        ];
    }
} catch (Exception $e) {
    $evidencesFromDB = [];
}
?>

<!-- Estilos específicos para el Portafolio de Evidencias -->
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

.badge-type {
    background: #e0f2fe;
    color: #0369a1;
}

.badge-phase {
    background: #f3e8ff;
    color: #6b21a8;
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
    max-width: 900px;
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
            <h1>Portafolio de Evidencias</h1>
            <p class="subtitle">Gestión centralizada de capturas, logs y reportes de pruebas</p>
        </div>

        <button class="btn-primary" type="button" onclick="openModal()">
            + Nueva evidencia
        </button>
    </header>

    <section class="stats">
        <div class="stat-card">
            <div class="stat-label">Evidencias Registradas</div>
            <div class="stat-number" id="totalEvidencias">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Capturas de Pantalla</div>
            <div class="stat-number" id="totalCapturas">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Logs y Reportes</div>
            <div class="stat-number" id="totalLogs">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Módulos Evidenciados</div>
            <div class="stat-number" id="totalModulos">0</div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Buscar evidencias</h2>
        </div>

        <div class="filters">
            <div class="field">
                <label for="search">Buscar por código, título o enlace</label>
                <input id="search" placeholder="Ej. EV-001 o captura login" oninput="renderTable()">
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
            <h2>Evidencias Registradas</h2>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título de la Evidencia</th>
                        <th>Módulo</th>
                        <th>Tipo</th>
                        <th>Fase</th>
                        <th>Enlace/Archivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="evidenceTable"></tbody>
            </table>
        </div>
    </section>

</div>

<!-- Modal para crear o editar evidencia -->
<div class="modal" id="evidenceModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nueva Evidencia</h2>
            <button class="close-btn" type="button" onclick="closeModal()" aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <form id="evidenceForm" method="POST" action="portafolio_evidencias.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" id="editId" name="editId">

                <div class="form-grid">
                    <h3 class="section-title">Información General</h3>

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
                        <label for="title">Título de la Evidencia *</label>
                        <input id="title" name="title" required maxlength="150" placeholder="Ej. Captura de pantalla de fallo en login con credenciales inválidas">
                    </div>

                    <div class="field">
                        <label for="evidenceType">Tipo de Evidencia *</label>
                        <select id="evidenceType" name="evidenceType" required>
                            <option value="Captura de Pantalla">Captura de Pantalla</option>
                            <option value="Archivo Log / Consola">Archivo Log / Consola</option>
                            <option value="Documento PDF / Reporte">Documento PDF / Reporte</option>
                            <option value="Grabadación de Video">Grabadación de Video</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="testPhase">Fase de Prueba *</label>
                        <select id="testPhase" name="testPhase" required>
                            <option value="Pruebas Unitarias">Pruebas Unitarias</option>
                            <option value="Pruebas de Integración">Pruebas de Integración</option>
                            <option value="Pruebas del Sistema">Pruebas del Sistema</option>
                            <option value="Pruebas de Aceptación (UAT)">Pruebas de Aceptación (UAT)</option>
                            <option value="Regresión">Regresión</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="fileUrl">Ruta / Enlace al Archivo de Evidencia *</label>
                        <input id="fileUrl" name="fileUrl" required maxlength="255" placeholder="Ej. uploads/evidencias/captura_01.png o https://drive.google me/...">
                    </div>

                    <div class="field full">
                        <label for="description">Descripción detallada de la evidencia *</label>
                        <textarea id="description" name="description" required placeholder="Describe qué muestra la evidencia y en qué contexto se obtuvo."></textarea>
                    </div>

                    <div class="field full">
                        <label for="observations">Observaciones adicionales</label>
                        <textarea id="observations" name="observations" placeholder="Cualquier nota extra relevante para los testers o desarrolladores."></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; padding-top:22px; margin-top:22px; border-top:1px solid var(--border);">
                    <button type="button" class="btn-light" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar evidencia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" action="portafolio_evidencias.php" style="display:none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
const evidences = <?= json_encode($evidencesFromDB); ?>;

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
    const max = evidences.reduce((highest, item) => {
        const match = String(item.code || '').match(/^EV-(\d+)$/);
        return match ? Math.max(highest, Number(match[1])) : highest;
    }, 0);
    return 'EV-' + String(max + 1).padStart(3, '0');
}

function openModal(id = null) {
    document.getElementById('evidenceForm').reset();
    document.getElementById('editId').value = '';

    if (id !== null) {
        const item = evidences.find(e => String(e.id) === String(id));
        if (!item) return;

        document.getElementById('modalTitle').textContent = 'Editar Evidencia';
        document.getElementById('editId').value = item.id;
        document.getElementById('code').value = item.code;
        document.getElementById('module').value = item.module;
        document.getElementById('title').value = item.title;
        document.getElementById('evidenceType').value = item.evidenceType;
        document.getElementById('testPhase').value = item.testPhase;
        document.getElementById('fileUrl').value = item.fileUrl;
        document.getElementById('description').value = item.description;
        document.getElementById('observations').value = item.observations || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Nueva Evidencia';
        document.getElementById('code').value = nextCode();
    }

    document.getElementById('evidenceModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('evidenceModal').classList.remove('open');
    document.body.style.overflow = '';
}

function renderTable() {
    const search = document.getElementById('search').value.trim().toLowerCase();
    const module = document.getElementById('filterModule').value;

    const filtered = evidences.filter(item => {
        const matchesSearch = item.code.toLowerCase().includes(search) || item.title.toLowerCase().includes(search) || item.fileUrl.toLowerCase().includes(search);
        const matchesModule = !module || item.module === module;
        return matchesSearch && matchesModule;
    });

    const tbody = document.getElementById('evidenceTable');

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty">No hay evidencias registradas que coincidan.</td></tr>`;
    } else {
        tbody.innerHTML = filtered.map(item => {
            return `
                <tr>
                    <td><strong>${escapeHTML(item.code)}</strong></td>
                    <td>
                        <strong>${escapeHTML(item.title)}</strong><br>
                        <small style="color:var(--muted);">${escapeHTML(item.description.substring(0, 50))}...</small>
                    </td>
                    <td>${escapeHTML(item.module)}</td>
                    <td><span class="badge badge-type">${escapeHTML(item.evidenceType)}</span></td>
                    <td><span class="badge badge-phase">${escapeHTML(item.testPhase)}</span></td>
                    <td><a href="${escapeHTML(item.fileUrl)}" target="_blank" style="color:var(--blue); font-weight:600;">Ver recurso</a></td>
                    <td>
                        <div class="actions">
                            <div class="qa-action-group">
                                <button class="qa-action-btn view" type="button" onclick="viewEvidence('${item.id}')" aria-label="Ver registro" title="Ver registro">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </button>
                                <button class="qa-action-btn edit" type="button" onclick="openModal('${item.id}')" aria-label="Editar registro" title="Editar registro">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                </button>
                                <button class="qa-action-btn delete" type="button" onclick="deleteEvidence('${item.id}')" aria-label="Eliminar registro" title="Eliminar registro">
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
    document.getElementById('totalEvidencias').textContent = evidences.length;

    const capturas = evidences.filter(e => e.evidenceType === 'Captura de Pantalla').length;
    document.getElementById('totalCapturas').textContent = capturas;

    const logs = evidences.filter(e => e.evidenceType === 'Archivo Log / Consola' || e.evidenceType === 'Documento PDF / Reporte').length;
    document.getElementById('totalLogs').textContent = logs;

    const modules = new Set(evidences.map(e => e.module));
    document.getElementById('totalModulos').textContent = modules.size;
}

function viewEvidence(id) {
    if (!id) return;
    window.location.href = 'portafolio_evidencias_detalle.php?id=' + encodeURIComponent(id);
}

function deleteEvidence(id) {
    const item = evidences.find(e => String(e.id) === String(id));
    if (!item) return;

    if (!confirm(`¿Eliminar la evidencia ${item.code}? Esta acción no se puede deshacer.`)) return;

    document.getElementById('deleteId').value = id;
    document.getElementById('deleteForm').submit();
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filterModule').value = '';
    renderTable();
}

document.getElementById('evidenceModal').addEventListener('click', event => {
    if (event.target.id === 'evidenceModal') closeModal();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeModal();
});

renderTable();
</script>

<?php include 'includes/footer.php'; ?>