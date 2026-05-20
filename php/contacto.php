<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

use App\Controllers\ContactController;

$ctrl = new ContactController();
$ctrl->submit();
