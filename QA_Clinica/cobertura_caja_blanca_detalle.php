<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: cobertura_caja_blanca.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM cobertura_caja_blanca WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: cobertura_caja_blanca.php');
    exit;
}

$codigo = $item['codigo'] ?? '';
$nombreFuncion = $item['nombre_funcion'] ?? '';
$modulo = $item['modulo'] ?? '';
$porcentajeCobertura = $item['porcentaje_cobertura'] ?? 0;
$criterioCobertura = $item['criterio_cobertura'] ?? '';
$complejidadCiclomatica = $item['complejidad_ciclomatica'] ?? 0;
$sentenciasEjecutadas = $item['sentencias_ejecutadas'] ?? 0;
$totalSentencias = $item['total_sentencias'] ?? 0;
$observaciones = $item['observaciones'] ?? '';
$pseudocodigo = $item['pseudocodigo'] ?? '';

$titulo_pagina = 'Detalle de Cobertura - ' . $codigo;
$subtitulo_pagina = 'Análisis de cobertura de caja blanca por función y criterio evaluado.';
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($codigo); ?> - <?php echo htmlspecialchars($nombreFuncion); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($modulo); ?></strong> | Cobertura: <strong><?php echo htmlspecialchars($porcentajeCobertura); ?>%</strong></p>
    </div>
    <div>
        <a href="cobertura_caja_blanca.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Criterio</span>
        <strong style="font-size: 1.05rem; color: #1e293b; display: block;"><?php echo htmlspecialchars($criterioCobertura); ?></strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Cobertura</span>
        <strong style="font-size: 1.2rem; color: #1e293b; display: block;"><?php echo htmlspecialchars($porcentajeCobertura); ?>%</strong>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #8b5cf6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Complejidad</span>
        <strong style="font-size: 1.2rem; color: #1e293b; display: block;">V(G): <?php echo htmlspecialchars($complejidadCiclomatica); ?></strong>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Datos de ejecución</h3>
        <p style="margin: 0 0 12px; color: #334155; line-height: 1.6;"><strong>Sentencias ejecutadas:</strong> <?php echo htmlspecialchars($sentenciasEjecutadas); ?> / <?php echo htmlspecialchars($totalSentencias); ?></p>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><strong>Observaciones:</strong> <?php echo !empty($observaciones) ? nl2br(htmlspecialchars($observaciones)) : '<span style="color: #94a3b8;">Sin observaciones registradas.</span>'; ?></p>
    </div>
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Método y módulo</h3>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><strong>Nombre de la función:</strong> <?php echo htmlspecialchars($nombreFuncion); ?><br>
        <strong>Módulo:</strong> <?php echo htmlspecialchars($modulo); ?></p>
    </div>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Pseudocódigo / rutas probadas</h3>
    <pre style="background: #f8fafc; padding: 16px; border-radius: 6px; border: 1px solid #e2e8f0; font-family: inherit; font-size: 0.9rem; color: #1e293b; white-space: pre-wrap; margin: 0;"><?php echo htmlspecialchars($pseudocodigo); ?></pre>
