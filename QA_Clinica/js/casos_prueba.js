/**
 * Módulo de Gestión de Casos de Prueba - Clínica Salud+ QA
 */

const SEED_TEST_CASES = [
    {
        id: "CP-001",
        code: "CP-001",
        name: "Registro exitoso de un nuevo paciente",
        module: "Gestión de pacientes",
        objective: "Verificar que el sistema guarde correctamente la información personal y médica de un paciente en la base de datos.",
        preconditions: "Acceso al módulo de pacientes con credenciales activas.",
        testData: "Nombre: Carlos Mendoza, Cédula: 8-900-1122, Teléfono: 6789-0123",
        steps: "1. Navegar a la sección 'Gestión de pacientes'.\n2. Hacer clic en 'Agregar Paciente'.\n3. Llenar los campos y guardar.",
        expectedResult: "Muestra mensaje de confirmación y el paciente figura en el listado.",
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
        preconditions: "Existir paciente con cédula 8-900-1122.",
        testData: "Cédula: 8-900-1122",
        steps: "1. Ir a Búsqueda de Pacientes.\n2. Escribir la cédula.\n3. Presionar Buscar.",
        expectedResult: "El sistema muestra la ficha coincidente.",
        priority: "Alta",
        status: "Pendiente",
        createdAt: "2026-10-21"
    }
];

let testCases = [];

// Inicialización segura cuando el DOM está completamente cargado
document.addEventListener("DOMContentLoaded", function () {
    loadTestCases();
    setupEventListeners();
    renderAll();
});

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

function setupEventListeners() {
    const btnNew = document.getElementById("btnNewTestCase");
    if (btnNew) {
        btnNew.addEventListener("click", openNewModal);
    }

    const form = document.getElementById("testCaseForm");
    if (form) {
        form.addEventListener("submit", handleFormSubmit);
    }
    
    // Filtros
    const searchInput = document.getElementById("searchInput");
    if (searchInput) searchInput.addEventListener("input", filterAndRender);

    const filterModule = document.getElementById("filterModule");
    if (filterModule) filterModule.addEventListener("change", filterAndRender);

    const filterStatus = document.getElementById("filterStatus");
    if (filterStatus) filterStatus.addEventListener("change", filterAndRender);

    const filterPriority = document.getElementById("filterPriority");
    if (filterPriority) filterPriority.addEventListener("change", filterAndRender);

    const btnReset = document.getElementById("btnResetFilters");
    if (btnReset) btnReset.addEventListener("click", resetFilters);
}

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
    return "CP-" + String(maxNum + 1).padStart(3, "0");
}

function renderAll() {
    updateMetrics();
    filterAndRender();
}

function updateMetrics() {
    const total = testCases.length;
    const passed = testCases.filter(c => c.status === "Aprobado").length;
    const failed = testCases.filter(c => c.status === "Fallido").length;
    const pending = testCases.filter(c => c.status === "Pendiente").length;

    const elTotal = document.getElementById("statTotal");
    const elPassed = document.getElementById("statPassed");
    const elFailed = document.getElementById("statFailed");
    const elPending = document.getElementById("statPending");

    if (elTotal) elTotal.textContent = total;
    if (elPassed) elPassed.textContent = passed;
    if (elFailed) elFailed.textContent = failed;
    if (elPending) elPending.textContent = pending;

    const passedPct = total > 0 ? Math.round((passed / total) * 100) : 0;
    const failedPct = total > 0 ? Math.round((failed / total) * 100) : 0;

    const elPassedPct = document.getElementById("statPassedPct");
    const elFailedPct = document.getElementById("statFailedPct");

    if (elPassedPct) elPassedPct.textContent = `${passedPct}% del total`;
    if (elFailedPct) elFailedPct.textContent = `${failedPct}% del total`;
}

function filterAndRender() {
    const searchVal = document.getElementById("searchInput")?.value.toLowerCase().trim() || "";
    const moduleVal = document.getElementById("filterModule")?.value || "ALL";
    const statusVal = document.getElementById("filterStatus")?.value || "ALL";
    const priorityVal = document.getElementById("filterPriority")?.value || "ALL";

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
    if (document.getElementById("searchInput")) document.getElementById("searchInput").value = "";
    if (document.getElementById("filterModule")) document.getElementById("filterModule").value = "ALL";
    if (document.getElementById("filterStatus")) document.getElementById("filterStatus").value = "ALL";
    if (document.getElementById("filterPriority")) document.getElementById("filterPriority").value = "ALL";
    filterAndRender();
}

function renderTable(casesList) {
    const tbody = document.getElementById("testCasesTableBody");
    if (!tbody) return;
    tbody.innerHTML = "";

    if (casesList.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 24px; color: #94a3b8;">
                    No se encontraron casos de prueba que coincidan con los criterios.
                </td>
            </tr>
        `;
        document.getElementById("tableFooterInfo").textContent = "Mostrando 0 casos de prueba.";
        return;
    }

    casesList.forEach(tc => {
        const tr = document.createElement("tr");

        let priorityBadge = tc.priority === "Alta" ? `<span style="background:#fee2e2; color:#991b1b; padding:3px 8px; border-radius:10px; font-weight:600; font-size:0.75rem;">Alta</span>` : (tc.priority === "Media" ? `<span style="background:#fef3c7; color:#92400e; padding:3px 8px; border-radius:10px; font-weight:600; font-size:0.75rem;">Media</span>` : `<span style="background:#f3f4f6; color:#4b5563; padding:3px 8px; border-radius:10px; font-weight:600; font-size:0.75rem;">Baja</span>`);

        let statusBadge = tc.status === "Aprobado" ? `<span style="background:#d1fae5; color:#065f46; padding:3px 8px; border-radius:10px; font-weight:600; font-size:0.75rem;">Aprobado</span>` : (tc.status === "Fallido" ? `<span style="background:#fee2e2; color:#991b1b; padding:3px 8px; border-radius:10px; font-weight:600; font-size:0.75rem;">Fallido</span>` : `<span style="background:#e0f2fe; color:#075985; padding:3px 8px; border-radius:10px; font-weight:600; font-size:0.75rem;">Pendiente</span>`);

        tr.innerHTML = `
            <td style="padding: 12px 16px; font-weight: 700;">${escapeHtml(tc.code)}</td>
            <td style="padding: 12px 16px;">${escapeHtml(tc.name)}</td>
            <td style="padding: 12px 16px;">${escapeHtml(tc.module)}</td>
            <td style="padding: 12px 16px;">${priorityBadge}</td>
            <td style="padding: 12px 16px;">${statusBadge}</td>
            <td style="padding: 12px 16px;">${tc.createdAt || 'N/A'}</td>
            <td style="padding: 12px 16px; text-align: center;">
                <button onclick="viewCaseDetail('${tc.id}')" style="background:none; border:none; cursor:pointer; color:#0284c7; margin:0 4px;" title="Ver detalle">👁️</button>
                <button onclick="openEditModal('${tc.id}')" style="background:none; border:none; cursor:pointer; color:#d97706; margin:0 4px;" title="Editar">✏️</button>
                <button onclick="deleteCase('${tc.id}')" style="background:none; border:none; cursor:pointer; color:#dc2626; margin:0 4px;" title="Eliminar">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById("tableFooterInfo").textContent = `Mostrando ${casesList.length} de ${testCases.length} casos registrados.`;
}

// Funciones globales accesibles desde los botones onclick del HTML
window.openNewModal = function() {
    document.getElementById("testCaseForm").reset();
    document.getElementById("caseId").value = "";
    document.getElementById("caseCode").value = generateNextCode();
    document.getElementById("modalFormTitle").innerHTML = `<i class="fa-solid fa-vials"></i> Nuevo caso de prueba`;
    openModal("modalForm");
};

window.openEditModal = function(caseId) {
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

    document.getElementById("modalFormTitle").innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Editar caso: ${tc.code}`;
    openModal("modalForm");
};

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
        alert("Por favor complete todos los campos obligatorios.");
        return;
    }

    if (id) {
        const index = testCases.findIndex(c => c.id === id);
        if (index !== -1) {
            testCases[index] = { ...testCases[index], name, module, priority, objective, preconditions, testData, steps, expectedResult };
        }
    } else {
        const today = new Date().toISOString().split('T')[0];
        const newCase = {
            id: code,
            code: code,
            name,
            module,
            priority,
            objective,
            preconditions,
            testData,
            steps,
            expectedResult,
            status: "Pendiente",
            createdAt: today
        };
        testCases.push(newCase);
    }

    saveToStorage();
    closeModal("modalForm");
    renderAll();
}

window.viewCaseDetail = function(caseId) {
    const tc = testCases.find(c => c.id === caseId);
    if (!tc) return;

    const detailHtml = `
        <div style="margin-bottom: 12px;">
            <span style="font-weight:700; color:#2563eb;">${escapeHtml(tc.code)}</span>
            <h3 style="margin:4px 0; font-size:1.2rem;">${escapeHtml(tc.name)}</h3>
            <p style="color:#64748b; font-size:0.85rem;">Módulo: ${escapeHtml(tc.module)} | Prioridad: ${tc.priority} | Estado: ${tc.status}</p>
        </div>
        <hr style="border:0; border-top:1px solid #e2e8f0; margin:12px 0;">
        <div style="margin-bottom:12px;">
            <h4 style="font-size:0.9rem; color:#475569; margin-bottom:4px;">Objetivo:</h4>
            <p style="margin:0; font-size:0.9rem;">${escapeHtml(tc.objective)}</p>
        </div>
        <div style="margin-bottom:12px;">
            <h4 style="font-size:0.9rem; color:#475569; margin-bottom:4px;">Pasos de ejecución:</h4>
            <pre style="background:#f8fafc; padding:10px; border-radius:6px; font-family:inherit; font-size:0.85rem; white-space:pre-wrap; margin:0;">${escapeHtml(tc.steps)}</pre>
        </div>
        <div>
            <h4 style="font-size:0.9rem; color:#475569; margin-bottom:4px;">Resultado esperado:</h4>
            <div style="background:#f0fdf4; padding:10px; border-radius:6px; border:1px solid #bbf7d0; font-size:0.85rem;">${escapeHtml(tc.expectedResult)}</div>
        </div>
    `;

    document.getElementById("detailModalBody").innerHTML = detailHtml;
    document.getElementById("btnEditFromDetail").onclick = function () {
        closeModal("modalDetail");
        openEditModal(tc.id);
    };

    openModal("modalDetail");
};

window.deleteCase = function(caseId) {
    const tc = testCases.find(c => c.id === caseId);
    if (!tc) return;

    if (confirm(`¿Desea eliminar el caso ${tc.code}: "${tc.name}"?`)) {
        testCases = testCases.filter(c => c.id !== caseId);
        saveToStorage();
        renderAll();
    }
};

window.openModal = function(modalId) {
    const m = document.getElementById(modalId);
    if (m) m.style.display = "flex";
};

window.closeModal = function(modalId) {
    const m = document.getElementById(modalId);
    if (m) m.style.display = "none";
};

function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}