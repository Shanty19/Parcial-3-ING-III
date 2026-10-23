<?php
$titulo_pestana    = "Cobertura de Caja Blanca";
$titulo_pagina     = "Cobertura de Caja Blanca";
$subtitulo_pagina  = "Técnicas de testing · Análisis de cobertura";
include 'includes/header.php';

$host     = 'localhost';
$dbname   = 'clinica_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar formulario por POST (Guardar o Eliminar)
$mensaje_alerta = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'guardar') {
        try {
            $editId               = $_POST['editId'] ?? '';
            $code                 = $_POST['code'];
            $module               = $_POST['module'];
            $functionName         = $_POST['functionName'];
            $coverageCriterion    = $_POST['coverageCriterion'];
            $cyclomaticComplexity = intval($_POST['cyclomaticComplexity']);
            $totalStatements      = intval($_POST['totalStatements']);
            $executedStatements   = intval($_POST['executedStatements']);
            
            // Cálculo seguro del porcentaje
            $coveragePercent      = ($totalStatements > 0) ? min(100, round(($executedStatements / $totalStatements) * 100)) : 0;
            
            $codeSnippet          = $_POST['codeSnippet'];
            $observations         = $_POST['observations'] ?? '';

            if (!empty($editId)) {
                $stmt = $pdo->prepare("UPDATE cobertura_caja_blanca SET modulo = ?, nombre_funcion = ?, criterio_cobertura = ?, complejidad_ciclomatica = ?, total_sentencias = ?, sentencias_ejecutadas = ?, porcentaje_cobertura = ?, pseudocodigo = ?, observaciones = ? WHERE id = ?");
                $stmt->execute([$module, $functionName, $coverageCriterion, $cyclomaticComplexity, $totalStatements, $executedStatements, $coveragePercent, $codeSnippet, $observations, $editId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cobertura_caja_blanca (codigo, modulo, nombre_funcion, criterio_cobertura, complejidad_ciclomatica, total_sentencias, sentencias_ejecutadas, porcentaje_cobertura, pseudocodigo, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $module, $functionName, $coverageCriterion, $cyclomaticComplexity, $totalStatements, $executedStatements, $coveragePercent, $codeSnippet, $observations]);
            }

            echo "<script>window.location.href='cobertura_caja_blanca.php';</script>";
            exit;
        } catch (Exception $e) {
            $mensaje_alerta = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    } elseif ($_POST['accion'] === 'eliminar') {
        $idEliminar = $_POST['id'] ?? '';
        if (!empty($idEliminar)) {
            $stmt = $pdo->prepare("DELETE FROM cobertura_caja_blanca WHERE id = ?");
            $stmt->execute([$idEliminar]);
            echo "<script>window.location.href='cobertura_caja_blanca.php';</script>";
            exit;
        }
    }
}

// Consultar registros desde la Base de Datos
$analysesFromDB = [];
try {
    $stmt = $pdo->query("SELECT * FROM cobertura_caja_blanca ORDER BY id DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($registros as $r) {
        $analysesFromDB[] = [
            'id'                   => $r['id'],
            'code'                 => $r['codigo'],
            'module'               => $r['modulo'],
            'functionName'         => $r['nombre_funcion'],
            'coverageCriterion'    => $r['criterio_cobertura'],
            'cyclomaticComplexity' => intval($r['complejidad_ciclomatica']),
            'totalStatements'      => intval($r['total_sentencias']),
            'executedStatements'   => intval($r['sentencias_ejecutadas']),
            'coveragePercent'      => intval($r['porcentaje_cobertura']),
            'codeSnippet'          => $r['pseudocodigo'],
            'observations'         => $r['observaciones']
        ];
    }
} catch (Exception $e) {
    $analysesFromDB = [];
}
?>

<!-- Estilos específicos para Cobertura de Caja Blanca -->
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

.badge-high {
    background: #dcf7eb;
    color: #08764f;
}

.badge-medium {
    background: #fef9c3;
    color: #854d0e;
}

.badge-low {
    background: #ffe5e5;
    color: #b52e35;
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

/* Ventana modal */
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

.progress-bar-bg {
    background: #e2e8f0;
    border-radius: 10px;
    height: 10px;
    width: 100px;
    overflow: hidden;
    display: inline-block;
    vertical-align: middle;
    margin-right: 8px;
}

.progress-bar-fill {
    height: 100%;
    background: var(--blue);
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
            <h1>Análisis de Cobertura de Caja Blanca</h1>
            <p class="subtitle">Evaluación de la estructura interna del código, caminos lógicos y ramas</p>
        </div>

        <button class="btn-primary" type="button" onclick="openModal()">
            + Nuevo análisis
        </button>
    </header>

    <section class="stats">
        <div class="stat-card">
            <div class="stat-label">Análisis registrados</div>
            <div class="stat-number" id="totalAnalisis">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Cobertura Promedio</div>
            <div class="stat-number" id="coberturaPromedio">0%</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Lineas/Sentencias Evaluadas</div>
            <div class="stat-number" id="totalLineas">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Caminos Lógicos Probados</div>
            <div class="stat-number" id="totalCaminos">0</div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Buscar análisis de caja blanca</h2>
        </div>

        <div class="filters">
            <div class="field">
                <label for="search">Buscar por código, función o módulo</label>
                <input id="search" placeholder="Ej. CB-001 o calcularCostoConsulta" oninput="renderTable()">
            </div>

            <div class="field">
                <label for="filterModule">Módulo</label>
                <select id="filterModule" onchange="renderTable()">
                    <option value="">Todos los módulos</option>
                    <option>Gestión de pacientes</option>
                    <option>Facturación e historial</option>
                    <option>Médicos y citas</option>
                    <option>Registro clínico SOAP</option>
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
            <h2>Análisis de Cobertura Registrados</h2>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Módulo / Función</th>
                        <th>Criterio de Cobertura</th>
                        <th>Sentencias (Ejecutadas/Total)</th>
                        <th>Cobertura (%)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="analysisTable"></tbody>
            </table>
        </div>
    </section>

</div>

<!-- Ventana modal para crear o editar un análisis -->
<div class="modal" id="analysisModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo análisis de Caja Blanca</h2>
            <button class="close-btn" type="button" onclick="closeModal()" aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <form id="analysisForm" method="POST" action="cobertura_caja_blanca.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" id="editId" name="editId">

                <div class="form-grid">
                    <h3 class="section-title">Datos del Componente / Código</h3>

                    <div class="field">
                        <label for="code">Código</label>
                        <input id="code" name="code" readonly>
                    </div>

                    <div class="field">
                        <label for="module">Módulo del sistema *</label>
                        <select id="module" name="module" required>
                            <option value="">Seleccionar módulo</option>
                            <option>Gestión de pacientes</option>
                            <option>Facturación e historial</option>
                            <option>Médicos y citas</option>
                            <option>Registro clínico SOAP</option>
                            <option>Autenticación y roles</option>
                            <option>Otro</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="functionName">Nombre del archivo / Función / Método evaluado *</label>
                        <input id="functionName" name="functionName" required maxlength="150" placeholder="Ej. PacienteController::calcularDescuento()">
                    </div>

                    <div class="field">
                        <label for="coverageCriterion">Criterio de Cobertura Evaluado *</label>
                        <select id="coverageCriterion" name="coverageCriterion" required>
                            <option value="Cobertura de Sentencias (Statement)">Cobertura de Sentencias (Statement)</option>
                            <option value="Cobertura de Decisiones / Ramas (Branch)">Cobertura de Decisiones / Ramas (Branch)</option>
                            <option value="Cobertura de Condiciones (Condition)">Cobertura de Condiciones (Condition)</option>
                            <option value="Cobertura de Caminos (Path Coverage)">Cobertura de Caminos (Path Coverage)</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="cyclomaticComplexity">Complejidad Ciclomática (V(G)) *</label>
                        <input type="number" id="cyclomaticComplexity" name="cyclomaticComplexity" min="1" required placeholder="Ej. 4">
                    </div>

                    <h3 class="section-title">Métricas de Ejecución</h3>

                    <div class="field">
                        <label for="totalStatements">Total de Sentencias / Líneas *</label>
                        <input type="number" id="totalStatements" name="totalStatements" min="1" required placeholder="Ej. 50" oninput="calculatePercentage()">
                    </div>

                    <div class="field">
                        <label for="executedStatements">Sentencias Ejecutadas por Pruebas *</label>
                        <input type="number" id="executedStatements" name="executedStatements" min="0" required placeholder="Ej. 42" oninput="calculatePercentage()">
                    </div>

                    <div class="field full">
                        <label>Porcentaje de Cobertura Calculado</label>
                        <input id="coveragePercent" readonly style="font-weight: bold; background: #f8fafc;" value="0%">
                    </div>

                    <div class="field full">
                        <label for="codeSnippet">Pseudocódigo o Rutas Críticas Analizadas *</label>
                        <textarea id="codeSnippet" name="codeSnippet" required placeholder="Describe las ramas 'if-else', bucles o condiciones principales probadas."></textarea>
                    </div>

                    <div class="field full">
                        <label for="observations">Observaciones y Recomendaciones</label>
                        <textarea id="observations" name="observations" placeholder="Detalla áreas no alcanzadas por la prueba o sugerencias de refactorización."></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:10px; padding-top:22px; margin-top:22px; border-top:1px solid var(--border);">
                    <button type="button" class="btn-light" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar análisis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" action="cobertura_caja_blanca.php" style="display:none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
const analyses = <?= json_encode($analysesFromDB); ?>;

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
    const max = analyses.reduce((highest, item) => {
        const match = String(item.code || '').match(/^CB-(\d+)$/);
        return match ? Math.max(highest, Number(match[1])) : highest;
    }, 0);
    return 'CB-' + String(max + 1).padStart(3, '0');
}

function calculatePercentage() {
    const total = parseFloat(document.getElementById('totalStatements').value) || 0;
    const executed = parseFloat(document.getElementById('executedStatements').value) || 0;
    
    if (total > 0) {
        const percent = Math.min(100, Math.round((executed / total) * 100));
        document.getElementById('coveragePercent').value = percent + '%';
    } else {
        document.getElementById('coveragePercent').value = '0%';
    }
}

function openModal(id = null) {
    document.getElementById('analysisForm').reset();
    document.getElementById('editId').value = '';

    if (id !== null) {
        const item = analyses.find(a => String(a.id) === String(id));
        if (!item) return;

        document.getElementById('modalTitle').textContent = 'Editar análisis de caja blanca';
        document.getElementById('editId').value = item.id;
        document.getElementById('code').value = item.code;
        document.getElementById('module').value = item.module;
        document.getElementById('functionName').value = item.functionName;
        document.getElementById('coverageCriterion').value = item.coverageCriterion;
        document.getElementById('cyclomaticComplexity').value = item.cyclomaticComplexity;
        document.getElementById('totalStatements').value = item.totalStatements;
        document.getElementById('executedStatements').value = item.executedStatements;
        document.getElementById('coveragePercent').value = item.coveragePercent + '%';
        document.getElementById('codeSnippet').value = item.codeSnippet;
        document.getElementById('observations').value = item.observations || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Nuevo análisis de caja blanca';
        document.getElementById('code').value = nextCode();
        document.getElementById('coveragePercent').value = '0%';
    }

    document.getElementById('analysisModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('analysisModal').classList.remove('open');
    document.body.style.overflow = '';
}

function renderTable() {
    const search = document.getElementById('search').value.trim().toLowerCase();
    const module = document.getElementById('filterModule').value;

    const filtered = analyses.filter(item => {
        const matchesSearch = item.code.toLowerCase().includes(search) || item.functionName.toLowerCase().includes(search);
        const matchesModule = !module || item.module === module;
        return matchesSearch && matchesModule;
    });

    const tbody = document.getElementById('analysisTable');

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty">No hay análisis registrados de caja blanca.</td></tr>`;
    } else {
        tbody.innerHTML = filtered.map(item => {
            let badgeClass = 'badge-high';
            if (item.coveragePercent < 50) badgeClass = 'badge-low';
            else if (item.coveragePercent < 80) badgeClass = 'badge-medium';

            return `
                <tr>
                    <td><strong>${escapeHTML(item.code)}</strong></td>
                    <td>
                        <strong>${escapeHTML(item.functionName)}</strong><br>
                        <small style="color:var(--muted);">${escapeHTML(item.module)}</small>
                    </td>
                    <td>${escapeHTML(item.coverageCriterion)}</td>
                    <td>${item.executedStatements} / ${item.totalStatements}</td>
                    <td>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: ${item.coveragePercent}%;"></div>
                        </div>
                        <strong>${item.coveragePercent}%</strong>
                    </td>
                    <td><span class="badge ${badgeClass}">${item.coveragePercent >= 80 ? 'Óptimo' : (item.coveragePercent >= 50 ? 'Aceptable' : 'Crítico')}</span></td>
                    <td>
                        <div class="actions">
                            <button class="action-btn" type="button" onclick="viewAnalysis('${item.id}')">Ver</button>
                            <button class="action-btn" type="button" onclick="openModal('${item.id}')">Editar</button>
                            <button class="btn-danger" type="button" onclick="deleteAnalysis('${item.id}')">Eliminar</button>
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    updateStats();
}

function updateStats() {
    document.getElementById('totalAnalisis').textContent = analyses.length;

    if (analyses.length === 0) {
        document.getElementById('coberturaPromedio').textContent = '0%';
        document.getElementById('totalLineas').textContent = '0';
        document.getElementById('totalCaminos').textContent = '0';
        return;
    }

    const totalPct = analyses.reduce((acc, a) => acc + a.coveragePercent, 0);
    const avg = Math.round(totalPct / analyses.length);
    document.getElementById('coberturaPromedio').textContent = avg + '%';

    const sumLines = analyses.reduce((acc, a) => acc + a.totalStatements, 0);
    document.getElementById('totalLineas').textContent = sumLines;

    const sumComplexity = analyses.reduce((acc, a) => acc + a.cyclomaticComplexity, 0);
    document.getElementById('totalCaminos').textContent = sumComplexity;
}

function viewAnalysis(id) {
    if (!id) return;
    window.location.href = 'cobertura_caja_blanca_detalle.php?id=' + encodeURIComponent(id);
}

function deleteAnalysis(id) {
    const item = analyses.find(a => String(a.id) === String(id));
    if (!item) return;

    if (!confirm(`¿Eliminar el análisis ${item.code}? Esta acción no se puede deshacer.`)) return;

    document.getElementById('deleteId').value = id;
    document.getElementById('deleteForm').submit();
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filterModule').value = '';
    renderTable();
}

document.getElementById('analysisModal').addEventListener('click', event => {
    if (event.target.id === 'analysisModal') closeModal();
});

document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeModal();
});

renderTable();
</script>

<?php include 'includes/footer.php'; ?>