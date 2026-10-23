<?php
/**
 * admin_ajax.php
 * Puente entre el JavaScript del panel de administración y el bus SOAP.
 * No accede a MySQL directamente: solo llama al bus, igual que
 * pacientes.php o agregar.php.
 *
 * Flujo: fetch() del navegador -> este archivo -> SoapClient -> bus.php -> ServicioLog -> MySQL
 */

require_once __DIR__ . '/includes/auth.php';
requerir_rol(['admin'], true);  // solo administradores; responde JSON 401/403

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';   // define BUS_URL y BUS_URI
$BUS_URL = BUS_URL;
$BUS_URI = BUS_URI;

$accion = $_POST['accion'] ?? '';

try {
    ini_set('default_socket_timeout', 5);
    $cliente = new SoapClient(null, [
        'location'   => $BUS_URL,
        'uri'        => $BUS_URI,
        'exceptions' => true,
    ]);

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        $respuesta = $cliente->eliminarBusqueda($id);
        $ok = (stripos($respuesta, 'error') !== 0);

        echo json_encode(['ok' => $ok, 'mensaje' => $respuesta, 'id' => $id]);
    } elseif ($accion === 'eliminar_todo') {
        $respuesta = $cliente->eliminarTodasBusquedas();
        echo json_encode(['ok' => true, 'mensaje' => $respuesta]);
    } else {
        http_response_code(400);
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
    }
} catch (SoapFault $fault) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error al comunicarse con el bus: ' . $fault->getMessage()]);
}