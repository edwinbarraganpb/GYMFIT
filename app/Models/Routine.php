<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class Routine
{
    public static function getLatest(int $clientId): ?array
    {
        $stmt = Database::connect()->prepare(
            "SELECT r.*, ent.nombre AS entrenador_nombre
             FROM rutinas r
             JOIN usuarios ent ON ent.id = r.entrenador_id
             WHERE r.cliente_id = :c
             ORDER BY r.asignada_en DESC LIMIT 1"
        );
        $stmt->execute([':c' => $clientId]);
        $routine = $stmt->fetch();
        return $routine ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO rutinas (cliente_id, entrenador_id, contenido, observaciones)
             VALUES (:cliente_id, :entrenador_id, :contenido, :observaciones)"
        );
        $stmt->execute([
            ':cliente_id'    => $data['cliente_id'],
            ':entrenador_id' => $data['entrenador_id'],
            ':contenido'     => $data['contenido'],
            ':observaciones' => $data['observaciones'] ?? '',
        ]);
        return (int)$db->lastInsertId();
    }

    public static function countByMonth(): array
    {
        $stmt = Database::connect()->query(
            "SELECT strftime('%Y-%m', asignada_en) AS mes, COUNT(*) AS total
             FROM rutinas GROUP BY mes ORDER BY mes ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countByClient(): array
    {
        $stmt = Database::connect()->query(
            "SELECT u.nombre, COUNT(r.id) AS total
             FROM usuarios u
             LEFT JOIN rutinas r ON r.cliente_id = u.id
             WHERE u.rol = 'cliente'
             GROUP BY u.id ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }

    public static function total(): int
    {
        $stmt = Database::connect()->query("SELECT COUNT(*) AS total FROM rutinas");
        return (int)$stmt->fetch()['total'];
    }
}
