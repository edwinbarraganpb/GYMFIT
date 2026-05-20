<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

use App\Controllers\ReportController;

$ctrl = new ReportController();
$tipo = $_GET['tipo'] ?? '';

if ($tipo === 'clientes') {
    $ctrl->clientProgress();
} elseif ($tipo === 'gimnasio') {
    $ctrl->gymStatistics();
}

json_response(['ok' => false, 'error' => 'Tipo de reporte no válido'], 400);
