<?php
$titulo_pestana   = "Dashboard del Tester";
$titulo_pagina    = "Dashboard del Tester";
$subtitulo_pagina = "Panel de control de calidad del software";
include 'includes/header.php';
?>

<div class="aviso-demo">
  <span class="pildora-demo">DEMO</span>
  Las cifras y filas de esta pantalla son datos de demostración, no resultados reales.
</div>

<!-- ========== Tarjetas de estadísticas ========== -->
<div class="tarjetas">
  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono">📋</div>
      <span class="pildora-demo">DEMO</span>
    </div>
    <div class="cifra">24</div>
    <h3>Total de casos de prueba</h3>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono icono-exito">✅</div>
      <span class="pildora-demo">DEMO</span>
    </div>
    <div class="cifra">15</div>
    <h3>Pruebas aprobadas</h3>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono icono-error">❌</div>
      <span class="pildora-demo">DEMO</span>
    </div>
    <div class="cifra">4</div>
    <h3>Pruebas fallidas</h3>
  </div>

  <div class="tarjeta">
    <div class="tarjeta-cabecera">
      <div class="icono icono-aviso">⏳</div>
      <span class="pildora-demo">DEMO</span>
    </div>
    <div class="cifra">5</div>
    <h3>Pruebas pendientes</h3>
  </div>
</div>

<!-- ========== Resumen de calidad + Acciones rápidas ========== -->
<div class="fila-doble">

  <section class="resumen">
    <div class="resumen-cabecera">
      <h2>Resumen de calidad</h2>
      <span class="pildora-demo pildora-demo-clara">DEMO</span>
    </div>

    <div class="anillo">
      <svg viewBox="0 0 120 120" role="img" aria-label="79 % de aprobación">
        <circle class="anillo-fondo" cx="60" cy="60" r="54"/>
        <circle class="anillo-valor" cx="60" cy="60" r="54"
                stroke-dasharray="339.29" stroke-dashoffset="71.6"
                transform="rotate(-90 60 60)"/>
      </svg>
      <div class="anillo-texto">
        <strong>79 %</strong>
        <span>de aprobación</span>
      </div>
    </div>

    <ul class="leyenda">
      <li><span class="punto-color c-exito"></span>Aprobadas<b>15</b></li>
      <li><span class="punto-color c-error"></span>Fallidas<b>4</b></li>
      <li><span class="punto-color c-aviso"></span>Pendientes<b>5</b></li>
    </ul>
    <p class="resumen-nota">15 aprobadas de 19 pruebas ejecutadas</p>
  </section>

  <section class="panel">
    <h2 class="panel-titulo">Acciones rápidas</h2>
    <div class="acciones-rapidas">
      <button type="button" class="btn-accion btn-accion-primaria">
        <span class="accion-icono">➕</span>
        <span>Crear caso de prueba<small>Documenta un nuevo caso</small></span>
      </button>
      <button type="button" class="btn-accion">
        <span class="accion-icono">▶️</span>
        <span>Ejecutar prueba<small>Registra un resultado</small></span>
      </button>
      <button type="button" class="btn-accion">
        <span class="accion-icono">🐞</span>
        <span>Registrar incidente<small>Reporta un defecto</small></span>
      </button>
      <button type="button" class="btn-accion">
        <span class="accion-icono">🗓️</span>
        <span>Ver plan de pruebas<small>Consulta la planificación</small></span>
      </button>
    </div>
  </section>

</div>

<!-- ========== Pruebas recientes ========== -->
<div class="categoria-bloque">
  <div class="categoria-header">
    <span class="badge">Pruebas recientes</span>
    <span class="conteo">Datos de demostración</span>
  </div>

  <div class="tabla-wrap">
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Caso de prueba</th>
          <th>Módulo</th>
          <th>Fecha</th>
          <th>Resultado</th>
          <th class="col-accion">Acción</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><span class="codigo-caso">CP-001</span></td>
          <td>Registro de pacientes</td>
          <td><span class="badge-servicio">Gestión de pacientes</span></td>
          <td class="fecha-celda">20/10/2026</td>
          <td><span class="estado estado-aprobado">Aprobado</span></td>
          <td class="col-accion"><button type="button" class="btn btn-secundario btn-sm">👁️ Ver</button></td>
        </tr>
        <tr>
          <td><span class="codigo-caso">CP-002</span></td>
          <td>Búsqueda de pacientes</td>
          <td><span class="badge-servicio">Gestión de pacientes</span></td>
          <td class="fecha-celda">21/10/2026</td>
          <td><span class="estado estado-fallido">Fallido</span></td>
          <td class="col-accion"><button type="button" class="btn btn-secundario btn-sm">👁️ Ver</button></td>
        </tr>
        <tr>
          <td><span class="codigo-caso">CP-003</span></td>
          <td>Consulta de doctores</td>
          <td><span class="badge-servicio">Doctores y especialidades</span></td>
          <td class="fecha-celda">22/10/2026</td>
          <td><span class="estado estado-pendiente">Pendiente</span></td>
          <td class="col-accion"><button type="button" class="btn btn-secundario btn-sm">👁️ Ver</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>