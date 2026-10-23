<?php
session_start();
require_once(__DIR__ . '/../config.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: analisis_valor_limite.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM qa_analisis_valor_limite WHERE id = ?");
$stmt->execute([$id]);
$analisis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$analisis) {
    header('Location: analisis_valor_limite.php');
    exit;
}

// Generar o consultar valores de prueba límite automáticos de caja negra
$min = $analisis['valor_minimo'];
$max = $analisis['valor_maximo'];

$evidencias = [];
if (!empty($analisis['evidencia'])) {
    $decoded = json_decode($analisis['evidencia'], true);
    if (is_array($decoded)) {
        $evidencias = array_values(array_filter(array_map('strval', $decoded), function ($valor) {
            return trim($valor) !== '';
        }));
    } elseif (is_string($analisis['evidencia']) && trim($analisis['evidencia']) !== '') {
        $evidencias = [trim($analisis['evidencia'])];
    }
}

$titulo_pagina = 'Detalle de Análisis - ' . $analisis['codigo_analisis'];
$subtitulo_pagina = 'Desglose de valores límite y casos de prueba evaluados.';
$pdf_export_data = [
    'title' => 'Detalle de análisis: ' . $analisis['codigo_analisis'],
    'subtitle' => 'Módulo: ' . ($analisis['modulo'] ?? '') . ' | Rango: ' . ($min !== '' ? $min : 'No aplica') . ' a ' . ($max !== '' ? $max : 'No aplica'),
    'fileName' => 'reporte_analisis_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $analisis['codigo_analisis']) . '.pdf',
    'sections' => [
        [
            'title' => 'Información general',
            'items' => [
                ['label' => 'Código', 'value' => $analisis['codigo_analisis'] ?? ''],
                ['label' => 'Módulo', 'value' => $analisis['modulo'] ?? ''],
                ['label' => 'Campo evaluado', 'value' => $analisis['campo_evaluado'] ?? ''],
                ['label' => 'Tipo de dato', 'value' => $analisis['tipo_dato'] ?? ''],
                ['label' => 'Valor mínimo', 'value' => $min !== '' ? $min : 'No aplica'],
                ['label' => 'Valor máximo', 'value' => $max !== '' ? $max : 'No aplica'],
                ['label' => 'Estado', 'value' => $analisis['estado'] ?? ''],
                ['label' => 'Objetivo', 'value' => $analisis['objetivo'] ?? ''],
                ['label' => 'Resultado esperado', 'value' => $analisis['resultado_esperado'] ?? ''],
                ['label' => 'Resultado obtenido', 'value' => $analisis['resultado_obtenido'] ?? 'Sin registrar']
            ]
        ]
    ]
];
include 'includes/header.php';
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detalle del Análisis: <?php echo htmlspecialchars($analisis['codigo_analisis']); ?></h2>
        <div>
            <button type="button" data-export-pdf="true" class="btn btn-success text-white me-2">⬇️ Descargar PDF</button>
            <a href="analisis_valor_limite_form.php?id=<?php echo $analisis['id']; ?>" class="btn btn-warning text-white me-2"><i class="fas fa-edit"></i> Editar</a>
            <a href="analisis_valor_limite.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    <!-- Tarjeta de Información General -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Información General</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Módulo:</strong> <?php echo htmlspecialchars($analisis['modulo']); ?></div>
                <div class="col-md-4"><strong>Campo Evaluado:</strong> <code><?php echo htmlspecialchars($analisis['campo_evaluado']); ?></code></div>
                <div class="col-md-4"><strong>Tipo de Dato:</strong> <?php echo htmlspecialchars($analisis['tipo_dato']); ?></div>
                <div class="col-md-4"><strong>Valor Mínimo:</strong> <?php echo htmlspecialchars($min !== '' ? $min : 'No aplica'); ?></div>
                <div class="col-md-4"><strong>Valor Máximo:</strong> <?php echo htmlspecialchars($max !== '' ? $max : 'No aplica'); ?></div>
                <div class="col-md-4"><strong>Estado General:</strong> 
                    <span class="badge bg-<?php echo ($analisis['estado']=='Aprobado')?'success':(($analisis['estado']=='Fallido')?'danger':'warning text-dark'); ?>">
                        <?php echo htmlspecialchars($analisis['estado']); ?>
                    </span>
                </div>
                <div class="col-md-12"><strong>Objetivo:</strong> <?php echo nl2br(htmlspecialchars($analisis['objetivo'])); ?></div>
                <div class="col-md-6"><strong>Resultado Esperado:</strong> <p class="mb-0"><?php echo nl2br(htmlspecialchars($analisis['resultado_esperado'])); ?></p></div>
                <div class="col-md-6"><strong>Resultado Obtenido:</strong> <p class="mb-0"><?php echo nl2br(htmlspecialchars($analisis['resultado_obtenido'] ?? 'Sin registrar')); ?></p></div>
            </div>
        </div>
    </div>

    <?php if (!empty($evidencias)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Evidencias</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($evidencias as $evidencia): ?>
                    <?php $src = preg_match('/^https?:\/\//i', $evidencia) ? $evidencia : '../' . ltrim($evidencia, '/'); ?>
                    <div class="col-md-3">
                        <div class="border rounded p-2 bg-light">
                            <img src="<?php echo htmlspecialchars($src); ?>" alt="Evidencia" style="width: 100%; height: 180px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <div class="small mt-2 text-break"><?php echo htmlspecialchars(basename($evidencia)); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Aplicación de la Técnica de Valor Límite -->
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Casos de Prueba de Caja Negra (Valores Límite)</h5>
            <span class="text-muted small">Cálculo basado en rangos mínimo y máximo permitidos</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Caso #</th>
                            <th>Valor Probado</th>
                            <th>Clasificación (Caja Negra)</th>
                            <th>Resultado Esperado</th>
                            <th>Resultado Obtenido</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Si es numérico con límites válidos, calculamos los 7 puntos clásicos de valor límite
                        $is_numeric_range = is_numeric($min) && is_numeric($max);
                        if ($is_numeric_range) {
                            $min_val = floatval($min);
                            $max_val = floatval($max);
                            
                            $casos_prueba = [
                                ['num' => 1, 'val' => $min_val - 1, 'class' => 'Inmediatamente inferior al mínimo (Inválido)', 'exp' => 'Error de validación: menor al mínimo'],
                                ['num' => 2, 'val' => $min_val, 'class' => 'Valor mínimo permitido (Válido)', 'exp' => 'Procesamiento exitoso en el límite inferior'],
                                ['num' => 3, 'val' => $min_val + 1, 'class' => 'Inmediatamente superior al mínimo (Válido)', 'exp' => 'Procesamiento exitoso'],
                                ['num' => 4, 'val' => ($min_val + $max_val) / 2, 'class' => 'Valor nominal / intermedio (Válido)', 'exp' => 'Procesamiento exitoso estándar'],
                                ['num' => 5, 'val' => $max_val - 1, 'class' => 'Inmediatamente inferior al máximo (Válido)', 'exp' => 'Procesamiento exitoso'],
                                ['num' => 6, 'val' => $max_val, 'class' => 'Valor máximo permitido (Válido)', 'exp' => 'Procesamiento exitoso en el límite superior'],
                                ['num' => 7, 'val' => $max_val + 1, 'class' => 'Inmediatamente superior al máximo (Inválido)', 'exp' => 'Error de validación: supera el máximo']
                            ];

                            foreach ($casos_prueba as $cp) {
                                echo "<tr>";
                                echo "<td>CP-0" . $cp['num'] . "</td>";
                                echo "<td><code>" . $cp['val'] . "</code></td>";
                                echo "<td>" . $cp['class'] . "</td>";
                                echo "<td>" . $cp['exp'] . "</td>";
                                echo "<td>" . htmlspecialchars($analisis['resultado_obtenido'] ?: 'Pendiente de ejecución') . "</td>";
                                echo "<td><span class='badge bg-warning text-dark'>" . htmlspecialchars($analisis['estado']) . "</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted'>Los límites numéricos no están completamente definidos o el campo requiere evaluación de longitud de texto. Revise la definición del campo.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
window.pdfData = <?php echo json_encode($pdf_export_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<?php include 'includes/footer.php'; ?>