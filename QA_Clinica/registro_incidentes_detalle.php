<?php
session_start();
require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: registro_incidentes.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM registro_incidentes WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: registro_incidentes.php');
    exit;
}

$codigo = $item['codigo'] ?? '';
$tituloIncidente = $item['titulo_incidente'] ?? '';
$modulo = $item['modulo'] ?? '';
$severidad = $item['severidad'] ?? '';
$estado = $item['estado'] ?? '';
$pasosReproducir = $item['pasos_reproducir'] ?? '';
$resultadoEsperado = $item['resultado_esperado'] ?? '';
$resultadoObtenido = $item['resultado_obtenido'] ?? '';
$observaciones = $item['observaciones'] ?? '';

$titulo_pagina = 'Detalle de Incidente - ' . $codigo;
$subtitulo_pagina = 'Seguimiento del incidente y resultados asociados a la corrección.';
$pdf_export_data = [
    'title' => 'Detalle de incidente: ' . $codigo . ' - ' . $tituloIncidente,
    'subtitle' => 'Módulo: ' . $modulo . ' | Severidad: ' . $severidad,
    'fileName' => 'reporte_incidente_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $codigo) . '.pdf',
    'sections' => [
        [
            'title' => 'Información del incidente',
            'items' => [
                ['label' => 'Código', 'value' => $codigo],
                ['label' => 'Título', 'value' => $tituloIncidente],
                ['label' => 'Módulo', 'value' => $modulo],
                ['label' => 'Severidad', 'value' => $severidad],
                ['label' => 'Estado', 'value' => $estado],
                ['label' => 'Pasos para reproducir', 'value' => $pasosReproducir ?: 'No se registraron pasos'],
                ['label' => 'Resultado esperado', 'value' => $resultadoEsperado ?: 'No definido'],
                ['label' => 'Resultado obtenido', 'value' => $resultadoObtenido ?: 'No registrado'],
                ['label' => 'Observaciones', 'value' => $observaciones ?: 'Sin observaciones']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($codigo); ?> - <?php echo htmlspecialchars($tituloIncidente); ?></h2>
        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Módulo: <strong><?php echo htmlspecialchars($modulo); ?></strong> | Severidad: <strong><?php echo htmlspecialchars($severidad); ?></strong></p>
    </div>
    <div>
        <button type="button" data-export-pdf="true" style="background: #16a34a; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-right: 8px;">⬇️ Descargar PDF</button>
        <a href="registro_incidentes.php" style="background: #64748b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem;">← Volver</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #3b82f6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Severidad</span>
        <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 0.8rem; display: inline-block;"><?php echo htmlspecialchars($severidad); ?></span>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #10b981; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <span style="color: #64748b; font-size: 0.85rem; display: block; margin-bottom: 6px;">Estado</span>
        <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 0.8rem; display: inline-block;"><?php echo htmlspecialchars($estado); ?></span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Pasos para reproducir</h3>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><?php echo !empty($pasosReproducir) ? nl2br(htmlspecialchars($pasosReproducir)) : '<span style="color: #94a3b8;">No se registraron pasos.</span>'; ?></p>
    </div>
    <div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Resultado esperado y obtenido</h3>
        <p style="margin: 0 0 12px; color: #334155; line-height: 1.6;"><strong>Esperado:</strong> <?php echo !empty($resultadoEsperado) ? nl2br(htmlspecialchars($resultadoEsperado)) : '<span style="color: #94a3b8;">No definido.</span>'; ?></p>
        <p style="margin: 0; color: #334155; line-height: 1.6;"><strong>Obtenido:</strong> <?php echo !empty($resultadoObtenido) ? nl2br(htmlspecialchars($resultadoObtenido)) : '<span style="color: #94a3b8;">No registrado.</span>'; ?></p>
    </div>
</div>

<div style="background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <h3 style="margin-top: 0; font-size: 1.1rem; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 16px;">Observaciones</h3>
    <p style="margin: 0; color: #334155; line-height: 1.6;"><?php echo !empty($observaciones) ? nl2br(htmlspecialchars($observaciones)) : '<span style="color: #94a3b8;">Sin observaciones.</span>'; ?></p>
