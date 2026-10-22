<?php
/**
 * admin.php
 * Panel de administración: lista las búsquedas (llamadas al bus)
 * registradas en log_busquedas y permite borrarlas.
 *
 * La eliminación normal ocurre por AJAX (ver admin_ajax.php + el
 * JavaScript al final de este archivo). El bloque de abajo (GET
 * ?eliminar=.. / POST borrar_todo) es solo un respaldo por si el
 * usuario tiene JavaScript deshabilitado; en ambos casos se pasa
 * por el bus SOAP, nunca se toca MySQL directo desde aquí.
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol(['admin']);  // solo administradores

require_once __DIR__ . '/config.php';   // define BUS_URL y BUS_URI
$BUS_URL = BUS_URL;
$BUS_URI = BUS_URI;

$mensaje = '';
$tipoMensaje = '';

$cliente = new SoapClient(null, [
    'location'   => $BUS_URL,
    'uri'        => $BUS_URI,
    'exceptions' => true,
]);

try {
    // --- Respaldo sin JavaScript: borrar un registro puntual ---
    if (isset($_GET['eliminar'])) {
        $respuesta = $cliente->eliminarBusqueda((int) $_GET['eliminar']);
        $mensaje = $respuesta;
        $tipoMensaje = (stripos($respuesta, 'error') === 0) ? 'error' : 'exito';
    }

    // --- Respaldo sin JavaScript: borrar todo ---
    if (isset($_POST['borrar_todo'])) {
        $respuesta = $cliente->eliminarTodasBusquedas();
        $mensaje = $respuesta;
        $tipoMensaje = 'exito';
    }

    $busquedas = $cliente->listarBusquedas();
    $errorConsulta = '';
} catch (SoapFault $fault) {
    $busquedas = [];
    $errorConsulta = "No se pudo consultar el bus de web services: " . $fault->getMessage();
}

include 'includes/header.php';
?>

<h1 class="titulo-pagina">Administración de búsquedas</h1>
<p class="subtitulo-pagina">Historial de consultas realizadas al bus de web services de Clínica Salud+.</p>

<div id="admin-toast-area" aria-live="polite"></div>

<?php if ($mensaje): ?>
    <div class="alerta <?php echo $tipoMensaje === 'exito' ? 'alerta-exito' : 'alerta-error'; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
<?php endif; ?>

<?php if ($errorConsulta): ?>
    <div class="alerta alerta-error"><?php echo htmlspecialchars($errorConsulta); ?></div>
<?php endif; ?>

<div class="admin-filtros">
    <div class="campo-busqueda">
        <input type="text" id="filtro-texto" placeholder="Buscar por servicio, método o parámetros..." autocomplete="off">
        <button type="button" id="btn-limpiar-filtro" title="Limpiar búsqueda">✕</button>
    </div>

    <select id="filtro-metodo">
        <option value="">Todos los métodos</option>
    </select>

    <span id="indicador-filtrando" class="indicador-filtrando">Filtrando…</span>
</div>

<div class="admin-toolbar">
    <span class="conteo-registros">
        <span id="conteo-visibles"><?php echo count($busquedas); ?></span> de
        <span id="conteo-total"><?php echo count($busquedas); ?></span> búsqueda(s)
    </span>

    <form method="post" id="form-borrar-todo" onsubmit="return confirm('¿Seguro que quieres borrar TODAS las búsquedas?');">
        <button type="submit" name="borrar_todo" value="1" id="btn-borrar-todo" class="btn btn-peligro">
            🗑️ Borrar todas las búsquedas
        </button>
    </form>
</div>

<div id="sin-datos-inicial" class="sin-datos" <?php echo empty($busquedas) ? '' : 'style="display:none;"'; ?>>
    <p>No hay búsquedas registradas.</p>
</div>

<div id="sin-resultados-filtro" class="sin-datos" style="display:none;">
    <p>Ningún registro coincide con el filtro actual.</p>
</div>

<div class="tabla-wrap" id="tabla-wrap-admin" <?php echo empty($busquedas) ? 'style="display:none;"' : ''; ?>>
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>ID</th>
                <th>Servicio</th>
                <th>Método</th>
                <th>Parámetros</th>
                <th>Fecha y hora</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="tbody-busquedas">
            <?php foreach ($busquedas as $b): ?>
                <tr class="fila-busqueda"
                    data-id="<?php echo (int) $b['id_log']; ?>"
                    data-servicio="<?php echo htmlspecialchars(strtolower($b['servicio'])); ?>"
                    data-metodo="<?php echo htmlspecialchars($b['metodo']); ?>"
                    data-parametros="<?php echo htmlspecialchars(strtolower($b['parametros'])); ?>">
                    <td><?php echo (int) $b['id_log']; ?></td>
                    <td><span class="badge-servicio"><?php echo htmlspecialchars($b['servicio']); ?></span></td>
                    <td><span class="metodo-texto"><?php echo htmlspecialchars($b['metodo']); ?>()</span></td>
                    <td>
                        <span class="parametros-celda" title="<?php echo htmlspecialchars($b['parametros']); ?>">
                            <?php echo htmlspecialchars($b['parametros'] !== '' ? $b['parametros'] : '—'); ?>
                        </span>
                    </td>
                    <td><span class="fecha-celda"><?php echo htmlspecialchars($b['fecha_hora']); ?></span></td>
                    <td>
                        <a href="admin.php?eliminar=<?php echo (int) $b['id_log']; ?>"
                           class="btn-eliminar-fila"
                           title="Eliminar registro"
                           data-id="<?php echo (int) $b['id_log']; ?>"
                           onclick="return confirm('¿Borrar este registro?');">
                            <span class="icono-papelera">🗑️</span>
                            <span class="texto-btn">Eliminar</span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
(function () {
    const tbody = document.getElementById('tbody-busquedas');
    const filtroTexto = document.getElementById('filtro-texto');
    const filtroMetodo = document.getElementById('filtro-metodo');
    const btnLimpiar = document.getElementById('btn-limpiar-filtro');
    const indicadorFiltrando = document.getElementById('indicador-filtrando');
    const conteoVisibles = document.getElementById('conteo-visibles');
    const conteoTotal = document.getElementById('conteo-total');
    const tablaWrap = document.getElementById('tabla-wrap-admin');
    const sinDatosInicial = document.getElementById('sin-datos-inicial');
    const sinResultadosFiltro = document.getElementById('sin-resultados-filtro');
    const toastArea = document.getElementById('admin-toast-area');
    const formBorrarTodo = document.getElementById('form-borrar-todo');
    const btnBorrarTodo = document.getElementById('btn-borrar-todo');

    // --- Toast simple ---
    function mostrarToast(mensaje, tipo) {
        const toast = document.createElement('div');
        toast.className = 'toast-admin toast-' + (tipo === 'error' ? 'error' : 'exito');
        toast.textContent = mensaje;
        toastArea.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('toast-visible'));
        setTimeout(() => {
            toast.classList.remove('toast-visible');
            setTimeout(() => toast.remove(), 250);
        }, 3000);
    }

    // --- Poblar el <select> de métodos dinámicamente desde los datos reales ---
    function poblarFiltroMetodos() {
        const metodos = new Set();
        tbody.querySelectorAll('tr.fila-busqueda').forEach(fila => {
            metodos.add(fila.dataset.metodo);
        });
        metodos.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m + '()';
            filtroMetodo.appendChild(opt);
        });
    }

    // --- Filtro de texto + método, sin recargar la página ---
    function aplicarFiltro() {
        indicadorFiltrando.classList.add('activo');

        const texto = filtroTexto.value.trim().toLowerCase();
        const metodo = filtroMetodo.value;
        let visibles = 0;

        tbody.querySelectorAll('tr.fila-busqueda').forEach(fila => {
            if (fila.classList.contains('fila-saliendo')) return; // en proceso de eliminarse

            const coincideTexto = !texto
                || fila.dataset.servicio.includes(texto)
                || fila.dataset.metodo.toLowerCase().includes(texto)
                || fila.dataset.parametros.includes(texto);

            const coincideMetodo = !metodo || fila.dataset.metodo === metodo;
            const visible = coincideTexto && coincideMetodo;

            if (visible) {
                fila.classList.remove('fila-oculta-filtro');
                visibles++;
            } else {
                fila.classList.add('fila-oculta-filtro');
            }
        });

        conteoVisibles.textContent = visibles;
        sinResultadosFiltro.style.display = (visibles === 0 && tbody.querySelectorAll('tr.fila-busqueda').length > 0) ? '' : 'none';

        setTimeout(() => indicadorFiltrando.classList.remove('activo'), 150);
    }

    filtroTexto.addEventListener('input', aplicarFiltro);
    filtroMetodo.addEventListener('change', aplicarFiltro);
    btnLimpiar.addEventListener('click', () => {
        filtroTexto.value = '';
        filtroMetodo.value = '';
        aplicarFiltro();
        filtroTexto.focus();
    });

    // --- Actualiza el total mostrado tras eliminar ---
    function actualizarTotales() {
        const total = tbody.querySelectorAll('tr.fila-busqueda').length;
        conteoTotal.textContent = total;
        if (total === 0) {
            tablaWrap.style.display = 'none';
            sinDatosInicial.style.display = '';
            sinResultadosFiltro.style.display = 'none';
        }
        aplicarFiltro();
    }

    // --- Eliminar UNA fila por AJAX ---
    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar-fila');
        if (!btn) return;
        e.preventDefault();

        if (!confirm('¿Borrar este registro?')) return;

        const fila = btn.closest('tr');
        const id = btn.dataset.id;

        btn.classList.add('cargando');
        btn.setAttribute('disabled', 'disabled');
        const textoOriginal = btn.querySelector('.texto-btn').textContent;
        btn.querySelector('.texto-btn').textContent = 'Eliminando...';

        const datos = new FormData();
        datos.append('accion', 'eliminar');
        datos.append('id', id);

        fetch('admin_ajax.php', { method: 'POST', body: datos })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    fila.classList.add('fila-saliendo');
                    mostrarToast(data.mensaje, 'exito');
                    setTimeout(() => {
                        fila.remove();
                        actualizarTotales();
                    }, 280);
                } else {
                    throw new Error(data.mensaje || 'No se pudo eliminar el registro.');
                }
            })
            .catch(err => {
                mostrarToast(err.message, 'error');
                btn.classList.remove('cargando');
                btn.removeAttribute('disabled');
                btn.querySelector('.texto-btn').textContent = textoOriginal;
            });
    });

    // --- Borrar TODO por AJAX ---
    formBorrarTodo.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('¿Seguro que quieres borrar TODAS las búsquedas?')) return;

        btnBorrarTodo.classList.add('cargando');
        btnBorrarTodo.setAttribute('disabled', 'disabled');
        const textoOriginal = btnBorrarTodo.innerHTML;
        btnBorrarTodo.innerHTML = 'Borrando...';

        const datos = new FormData();
        datos.append('accion', 'eliminar_todo');

        fetch('admin_ajax.php', { method: 'POST', body: datos })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    mostrarToast(data.mensaje, 'exito');
                    tbody.querySelectorAll('tr.fila-busqueda').forEach(f => f.classList.add('fila-saliendo'));
                    setTimeout(() => {
                        tbody.innerHTML = '';
                        actualizarTotales();
                    }, 280);
                } else {
                    throw new Error(data.mensaje || 'No se pudo borrar el historial.');
                }
            })
            .catch(err => mostrarToast(err.message, 'error'))
            .finally(() => {
                btnBorrarTodo.classList.remove('cargando');
                btnBorrarTodo.removeAttribute('disabled');
                btnBorrarTodo.innerHTML = textoOriginal;
            });
    });

    poblarFiltroMetodos();
})();
</script>

<?php include 'includes/footer.php'; ?>