<?php

/**
 * Productos con minimos y maximos
 */

require_once("access.php");

$access = new Access();
$access->ProductWithMinMax();
echo "Proceso finalizado, Cerrando conexion...\n";
$access->free();
echo "LISTO!!!\n";

