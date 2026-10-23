<?php include 'includes/header.php'; ?>

<!-- Content Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h1 style="margin: 0; color: #1e293b; font-size: 1.5rem; font-weight: 700;">Casos de prueba</h1>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Gestión y documentación de los casos de prueba de Clínica Salud+.</p>
    </div>
    <div>
        <button id="btnNewTestCase" style="background-color: #3b82f6; color: white; padding: 10px 18px; border-radius: 6px; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <i class="fa-solid fa-plus"></i> Nuevo caso de prueba
        </button>
    </div>
</div>

<!-- Metrics Cards Summary -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">
            <i class="fa-solid fa-list-check" style="color: #3b82f6; margin-right: 6px;"></i> Total de Casos
        </span>
        <strong style="font-size: 1.8rem; color: #1e293b; display: block;" id="statTotal">0</strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;">Casos documentados</span>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">
            <i class="fa-solid fa-circle-check" style="color: #10b981; margin-right: 6px;"></i> Aprobados
        </span>
        <strong style="font-size: 1.8rem; color: #10b981; display: block;" id="statPassed">0</strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;" id="statPassedPct">0% del total</span>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #ef4444; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">
            <i class="fa-solid fa-circle-xmark" style="color: #ef4444; margin-right: 6px;"></i> Fallidos
        </span>
        <strong style="font-size: 1.8rem; color: #ef4444; display: block;" id="statFailed">0</strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;" id="statFailedPct">0% del total</span>
    </div>
    
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 8px;">
            <i class="fa-solid fa-clock" style="color: #f59e0b; margin-right: 6px;"></i> Pendientes
        </span>
        <strong style="font-size: 1.8rem; color: #f59e0b; display: block;" id="statPending">0</strong>
        <span style="color: #94a3b8; font-size: 0.75rem; display: block; margin-top: 4px;">Sin ejecutar</span>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card filter-card" style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <div class="filter-grid" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div class="filter-group search-group" style="flex: 2; min-width: 240px;">
            <label for="searchInput" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-magnifying-glass"></i> Buscar</label>
            <input type="text" id="searchInput" placeholder="Buscar por código o nombre del caso..." class="form-control" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;">
        </div>
        <div class="filter-group" style="flex: 1; min-width: 180px;">
            <label for="filterModule" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-cubes"></i> Módulo</label>
            <select id="filterModule" class="form-control" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los módulos</option>
                <option value="Gestión de pacientes">Gestión de pacientes</option>
                <option value="Búsqueda de pacientes">Búsqueda de pacientes</option>
                <option value="Consulta de doctores y especialidades">Consulta de doctores y especialidades</option>
                <option value="Comunicación con el servicio SOAP">Comunicación con el servicio SOAP</option>
                <option value="Historial de búsquedas">Historial de búsquedas</option>
            </select>
        </div>
        <div class="filter-group" style="flex: 1; min-width: 150px;">
            <label for="filterStatus" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-flag"></i> Estado</label>
            <select id="filterStatus" class="form-control" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todos los estados</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Aprobado">Aprobado</option>
                <option value="Fallido">Fallido</option>
            </select>
        </div>
        <div class="filter-group" style="flex: 1; min-width: 150px;">
            <label for="filterPriority" style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 6px;"><i class="fa-solid fa-arrow-up-wide-short"></i> Prioridad</label>
            <select id="filterPriority" class="form-control" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; background: white; box-sizing: border-box;">
                <option value="ALL">Todas</option>
                <option value="Alta">Alta</option>
                <option value="Media">Media</option>
                <option value="Baja">Baja</option>
            </select>
        </div>
        <div class="filter-group btn-reset-container">
            <button id="btnResetFilters" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 6px; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-rotate"></i> Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Test Cases Table Card -->
<div class="card table-card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="data-table" id="testCasesTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: #0f172a; color: white;">
                    <th style="padding: 12px 16px;">Código</th>
                    <th style="padding: 12px 16px;">Nombre del caso</th>
                    <th style="padding: 12px 16px;">Módulo</th>
                    <th style="padding: 12px 16px;">Prioridad</th>
                    <th style="padding: 12px 16px;">Estado</th>
                    <th style="padding: 12px 16px;">Fecha de creación</th>
                    <th style="padding: 12px 16px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="testCasesTableBody">
                <!-- Se llena dinámicamente con JavaScript -->
            </tbody>
        </table>
    </div>
    <div class="table-footer" id="tableFooterInfo" style="padding: 16px 20px; font-size: 0.85rem; color: #64748b; border-top: 1px solid #f1f5f9;">
        Mostrando 0 casos de prueba.
    </div>
</div>

<!-- Modal: Formulario para Crear / Editar Caso (Con scroll interno y ajustado) -->
<div id="modalForm" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(15, 23, 42, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; padding: 10px;">
    <div style="width: 100%; max-width: 750px; max-height: 92vh; background: #ffffff; border-radius: 10px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; overflow: hidden;">
        <div style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc; flex-shrink: 0;">
            <h3 id="modalFormTitle" style="margin: 0; font-size: 1.1rem; color: #1e293b;"><i class="fa-solid fa-vials"></i> Nuevo caso de prueba</h3>
            <button type="button" onclick="closeModal('modalForm')" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <form id="testCaseForm" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
            <div style="padding: 16px 20px; overflow-y: auto; flex-grow: 1; max-height: calc(92vh - 120px);">
                <input type="hidden" id="caseId">
                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div style="flex: 0 0 calc(33.333% - 10px);">
                        <label for="caseCode" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Código <span style="color: red;">*</span></label>
                        <input type="text" id="caseCode" placeholder="CP-001" required readonly style="background-color: #f1f5f9; cursor: not-allowed; width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                        <small style="font-size: 0.75rem; color: #64748b;">Generado automáticamente</small>
                    </div>
                    <div style="flex: 0 0 calc(66.666% - 5px);">
                        <label for="caseName" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Nombre del caso <span style="color: red;">*</span></label>
                        <input type="text" id="caseName" placeholder="Ej: Registro exitoso de paciente con datos válidos" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div style="flex: 0 0 calc(50% - 7.5px);">
                        <label for="caseModule" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Módulo <span style="color: red;">*</span></label>
                        <select id="caseModule" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                            <option value="" disabled selected>Seleccione un módulo</option>
                            <option value="Gestión de pacientes">Gestión de pacientes</option>
                            <option value="Búsqueda de pacientes">Búsqueda de pacientes</option>
                            <option value="Consulta de doctores y especialidades">Consulta de doctores y especialidades</option>
                            <option value="Comunicación con el servicio SOAP">Comunicación con el servicio SOAP</option>
                            <option value="Historial de búsquedas">Historial de búsquedas</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 calc(50% - 7.5px);">
                        <label for="casePriority" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Prioridad <span style="color: red;">*</span></label>
                        <select id="casePriority" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; box-sizing: border-box;">
                            <option value="Alta">Alta</option>
                            <option value="Media" selected>Media</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="caseObjective" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Objetivo de la prueba <span style="color: red;">*</span></label>
                    <textarea id="caseObjective" rows="2" placeholder="Describa el comportamiento específico que desea verificar..." required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div style="flex: 0 0 calc(50% - 7.5px);">
                        <label for="casePreconditions" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Precondiciones</label>
                        <textarea id="casePreconditions" rows="2" placeholder="Requisitos previos..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                    </div>
                    <div style="flex: 0 0 calc(50% - 7.5px);">
                        <label for="caseTestData" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Datos de prueba</label>
                        <textarea id="caseTestData" rows="2" placeholder="Datos requeridos..." style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="caseSteps" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Pasos de ejecución <span style="color: red;">*</span></label>
                    <textarea id="caseSteps" rows="3" placeholder="1. Acceder al módulo...&#10;2. Ingresar datos..." required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="caseExpectedResult" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: #475569;">Resultado esperado <span style="color: red;">*</span></label>
                    <textarea id="caseExpectedResult" rows="2" placeholder="Describa la respuesta esperada..." required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-top: 10px; font-size: 0.85rem; background: #e0f2fe; padding: 10px; border-radius: 6px; color: #0369a1;">
                    <i class="fa-solid fa-circle-info"></i> Nota: Todo caso nuevo se registra con estado inicial <strong>Pendiente</strong>.
                </div>
            </div>
            <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px; background-color: #f8fafc; flex-shrink: 0;">
                <button type="button" onclick="closeModal('modalForm')" style="background: #e2e8f0; color: #334155; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" id="btnSaveCase" style="background: #3b82f6; color: white; padding: 8px 18px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Caso de Prueba
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Vista de Detalle del Caso -->
<div id="modalDetail" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(15, 23, 42, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; padding: 10px;">
    <div style="width: 100%; max-width: 750px; max-height: 90vh; background: #ffffff; border-radius: 10px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
            <h3 style="margin: 0; font-size: 1.1rem;"><i class="fa-solid fa-file-lines"></i> Detalle del Caso de Prueba</h3>
            <button type="button" onclick="closeModal('modalDetail')" style="background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>
        <div id="detailModalBody" style="padding: 20px; overflow-y: auto; max-height: calc(90vh - 120px);">
            <!-- Se llena dinámicamente -->
        </div>
        <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px; background-color: #f8fafc;">
            <button type="button" onclick="closeModal('modalDetail')" style="background: #e2e8f0; color: #334155; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Cerrar</button>
            <button type="button" id="btnEditFromDetail" style="background: #3b82f6; color: white; padding: 8px 16px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                <i class="fa-solid fa-pen-to-square"></i> Editar Caso
            </button>
        </div>
    </div>
</div>

<script src="js/casos_prueba.js"></script>

<?php include 'includes/footer.php'; ?>