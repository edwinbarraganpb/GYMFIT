<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

use App\Controllers\RoutineController;

$ctrl = new RoutineController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ctrl->get();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->save();
}

json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
