<?php
$titulo_pestana    = "Matriz de Clases de Equivalencia";
$titulo_pagina     = "Matriz de Clases de Equivalencia";
$subtitulo_pagina  = "Técnicas de testing · Análisis de clases válidas e inválidas";
include 'includes/header.php';
?>

<!-- Estilos específicos para la Matriz de Equivalencia -->
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
    border-top-color: var(--red);
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

.valid {
    background: #dcf7eb;
    color: #08764f;
}

.invalid {
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

.class-box {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 14px;
    background: #fbfcff;
}

.class-box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.class-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
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

    <header class="page-header">
        <div>
            <h1>Matriz de Clases de Equivalencia</h1>
            <p class="subtitle">Técnicas de testing · Análisis de clases válidas e inválidas</p>
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
            <div class="stat-label">Clases identificadas</div>
            <div class="stat-number" id="totalClases">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Clases válidas</div>
            <div class="stat-number" id="clasesValidas">0</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Clases inválidas</div>
            <div class="stat-number" id="clasesInvalidas">0</div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Buscar análisis</h2>
        </div>

        <div class="filters">
            <div class="field">
                <label for="search">Buscar por código o nombre</label>
                <input id="search" placeholder="Ej. CE-001 o búsqueda de pacientes" oninput="renderTable()">
            </div>

            <div class="field">
                <label for="filterModule">Módulo</label>
                <select id="filterModule" onchange="renderTable()">
                    <option value="">Todos los módulos</option>
                    <option>Gestión de pacientes</option>
                    <option>Búsqueda de pacientes</option>
                    <option>Médicos y especialidades</option>
                    <option>Registro clínico SOAP</option>
                    <option>Inicio de sesión</option>
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
            <h2>Análisis registrados</h2>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre del análisis</th>
                        <th>Módulo</th>
                        <th>Total de clases</th>
                        <th>Válidas</th>
                        <th>Inválidas</th>
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
            <h2 id="modalTitle">Nuevo análisis de equivalencia</h2>
            <button class="close-btn" type="button" onclick="closeModal()" aria-label="Cerrar">&times;</button>
        </div>

        <div class="modal-body">
            <form id="analysisForm">
                <input type="hidden" id="editId">

                <div class="form-grid">
                    <h3 class="section-title">Información general</h3>

                    <div class="field">
                        <label for="code">Código</label>
                        <input id="code" readonly>
                    </div>

                    <div class="field">
                        <label for="module">Módulo del sistema *</label>
                        <select id="module" required>
                            <option value="">Seleccionar módulo</option>
                            <option>Gestión de pacientes</option>
                            <option>Búsqueda de pacientes</option>
                            <option>Médicos y especialidades</option>
                            <option>Registro clínico SOAP</option>
                            <option>Inicio de sesión</option>
                            <option>Otro</option>
                        </select>
                    </div>

                    <div class="field full">
                        <label for="name">Nombre del análisis *</label>
                        <input id="name" required maxlength="150" placeholder="Ej. Validación de búsqueda de pacientes">
                    </div>

                    <div class="field full">
                        <label for="fieldName">Campo o dato que se va a validar *</label>
                        <input id="fieldName" required maxlength="120" placeholder="Ej. Nombre del paciente">
                    </div>

                    <div class="field full">
                        <label for="rule">Regla de validación *</label>
                        <textarea id="rule" required placeholder="Describe qué entradas acepta el sistema y cuáles rechaza."></textarea>
                    </div>

                    <h3 class="section-title">Clases de equivalencia</h3>

                    <div class="full">
                        <p class="subtitle">Registra cada grupo de entradas que debería comportarse de manera similar.</p>
                        <div id="classesContainer"></div>
                        <button type="button" class="btn-light" onclick="addClass()" style="margin-top: 10px;">
                            + Agregar clase
                        </button>
                    </div>

                    <div class="field full">
                        <label for="observations">Observaciones</label>
                        <textarea id="observations" placeholder="Notas adicionales del análisis"></textarea>
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

<script>
const STORAGE_KEY = 'qa_clinica_matriz_equivalencia_v1';

let analyses = loadAnalyses();
let classCounter = 0;

function loadAnalyses() {
    try {
        const data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        return Array.isArray(data) ? data : [];
    } catch (error) {
        return [];
    }
}

function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(analyses));
}

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
        const match = String(item.code || '').match(/^CE-(\d+)$/);
        return match ? Math.max(highest, Number(match[1])) : highest;
    }, 0);
    return 'CE-' + String(max + 1).padStart(3, '0');
}

function openModal(id = null) {
    document.getElementById('analysisForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('classesContainer').innerHTML = '';
    classCounter = 0;

    if (id !== null) {
        const item = analyses.find(a => a.id === id);
        if (!item) return;

        document.getElementById('modalTitle').textContent = 'Editar análisis de equivalencia';
        document.getElementById('editId').value = item.id;
        document.getElementById('code').value = item.code;
        document.getElementById('module').value = item.module;
        document.getElementById('name').value = item.name;
        document.getElementById('fieldName').value = item.fieldName;
        document.getElementById('rule').value = item.rule;
        document.getElementById('observations').value = item.observations || '';

        (item.classes || []).forEach(c => addClass(c));
    } else {
        document.getElementById('modalTitle').textContent = 'Nuevo análisis de equivalencia';
        document.getElementById('code').value = nextCode();
        addClass();
        addClass();
    }

    document.getElementById('analysisModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('analysisModal').classList.remove('open');
    document.body.style.overflow = '';
}

function addClass(data = {}) {
    classCounter++;
    const id = classCounter;
    const container = document.getElementById('classesContainer');
    const div = document.createElement('div');

    div.className = 'class-box';
    div.dataset.classId = id;

    div.innerHTML = `
        <div class="class-box-header">
            <strong>Clase de equivalencia ${id}</strong>
            <button type="button" class="btn-danger" onclick="removeClass(${id})">Eliminar</button>
        </div>
        <div class="class-grid">
            <div class="field">
                <label>Nombre de la clase *</label>
                <input class="class-name" required maxlength="120" value="${escapeHTML(data.name || '')}" placeholder="Ej. Paciente existente">
            </div>
            <div class="field">
                <label>Tipo de clase *</label>
                <select class="class-type" required>
                    <option value="Válida" ${data.type === 'Válida' ? 'selected' : ''}>Válida</option>
                    <option value="Inválida" ${data.type === 'Inválida' ? 'selected' : ''}>Inválida</option>
                </select>
            </div>
            <div class="field">
                <label>Dato de ejemplo *</label>
                <input class="class-data" required maxlength="250" value="${escapeHTML(data.data || '')}" placeholder="Ej. María González">
            </div>
            <div class="field">
                <label>Resultado esperado *</label>
                <input class="class-expected" required maxlength="250" value="${escapeHTML(data.expected || '')}" placeholder="Ej. Mostrar el paciente">
            </div>
        </div>
    `;
    container.appendChild(div);
}

function removeClass(id) {
    const element = document.querySelector(`.class-box[data-class-id="${id}"]`);
    if (element) element.remove();
}

function collectClasses() {
    return [...document.querySelectorAll('#classesContainer .class-box')].map(box => ({
        name: box.querySelector('.class-name').value.trim(),
        type: box.querySelector('.class-type').value,
        data: box.querySelector('.class-data').value.trim(),
        expected: box.querySelector('.class-expected').value.trim()
    }));
}

document.getElementById('analysisForm').addEventListener('submit', event => {
    event.preventDefault();
    const classes = collectClasses();

    if (classes.length === 0) {
        alert('Agrega al menos una clase de equivalencia.');
        return;
    }

    if (classes.some(c => !c.name || !c.data || !c.expected)) {
        alert('Completa todos los campos de las clases.');
        return;
    }

    const editId = document.getElementById('editId').value;
    const item = {
        id: editId || (window.crypto && crypto.randomUUID ? crypto.randomUUID() : String(Date.now())),
        code: document.getElementById('code').value,
        module: document.getElementById('module').value,
        name: document.getElementById('name').value.trim(),
        fieldName: document.getElementById('fieldName').value.trim(),
        rule: document.getElementById('rule').value.trim(),
        observations: document.getElementById('observations').value.trim(),
        classes,
        updatedAt: new Date().toISOString()
    };

    if (editId) {
        analyses = analyses.map(a => a.id === editId ? item : a);
    } else {
        analyses.push(item);
    }

    try {
        persist();
    } catch (error) {
        alert('No se pudieron guardar los datos en este navegador.');
        return;
    }

    closeModal();
    renderTable();
});

function renderTable() {
    const search = document.getElementById('search').value.trim().toLowerCase();
    const module = document.getElementById('filterModule').value;

    const filtered = analyses.filter(item => {
        const matchesSearch = item.code.toLowerCase().includes(search) || item.name.toLowerCase().includes(search);
        const matchesModule = !module || item.module === module;
        return matchesSearch && matchesModule;
    });

    const tbody = document.getElementById('analysisTable');

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty">No hay análisis registrados que coincidan.</td></tr>`;
    } else {
        tbody.innerHTML = filtered.map(item => {
            const classes = item.classes || [];
            const valid = classes.filter(c => c.type === 'Válida').length;
            const invalid = classes.filter(c => c.type === 'Inválida').length;

            return `
                <tr>
                    <td><strong>${escapeHTML(item.code)}</strong></td>
                    <td>${escapeHTML(item.name)}</td>
                    <td>${escapeHTML(item.module)}</td>
                    <td>${classes.length}</td>
                    <td><span class="badge valid">${valid}</span></td>
                    <td><span class="badge invalid">${invalid}</span></td>
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
    const allClasses = analyses.flatMap(a => a.classes || []);
    document.getElementById('totalClases').textContent = allClasses.length;
    document.getElementById('clasesValidas').textContent = allClasses.filter(c => c.type === 'Válida').length;
    document.getElementById('clasesInvalidas').textContent = allClasses.filter(c => c.type === 'Inválida').length;
}

function viewAnalysis(id) {
    const item = analyses.find(a => a.id === id);
    if (!item) return;

    const details = [
        `Código: ${item.code}`,
        `Nombre: ${item.name}`,
        `Módulo: ${item.module}`,
        `Campo: ${item.fieldName}`,
        `Regla: ${item.rule}`,
        '',
        'CLASES DE EQUIVALENCIA:',
        ...item.classes.map((c, i) => `${i + 1}. ${c.name} (${c.type})\nDato: ${c.data}\nResultado esperado: ${c.expected}`),
        '',
        `Observaciones: ${item.observations || 'Sin observaciones'}`
    ];
    alert(details.join('\n'));
}

function deleteAnalysis(id) {
    const item = analyses.find(a => a.id === id);
    if (!item) return;

    if (!confirm(`¿Eliminar el análisis ${item.code}? Esta acción no se puede deshacer.`)) return;

    analyses = analyses.filter(a => a.id !== id);
    try {
        persist();
        renderTable();
    } catch (error) {
        alert('No se pudo actualizar el almacenamiento.');
    }
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
