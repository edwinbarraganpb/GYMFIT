<?php
require __DIR__ . '/config.php';

$in     = json_input();
$nombre = trim($in['nombre'] ?? '');
$email  = trim($in['email'] ?? '');
$pass   = (string)($in['password'] ?? '');
$rol    = $in['rol'] ?? '';

if ($nombre === '' || $email === '' || $pass === '' || !in_array($rol, ['entrenador','cliente'], true)) {
    json_response(['ok' => false, 'error' => 'Datos incompletos o rol inválido'], 400);
}
if (strlen($pass) < 6) {
    json_response(['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'], 400);
}

try {
    $stmt = db()->prepare(
        "INSERT INTO usuarios (nombre, email, password_hash, rol)
         VALUES (:n, :e, crypt(:p, gen_salt('bf')), :r)
         RETURNING id, nombre, email, rol"
    );
    $stmt->execute([':n' => $nombre, ':e' => $email, ':p' => $pass, ':r' => $rol]);
    $user = $stmt->fetch();

    $_SESSION['user'] = $user;
    json_response(['ok' => true, 'user' => $user]);
} catch (PDOException $e) {
    if ($e->getCode() === '23505') {
        json_response(['ok' => false, 'error' => 'Ese email ya está registrado'], 409);
    }
    json_response(['ok' => false, 'error' => 'Error al registrar: ' . $e->getMessage()], 500);
}
