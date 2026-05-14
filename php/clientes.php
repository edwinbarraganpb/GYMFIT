<?php
require __DIR__ . '/config.php';
$u = require_auth('entrenador');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = db()->prepare(
        "SELECT u.id, u.nombre, u.email, u.avatar_url,
                (SELECT MAX(asignada_en) FROM rutinas r WHERE r.cliente_id = u.id) AS ultima_rutina
         FROM entrenador_cliente ec
         JOIN usuarios u ON u.id = ec.cliente_id
         WHERE ec.entrenador_id = :eid
         ORDER BY u.nombre"
    );
    $stmt->execute([':eid' => $u['id']]);
    json_response(['ok' => true, 'clientes' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    // Agregar cliente existente (por email) a este entrenador
    $in    = json_input();
    $email = trim($in['email'] ?? '');
    if ($email === '') json_response(['ok'=>false,'error'=>'Email requerido'], 400);

    $c = db()->prepare("SELECT id, rol FROM usuarios WHERE email = :e");
    $c->execute([':e' => $email]);
    $cli = $c->fetch();
    if (!$cli || $cli['rol'] !== 'cliente') {
        json_response(['ok'=>false,'error'=>'No existe un cliente con ese email'], 404);
    }

    $ins = db()->prepare(
        "INSERT INTO entrenador_cliente (entrenador_id, cliente_id)
         VALUES (:e, :c) ON CONFLICT DO NOTHING"
    );
    $ins->execute([':e' => $u['id'], ':c' => $cli['id']]);
    json_response(['ok' => true]);
}

json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
