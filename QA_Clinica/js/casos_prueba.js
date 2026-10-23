/**
 * Módulo de Gestión de Casos de Prueba - Clínica Salud+ QA
 */

// Casos de demostración predeterminados basados en las funciones reales de Clínica Salud+
const SEED_TEST_CASES = [
    {
        id: "CP-001",
        code: "CP-001",
        name: "Registro exitoso de un nuevo paciente",
        module: "Gestión de pacientes",
        objective: "Verificar que el sistema guarde correctamente la información personal y médica de un paciente en la base de datos.",
        preconditions: "Acceso al módulo de pacientes con credenciales activas.",
        testData: "Nombre: Carlos Mendoza, Cédula: 8-900-1122, Teléfono: 6789-0123, Email: cmendoza@email.com",
        steps: "1. Navegar a la sección 'Gestión de pacientes'.\n2. Hacer clic en 'Agregar Paciente'.\n3. Llenar todos los campos obligatorios con los datos de prueba.\n4. Presionar 'Guardar'.",
        expectedResult: "Muestra mensaje de confirmación y el paciente figura inmediatamente en el listado.",
        priority: "Alta",
        status: "Pendiente",
        createdAt: "2026-10-20"
    },
    {
        id: "CP-002",
        code: "CP-002",
        name: "Búsqueda de paciente por número de cédula",
        module: "Búsqueda de pacientes",
        objective: "Validar la precisión del filtro de búsqueda por cédula registrada.",
        preconditions: "Debe existir al menos un paciente registrado previamente con cédula 8-900-1122.",
        testData: "Cédula de búsqueda: 8-900-1122",
        steps: "1. Ir a la pantalla de Búsqueda de Pacientes.\n2. Escribir '8-900-1122' en el campo de filtro.\n3. Presionar la tecla Enter o pulsar en 'Buscar'.",
        expectedResult: "El sistema muestra únicamente la ficha coincidente con el paciente Carlos Mendoza.",
        priority: "Alta",
        status: "Pendiente",
        createdAt: "2026-10-21"
    },
    {
        id: "CP-003",
        code: "CP-003",
        name: "Consulta de catálogo de doctores y especialidades",
        module: "Consulta de doctores y especialidades",
        objective: "Comprobar el despliegue del catálogo médico y la disponibilidad por especialidad.",
        preconditions: "Servicio de datos de doctores disponible.",
        testData: "Especialidad: Cardiología",
        steps: "1. Acceder a la vista de doctores.\n2. Seleccionar el filtro de especialidad 'Cardiología'.",
        expectedResult: "Se listan los médicos pertenecientes a Cardiología con sus horarios.",
        priority: "Media",
        status: "Pendiente",
        createdAt: "2026-10-21"
    },
    {
        id: "CP-004",
        code: "CP-004",
        name: "Consumo de servicio web SOAP de aseguradoras",
        module: "Comunicación con el servicio SOAP",
        objective: "Verificar la conexión y respuesta XML del cliente de pruebas SOAP de cobertura.",
        preconditions: "Servidor local/remoto SOAP activo y accesible.",
        testData: "Código de cliente: 10452, Póliza: POL-2026-X",
        steps: "1. Ingresar a Cliente_prueba.php.\n2. Seleccionar la transacción SOAP.\n3. Ejecutar petición.",
        expectedResult: "El Web Service retorna un XML válido con código HTTP 200 OK y estado de póliza Activa.",
        priority: "Alta",
        status: "Pendiente",
        createdAt: "2026-10-22"
    },
    {
        id: "CP-005",
        code: "CP-005",
        name: "Persistencia de logs en el historial de búsquedas",
        module: "Historial de búsquedas",
        objective: "Confirmar que cada consulta realizada quede registrada en la bitácora del sistema (serviciolog.php).",
        preconditions: "Haber realizado al menos una búsqueda en el sistema.",
        testData: "Usuario actual: Tester QA",
        steps: "1. Ejecutar una consulta de paciente.\n2. Ir a la vista de registros/logs de administración.\n3. Filtrar por la hora actual.",
        expectedResult: "Se visualiza la entrada de log correspondiente indicando usuario, fecha, hora y parámetros consultados.",
        priority: "Baja",
        status: "Pendiente",
        createdAt: "2026-10-22"
    }
];

let testCases = [];

document.addEventListener("DOMContentLoaded", function () {
    loadTestCases();
    setupEventListeners();
    renderAll();
});

// Carga datos desde LocalStorage o inicializa con los datos de demostración
function loadTestCases() {
    const stored = localStorage.getItem("salud_qa_test_cases");
    if (stored) {
        try {
            testCases = JSON.parse(stored);
        } catch (e) {
            testCases = [...SEED_TEST_CASES];
            saveToStorage();
        }
    } else {
        testCases = [...SEED_TEST_CASES];
        saveToStorage();
    }
}

function saveToStorage() {
    localStorage.setItem("salud_qa_test_cases", JSON.stringify(testCases));
}

// Configuración de escuchadores de eventos
function setupEventListeners() {
    document.getElementById("btnNewTestCase").addEventListener("click", openNewModal);
    document.getElementById("testCaseForm").addEventListener("submit", handleFormSubmit);
    
    // Filtros
    document.getElementById("searchInput").addEventListener("input", filterAndRender);
    document.getElementById("filterModule").addEventListener("change", filterAndRender);
    document.getElementById("filterStatus").addEventListener("change", filterAndRender);
    document.getElementById("filterPriority").addEventListener("change", filterAndRender);
    document.getElementById("btnResetFilters").addEventListener("click", resetFilters);
}

// Generador de Código consecutivo (CP-001, CP-002, ...)
function generateNextCode() {
    let maxNum = 0;
    testCases.forEach(tc => {
        if (tc.code && tc.code.startsWith("CP-")) {
            const numPart = parseInt(tc.code.replace("CP-", ""), 10);
            if (!isNaN(numPart) && numPart > maxNum) {
                maxNum = numPart;
            }
        }
    });
    const nextNum = maxNum + 1;
    return "CP-" + String(nextNum).padStart(3, "0");
}

// Renderizado general
function renderAll() {
    updateMetrics();
    filterAndRender();
}

// Actualiza las tarjetas de métricas
function updateMetrics() {
    const total = testCases.length;
    const passed = testCases.filter(c => c.status === "Aprobado").length;
    const failed = testCases.filter(c => c.status === "Fallido").length;
    const pending = testCases.filter(c => c.status === "Pendiente").length;

    document.getElementById("statTotal").textContent = total;
    document.getElementById("statPassed").textContent = passed;
    document.getElementById("statFailed").textContent = failed;
    document.getElementById("statPending").textContent = pending;

    const passedPct = total > 0 ? Math.round((passed / total) * 100) : 0;
    const failedPct = total > 0 ? Math.round((failed / total) * 100) : 0;

    document.getElementById("statPassedPct").textContent = `${passedPct}% del total`;
    document.getElementById("statFailedPct").textContent = `${failedPct}% del total`;
}

// Filtrado de la tabla
function filterAndRender() {
    const searchVal = document.getElementById("searchInput").value.toLowerCase().trim();
    const moduleVal = document.getElementById("filterModule").value;
    const statusVal = document.getElementById("filterStatus").value;
    const priorityVal = document.getElementById("filterPriority").value;

    const filtered = testCases.filter(tc => {
        const matchesSearch = tc.code.toLowerCase().includes(searchVal) || tc.name.toLowerCase().includes(searchVal);
        const matchesModule = (moduleVal === "ALL" || tc.module === moduleVal);
        const matchesStatus = (statusVal === "ALL" || tc.status === statusVal);
        const matchesPriority = (priorityVal === "ALL" || tc.priority === priorityVal);

        return matchesSearch && matchesModule && matchesStatus && matchesPriority;
    });

    renderTable(filtered);
}

function resetFilters() {
    document.getElementById("searchInput").value = "";
    document.getElementById("filterModule").value = "ALL";
    document.getElementById("filterStatus").value = "ALL";
    document.getElementById("filterPriority").value = "ALL";
    filterAndRender();
}

// Renderiza la tabla HTML
function renderTable(casesList) {
    const tbody = document.getElementById("testCasesTableBody");
    tbody.innerHTML = "";

    if (casesList.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                    No se encontraron casos de prueba que coincidan con los criterios.
                </td>
            </tr>
        `;
        document.getElementById("tableFooterInfo").textContent = "Mostrando 0 casos de prueba.";
        return;
    }

    casesList.forEach(tc => {
        const tr = document.createElement("tr");

        // Badge para prioridad
        let priorityBadge = "";
        if (tc.priority === "Alta") priorityBadge = `<span class="badge badge-danger">Alta</span>`;
        else if (tc.priority === "Media") priorityBadge = `<span class="badge badge-warning">Media</span>`;
        else priorityBadge = `<span class="badge badge-secondary">Baja</span>`;

        // Badge para estado
        let statusBadge = "";
        if (tc.status === "Aprobado") statusBadge = `<span class="badge badge-success"><i class="fa-solid fa-check"></i> Aprobado</span>`;
        else if (tc.status === "Fallido") statusBadge = `<span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Fallido</span>`;
        else statusBadge = `<span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Pendiente</span>`;

        tr.innerHTML = `
            <td><strong>${escapeHtml(tc.code)}</strong></td>
            <td>${escapeHtml(tc.name)}</td>
            <td><span class="module-tag">${escapeHtml(tc.module)}</span></td>
            <td>${priorityBadge}</td>
            <td>${statusBadge}</td>
            <td>${tc.createdAt || 'N/A'}</td>
            <td class="action-buttons text-center">
                <button class="btn-icon text-blue" title="Ver detalle" onclick="viewCaseDetail('${tc.id}')">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <button class="btn-icon text-orange" title="Editar caso" onclick="openEditModal('${tc.id}')">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn-icon text-green" title="Ejecutar prueba" onclick="navigateToExecution('${tc.id}')">
                    <i class="fa-solid fa-play"></i>
                </button>
                <button class="btn-icon text-red" title="Eliminar caso" onclick="deleteCase('${tc.id}')">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById("tableFooterInfo").textContent = `Mostrando ${casesList.length} de ${testCases.length} casos de prueba registados.`;
}

// Abrir modal de nuevo caso
function openNewModal() {
    document.getElementById("testCaseForm").reset();
    document.getElementById("caseId").value = "";
    document.getElementById("caseCode").value = generateNextCode();
    document.getElementById("modalFormTitle").innerHTML = `<i class="fa-solid fa-vials"></i> Nuevo caso de prueba`;
    openModal("modalForm");
}

// Abrir modal de edición
function openEditModal(caseId) {
    const tc = testCases.find(c => c.id === caseId);
    if (!tc) return;

    document.getElementById("caseId").value = tc.id;
    document.getElementById("caseCode").value = tc.code;
    document.getElementById("caseName").value = tc.name;
    document.getElementById("caseModule").value = tc.module;
    document.getElementById("casePriority").value = tc.priority;
    document.getElementById("caseObjective").value = tc.objective;
    document.getElementById("casePreconditions").value = tc.preconditions || "";
    document.getElementById("caseTestData").value = tc.testData || "";
    document.getElementById("caseSteps").value = tc.steps;
    document.getElementById("caseExpectedResult").value = tc.expectedResult;

    document.getElementById("modalFormTitle").innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Editar caso de prueba: ${tc.code}`;
    openModal("modalForm");
}

// Guardar/Actualizar caso
function handleFormSubmit(e) {
    e.preventDefault();

    const id = document.getElementById("caseId").value;
    const code = document.getElementById("caseCode").value.trim();
    const name = document.getElementById("caseName").value.trim();
    const module = document.getElementById("caseModule").value;
    const priority = document.getElementById("casePriority").value;
    const objective = document.getElementById("caseObjective").value.trim();
    const preconditions = document.getElementById("casePreconditions").value.trim();
    const testData = document.getElementById("caseTestData").value.trim();
    const steps = document.getElementById("caseSteps").value.trim();
    const expectedResult = document.getElementById("caseExpectedResult").value.trim();

    if (!code || !name || !module || !objective || !steps || !expectedResult) {
        alert("Por favor complete todos los campos obligatorios del formulario.");
        return;
    }

    if (id) {
        // Edición de caso existente
        const index = testCases.findIndex(c => c.id === id);
        if (index !== -1) {
            testCases[index] = {
                ...testCases[index],
                name,
                module,
                priority,
                objective,
                preconditions,
                testData,
                steps,
                expectedResult
            };
        }
    } else {
        // Creación de nuevo caso
        const today = new Date().toISOString().split('T')[0];
        const newCase = {
            id: code,
            code: code,
            name: name,
            module: module,
            priority: priority,
            objective: objective,
            preconditions: preconditions,
            testData: testData,
            steps: steps,
            expectedResult: expectedResult,
            status: "Pendiente", // Estado inicial siempre Pendiente
            createdAt: today
        };
        testCases.push(newCase);
    }

    saveToStorage();
    closeModal("modalForm");
    renderAll();
}

// Ver Detalle
function viewCaseDetail(caseId) {
    const tc = testCases.find(c => c.id === caseId);
    if (!tc) return;

    let priorityBadge = tc.priority === "Alta" ? `<span class="badge badge-danger">Alta</span>` : (tc.priority === "Media" ? `<span class="badge badge-warning">Media</span>` : `<span class="badge badge-secondary">Baja</span>`);
    let statusBadge = tc.status === "Aprobado" ? `<span class="badge badge-success">Aprobado</span>` : (tc.status === "Fallido" ? `<span class="badge badge-danger">Fallido</span>` : `<span class="badge badge-pending">Pendiente</span>`);

    const detailHtml = `
        <div class="detail-header-block">
            <div class="detail-code">${escapeHtml(tc.code)}</div>
            <h2 class="detail-title">${escapeHtml(tc.name)}</h2>
            <div class="detail-meta">
                <span><strong>Módulo:</strong> ${escapeHtml(tc.module)}</span> &bull;
                <span><strong>Prioridad:</strong> ${priorityBadge}</span> &bull;
                <span><strong>Estado:</strong> ${statusBadge}</span> &bull;
                <span><strong>Fecha de creación:</strong> ${tc.createdAt || 'N/A'}</span>
            </div>
        </div>
        <hr class="divider">
        <div class="detail-section">
            <h4><i class="fa-solid fa-bullseye"></i> Objetivo de la prueba</h4>
            <p>${escapeHtml(tc.objective)}</p>
        </div>
        <div class="detail-row">
            <div class="detail-section col-6">
                <h4><i class="fa-solid fa-list-check"></i> Precondiciones</h4>
                <p>${tc.preconditions ? escapeHtml(tc.preconditions) : '<em>Sin precondiciones especificadas.</em>'}</p>
            </div>
            <div class="detail-section col-6">
                <h4><i class="fa-solid fa-database"></i> Datos de prueba</h4>
                <p>${tc.testData ? escapeHtml(tc.testData) : '<em>Sin datos de prueba específicos.</em>'}</p>
            </div>
        </div>
        <div class="detail-section">
            <h4><i class="fa-solid fa-bars-staggered"></i> Pasos de ejecución</h4>
            <pre class="steps-box">${escapeHtml(tc.steps)}</pre>
        </div>
        <div class="detail-section">
            <h4><i class="fa-solid fa-circle-check"></i> Resultado esperado</h4>
            <div class="expected-box">${escapeHtml(tc.expectedResult)}</div>
        </div>
    `;

    document.getElementById("detailModalBody").innerHTML = detailHtml;
    document.getElementById("btnEditFromDetail").onclick = function () {
        closeModal("modalDetail");
        openEditModal(tc.id);
    };

    openModal("modalDetail");
}

// Eliminar caso
function deleteCase(caseId) {
    const tc = testCases.find(c => c.id === caseId);
    if (!tc) return;

    if (confirm(`¿Está seguro de que desea eliminar el caso de prueba ${tc.code}: "${tc.name}"?`)) {
        testCases = testCases.filter(c => c.id !== caseId);
        saveToStorage();
        renderAll();
    }
}

// Acción para pasar al módulo de ejecución de pruebas
function navigateToExecution(caseId) {
    alert(`Redirigiendo al módulo de Ejecución de Pruebas para el caso ${caseId}.\n\n(Nota: El módulo de Ejecución se implementará por separado en la siguiente fase).`);
}

// Controladores de Modal
function openModal(modalId) {
    const m = document.getElementById(modalId);
    if (m) m.classList.add("active");
}

function closeModal(modalId) {
    const m = document.getElementById(modalId);
    if (m) m.classList.remove("active");
}

// Auxiliar para evitar inyección HTML
function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}