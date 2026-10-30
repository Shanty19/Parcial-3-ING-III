<?php
require_once __DIR__ . '/includes/auth.php';

$salidaRegistrada = cerrar_sesion();
header('Location: login.php?m=' . ($salidaRegistrada ? 'salio' : 'error_salida'));
exit;
