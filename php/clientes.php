<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

use App\Controllers\ClientController;

$ctrl = new ClientController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ctrl->list();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->assign();
}

json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
