<?php
require __DIR__ . '/config.php';
json_response(['ok' => true, 'user' => current_user()]);
