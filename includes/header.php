<?php
require_once __DIR__ . '/auth.php';

// Detecta la página actual para resaltarla en el menú
$pagina_actual = basename($_SERVER['PHP_SELF']);

// Usuario con sesión activa (o null) para armar el menú
$usuario_menu = usuario_actual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($titulo_pestana) ? $titulo_pestana . ' | Clínica Salud+' : 'Clínica Salud+'; ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
  <div class="header-inner">
    <a href="index.php" class="logo">
      <!-- Logo: estetoscopio en SVG -->
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
        <span class="nombre">Clínica Salud+</span>
        <span class="slogan">Cuidamos de ti</span>
      </span>
    </a>

    <nav class="menu">
      <a href="index.php" class="<?php echo $pagina_actual === 'index.php' ? 'activo' : ''; ?>">Acerca de</a>
      <?php if ($usuario_menu): ?>
        <a href="pacientes.php" class="<?php echo $pagina_actual === 'pacientes.php' ? 'activo' : ''; ?>">Pacientes</a>
        <a href="buscar.php" class="<?php echo $pagina_actual === 'buscar.php' ? 'activo' : ''; ?>">Buscar</a>
        <a href="agregar.php" class="<?php echo $pagina_actual === 'agregar.php' ? 'activo' : ''; ?>">Agregar</a>
        <?php if ($usuario_menu['rol'] === 'admin'): ?>
          <a href="admin.php" class="<?php echo $pagina_actual === 'admin.php' ? 'activo' : ''; ?>">Administración</a>
        <?php endif; ?>
        <span class="menu-usuario" title="Rol: <?php echo htmlspecialchars($usuario_menu['rol']); ?>">
          👤 <?php echo htmlspecialchars($usuario_menu['username']); ?>
        </span>
        <a href="logout.php" class="menu-salir">Cerrar sesión</a>
      <?php else: ?>
        <a href="login.php" class="<?php echo $pagina_actual === 'login.php' ? 'activo' : ''; ?>">Iniciar sesión</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main></main>