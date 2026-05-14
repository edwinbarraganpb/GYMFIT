<?php
require __DIR__ . '/config.php';

$in    = json_input();
$email = trim($in['email'] ?? '');
$pass  = (string)($in['password'] ?? '');
$rol   = $in['rol'] ?? null; // opcional: 'entrenador' o 'cliente'

if ($email === '' || $pass === '') {
    json_response(['ok' => false, 'error' => 'Email y contraseña son obligatorios'], 400);
}

$stmt = db()->prepare(
    "SELECT id, nombre, email, password_hash, rol, avatar_url
     FROM usuarios WHERE email = :email LIMIT 1"
);
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    json_response(['ok' => false, 'error' => 'Credenciales inválidas'], 401);
}

// Verificación con crypt() de PostgreSQL (bcrypt)
$check = db()->prepare("SELECT (password_hash = crypt(:p, password_hash)) AS ok FROM usuarios WHERE id = :id");
$check->execute([':p' => $pass, ':id' => $user['id']]);
$ok = $check->fetch();

if (!$ok || $ok['ok'] !== true) {
    json_response(['ok' => false, 'error' => 'Credenciales inválidas'], 401);
}

if ($rol && $user['rol'] !== $rol) {
    json_response(['ok' => false, 'error' => 'Este usuario no tiene el rol seleccionado'], 403);
}

unset($user['password_hash']);
$_SESSION['user'] = $user;

json_response(['ok' => true, 'user' => $user]);
