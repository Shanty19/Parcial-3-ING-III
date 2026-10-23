<?php include 'includes/header.php'; ?>

<!-- Content Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Casos de prueba</h1>
        <p class="page-subtitle">Gestión y documentación de los casos de prueba de Clínica Salud+.</p>
    </div>
    <div>
        <button id="btnNewTestCase" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nuevo caso de prueba
        </button>
    </div>
</div>

<!-- Metrics Cards Summary -->
<div class="stats-grid">
    <div class="stat-card border-blue">
        <div class="stat-icon bg-light-blue text-blue">
            <i class="fa-solid fa-list-check"></i>
        </div>
        <div class="stat-details">
            <span class="stat-title">Total de Casos</span>
            <span class="stat-value" id="statTotal">0</span>
            <span class="stat-subtext">Casos documentados</span>
        </div>
    </div>

    <div class="stat-card border-green">
        <div class="stat-icon bg-light-green text-green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-details">
            <span class="stat-title">Aprobados</span>
            <span class="stat-value text-green" id="statPassed">0</span>
            <span class="stat-subtext" id="statPassedPct">0% del total</span>
        </div>
    </div>

    <div class="stat-card border-red">
        <div class="stat-icon bg-light-red text-red">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="stat-details">
            <span class="stat-title">Fallidos</span>
            <span class="stat-value text-red" id="statFailed">0</span>
            <span class="stat-subtext" id="statFailedPct">0% del total</span>
        </div>
    </div>

    <div class="stat-card border-yellow">
        <div class="stat-icon bg-light-yellow text-yellow">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-details">
            <span class="stat-title">Pendientes</span>
            <span class="stat-value text-yellow" id="statPending">0</span>
            <span class="stat-subtext">Sin ejecutar</span>
        </div>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card filter-card">
    <div class="filter-grid">
        <div class="filter-group search-group">
            <label for="searchInput"><i class="fa-solid fa-magnifying-glass"></i> Buscar</label>
            <input type="text" id="searchInput" placeholder="Buscar por código o nombre del caso..." class="form-control">
        </div>
        <div class="filter-group">
            <label for="filterModule"><i class="fa-solid fa-cubes"></i> Módulo</label>
            <select id="filterModule" class="form-control">
                <option value="ALL">Todos los módulos</option>
                <option value="Gestión de pacientes">Gestión de pacientes</option>
                <option value="Búsqueda de pacientes">Búsqueda de pacientes</option>
                <option value="Consulta de doctores y especialidades">Consulta de doctores y especialidades</option>
                <option value="Comunicación con el servicio SOAP">Comunicación con el servicio SOAP</option>
                <option value="Historial de búsquedas">Historial de búsquedas</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterStatus"><i class="fa-solid fa-flag"></i> Estado</label>
            <select id="filterStatus" class="form-control">
                <option value="ALL">Todos los estados</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Aprobado">Aprobado</option>
                <option value="Fallido">Fallido</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filterPriority"><i class="fa-solid fa-arrow-up-wide-short"></i> Prioridad</label>
            <select id="filterPriority" class="form-control">
                <option value="ALL">Todas las prioridades</option>
                <option value="Alta">Alta</option>
                <option value="Media">Media</option>
                <option value="Baja">Baja</option>
            </select>
        </div>
        <div class="filter-group btn-reset-container">
            <button id="btnResetFilters" class="btn btn-secondary btn-block">
                <i class="fa-solid fa-rotate"></i> Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Test Cases Table Card -->
<div class="card table-card">
    <div class="table-responsive">
        <table class="data-table" id="testCasesTable">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre del caso</th>
                    <th>Módulo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Fecha de creación</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="testCasesTableBody">
                <!-- Se llena dinámicamente con JavaScript -->
            </tbody>
        </table>
    </div>
    <div class="table-footer" id="tableFooterInfo">
        Mostrando 0 casos de prueba.
    </div>
</div>

<!-- Modal: Formulario para Crear / Editar Caso -->
<div id="modalForm" class="modal-backdrop">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalFormTitle"><i class="fa-solid fa-vials"></i> Nuevo caso de prueba</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalForm')">&times;</button>
            </div>
            <form id="testCaseForm">
                <div class="modal-body">
                    <input type="hidden" id="caseId">
                    <div class="form-row">
                        <div class="form-group col-4">
                            <label for="caseCode">Código <span class="required">*</span></label>
                            <input type="text" id="caseCode" class="form-control" placeholder="CP-001" required readonly style="background-color: var(--bg-neutral); cursor: not-allowed;">
                            <small class="form-hint">Generado automáticamente</small>
                        </div>
                        <div class="form-group col-8">
                            <label for="caseName">Nombre del caso <span class="required">*</span></label>
                            <input type="text" id="caseName" class="form-control" placeholder="Ej: Registro exitoso de paciente con datos válidos" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="caseModule">Módulo <span class="required">*</span></label>
                            <select id="caseModule" class="form-control" required>
                                <option value="" disabled selected>Seleccione un módulo</option>
                                <option value="Gestión de pacientes">Gestión de pacientes</option>
                                <option value="Búsqueda de pacientes">Búsqueda de pacientes</option>
                                <option value="Consulta de doctores y especialidades">Consulta de doctores y especialidades</option>
                                <option value="Comunicación con el servicio SOAP">Comunicación con el servicio SOAP</option>
                                <option value="Historial de búsquedas">Historial de búsquedas</option>
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="casePriority">Prioridad <span class="required">*</span></label>
                            <select id="casePriority" class="form-control" required>
                                <option value="Alta">Alta</option>
                                <option value="Media" selected>Media</option>
                                <option value="Baja">Baja</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="caseObjective">Objetivo de la prueba <span class="required">*</span></label>
                        <textarea id="caseObjective" class="form-control" rows="2" placeholder="Describa el comportamiento específico que desea verificar..." required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="casePreconditions">Precondiciones</label>
                            <textarea id="casePreconditions" class="form-control" rows="2" placeholder="Requisitos previos (ej: El servidor SOAP debe estar activo)"></textarea>
                        </div>
                        <div class="form-group col-6">
                            <label for="caseTestData">Datos de prueba</label>
                            <textarea id="caseTestData" class="form-control" rows="2" placeholder="Datos requeridos (ej: Nombre: Juan, Cédula: 8-123-456)"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="caseSteps">Pasos de ejecución <span class="required">*</span></label>
                        <textarea id="caseSteps" class="form-control" rows="4" placeholder="Escriba las instrucciones paso a paso:&#10;1. Acceder al módulo...&#10;2. Ingresar la cédula...&#10;3. Hacer clic en Buscar..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="caseExpectedResult">Resultado esperado <span class="required">*</span></label>
                        <textarea id="caseExpectedResult" class="form-control" rows="2" placeholder="Describa la respuesta esperada del sistema..." required></textarea>
                    </div>

                    <div class="alert alert-info" style="margin-top: 15px; font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-info"></i> Nota: Todo caso nuevo se registra con estado inicial <strong>Pendiente</strong>. Las pruebas deben ejecutarse individualmente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalForm')">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveCase">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Caso de Prueba
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Vista de Detalle del Caso -->
<div id="modalDetail" class="modal-backdrop">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-file-lines"></i> Detalle del Caso de Prueba</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalDetail')">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Se llena dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetail')">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnEditFromDetail">
                    <i class="fa-solid fa-pen-to-square"></i> Editar Caso
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/casos_prueba.js"></script>

<?php include 'includes/footer.php'; ?>