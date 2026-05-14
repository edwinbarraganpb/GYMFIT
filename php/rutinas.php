<?php
require __DIR__ . '/config.php';
$u = require_auth();

$method = $_SERVER['REQUEST_METHOD'];

// GET ?cliente_id=X  -> última rutina del cliente
if ($method === 'GET') {
    $cid = (int)($_GET['cliente_id'] ?? 0);

    // Cliente solo puede ver lo suyo
    if ($u['rol'] === 'cliente') {
        $cid = (int)$u['id'];
    }
    if ($cid <= 0) json_response(['ok'=>false,'error'=>'cliente_id requerido'], 400);

    $stmt = db()->prepare(
        "SELECT r.*, ent.nombre AS entrenador_nombre
         FROM rutinas r
         JOIN usuarios ent ON ent.id = r.entrenador_id
         WHERE r.cliente_id = :c
         ORDER BY r.asignada_en DESC LIMIT 1"
    );
    $stmt->execute([':c' => $cid]);
    json_response(['ok' => true, 'rutina' => $stmt->fetch() ?: null]);
}

// POST -> entrenador asigna/actualiza rutina
if ($method === 'POST') {
    if ($u['rol'] !== 'entrenador') json_response(['ok'=>false,'error'=>'Solo entrenadores'], 403);

    $in            = json_input();
    $cid           = (int)($in['cliente_id'] ?? 0);
    $contenido     = trim($in['contenido'] ?? '');
    $observaciones = trim($in['observaciones'] ?? '');

    if ($cid <= 0 || $contenido === '') {
        json_response(['ok'=>false,'error'=>'Datos incompletos'], 400);
    }

    $stmt = db()->prepare(
        "INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
         VALUES (:c, :e, :ct, :ob)
         RETURNING id"
    );
    $stmt->execute([':c'=>$cid, ':e'=>$u['id'], ':ct'=>$contenido, ':ob'=>$observaciones]);
    json_response(['ok' => true, 'id' => $stmt->fetch()['id']]);
}

json_response(['ok'=>false,'error'=>'Método no permitido'], 405);
