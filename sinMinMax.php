<?php

/**
 * Alta de minimos y maximos via excel
 */

require_once("access.php");

$access = new Access();
$access->ProductWithStockWithMinMax();
echo "Proceso finalizado, Cerrando conexion...\n";
$access->free();
echo "LISTO!!!\n";

