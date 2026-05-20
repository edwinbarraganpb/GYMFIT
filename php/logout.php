<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

use App\Controllers\AuthController;

$ctrl = new AuthController();
$ctrl->logout();
