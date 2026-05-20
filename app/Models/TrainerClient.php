<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class TrainerClient
{
    public static function getClients(int $trainerId): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT u.id, u.nombre, u.email, u.avatar_url, u.edad, u.objetivo, u.nivel,
                    (SELECT MAX(asignada_en) FROM rutinas r WHERE r.cliente_id = u.id) AS ultima_rutina
             FROM entrenador_cliente ec
             JOIN usuarios u ON u.id = ec.cliente_id
             WHERE ec.entrenador_id = :eid
             ORDER BY u.nombre"
        );
        $stmt->execute([':eid' => $trainerId]);
        return $stmt->fetchAll();
    }

    public static function findByEmail(int $trainerId, string $email): ?array
    {
        $stmt = Database::connect()->prepare(
            "SELECT u.id, u.nombre, u.email, u.rol
             FROM usuarios u
             WHERE u.email = :email AND u.rol = 'cliente'"
        );
        $stmt->execute([':email' => $email]);
        $client = $stmt->fetch();

        if (!$client) {
            return null;
        }

        $check = Database::connect()->prepare(
            "SELECT id FROM entrenador_cliente
             WHERE entrenador_id = :eid AND cliente_id = :cid LIMIT 1"
        );
        $check->execute([':eid' => $trainerId, ':cid' => $client['id']]);

        if ($check->fetch()) {
            return null;
        }

        return $client;
    }

    public static function assign(int $trainerId, int $clientId): bool
    {
        $stmt = Database::connect()->prepare(
            "INSERT OR IGNORE INTO entrenador_cliente (entrenador_id, cliente_id)
             VALUES (:eid, :cid)"
        );
        $stmt->execute([':eid' => $trainerId, ':cid' => $clientId]);
        return $stmt->rowCount() > 0;
    }

    public static function countClientsPerTrainer(): array
    {
        $stmt = Database::connect()->query(
            "SELECT u.nombre, COUNT(ec.cliente_id) AS total
             FROM usuarios u
             LEFT JOIN entrenador_cliente ec ON ec.entrenador_id = u.id
             WHERE u.rol = 'entrenador'
             GROUP BY u.id ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }
}
