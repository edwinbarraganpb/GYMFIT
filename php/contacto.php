<?php
require __DIR__ . '/config.php';

$in      = json_input();
$nombre  = trim($in['nombre'] ?? '');
$email   = trim($in['email'] ?? '');
$mensaje = trim($in['mensaje'] ?? '');

if ($nombre === '' || $email === '' || $mensaje === '') {
    json_response(['ok'=>false,'error'=>'Todos los campos son obligatorios'], 400);
}

$stmt = db()->prepare(
    "INSERT INTO contactos (nombre, email, mensaje) VALUES (:n,:e,:m)"
);
$stmt->execute([':n'=>$nombre, ':e'=>$email, ':m'=>$mensaje]);

json_response(['ok'=>true]);
