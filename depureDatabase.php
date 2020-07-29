<?php

/**
 * Alta de minimos y maximos via excel
 */

require_once("access.php");

$filename = './depure.xlsx';
$access = new Access();
echo "Leyendo Archivo ($filename) ...\n";
$access->depureDatabase($filename);
echo "Proceso finalizado, Cerrando conexion...\n";
$access->free();
echo "LISTO!!!\n";

