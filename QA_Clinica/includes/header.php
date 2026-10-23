<?php
// Detecta la página actual para resaltarla en el menú lateral
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Valores por defecto (cada página puede definirlos antes de incluir este archivo)
$titulo_pagina     = isset($titulo_pagina) ? $titulo_pagina : 'Dashboard del Tester';
$subtitulo_pagina = isset($subtitulo_pagina) ? $subtitulo_pagina : 'Panel de control de calidad del software';

// Opciones del menú lateral actualizadas con los archivos correspondientes
$menu = [
  ['archivo' => 'dashboard.php',          'icono' => '📊', 'texto' => 'Dashboard'],
  ['archivo' => 'casos_prueba.php',       'icono' => '📋', 'texto' => 'Casos de prueba'],
  ['archivo' => 'matriz_equivalencia.php','icono' => '▶️', 'texto' => 'Matriz de Clases de Equivalencia'],
  ['archivo' => 'analisis_valor_limite.php', 'icono' => '📈', 'texto' => 'Análisis de valor límite'],
  ['archivo' => 'tabla_decision.php', 'icono' => '📐', 'texto' => 'Tabla de Decisión'],
  ['archivo' => 'cobertura_caja_blanca.php', 'icono' => '🧪', 'texto' => 'Cobertura Caja Blanca'],
  ['archivo' => 'plan_pruebas.php',       'icono' => '🗓️', 'texto' => 'Plan de pruebas'],
  ['archivo' => 'registro_incidentes.php','icono' => '🐞', 'texto' => 'Registro de incidentes'],
  ['archivo' => 'portafolio_evidencias.php','icono' => '📁', 'texto' => 'Portafolio de evidencias'],
  

];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($titulo_pestana) ? $titulo_pestana . ' | QA Clínica Salud+' : 'QA Clínica Salud+'; ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="overlay" id="overlay"></div>

<!-- ========== Barra lateral ========== -->
<aside class="sidebar" id="sidebar">
  <a href="dashboard.php" class="logo">
    <!-- Logo: estetoscopio en SVG (el mismo de Clínica Salud+) -->
    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M20 8V22C20 27.5228 24.4772 32 30 32C35.5228 32 40 27.5228 40 22V8"
            stroke="#ffffff" stroke-width="3.2" stroke-linecap="round"/>
      <line x1="20" y1="8" x2="20" y2="4" stroke="#ffffff" stroke-width="3.2" stroke-linecap="round"/>
      <line x1="40" y1="8" x2="40" y2="4" stroke="#ffffff" stroke-width="3.2" stroke-linecap="round"/>
      <path d="M30 32V42C30 48 34.5 52 40 52C45.5 52 50 48 50 42V38"
            stroke="#ffffff" stroke-width="3.2" stroke-linecap="round"/>
      <circle cx="50" cy="38" r="5" stroke="#ffffff" stroke-width="3.2"/>
      <circle cx="50" cy="38" r="1.6" fill="#ffffff"/>
    </svg>
    <span class="logo-text">
      <span class="nombre">QA Clínica Salud+</span>
      <span class="slogan">Control de calidad</span>
    </span>
  </a>

  <nav class="menu-lateral">
    <?php foreach ($menu as $item): ?>
      <a href="<?php echo $item['archivo']; ?>"
         class="<?php echo $pagina_actual === $item['archivo'] ? 'activo' : ''; ?>">
        <span class="menu-icono"><?php echo $item['icono']; ?></span><?php echo $item['texto']; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="menu-pie">
    <a href="#"><span class="menu-icono">🚪</span>Cerrar sesión</a>
  </div>
</aside>

<div class="contenido-wrap">

  <!-- ========== Barra superior ========== -->
  <header class="topbar">
    <button class="btn-menu" id="btnMenu" type="button" aria-label="Abrir menú">☰</button>

    <div class="topbar-titulo">
      <h1><?php echo $titulo_pagina; ?></h1>
      <p><?php echo $subtitulo_pagina; ?></p>
    </div>

    <div class="topbar-derecha">
      <button class="btn-icono" type="button" aria-label="Notificaciones">🔔<span class="punto"></span></button>
      <div class="usuario">
        <span class="avatar">TQ</span>
        <span class="usuario-datos">
          <strong>Tester QA</strong>
          <span class="rol-etiqueta">Tester</span>
        </span>
      </div>
    </div>
  </header>

  <!-- Nota importante: El tag <main class="contenido"> se eliminaba aquí provocando conflicto de etiquetas, 
       por lo que tus páginas individuales (como matriz_equivalencia.php) ya controlan su propio contenido limpio -->