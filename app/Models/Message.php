<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class Message
{
    public static function create(array $data): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO mensajes (de_usuario_id, para_usuario_id, contenido)
             VALUES (:de, :para, :contenido)"
        );
        $stmt->execute([
            ':de'        => $data['de_usuario_id'],
            ':para'      => $data['para_usuario_id'],
            ':contenido' => $data['contenido'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function getConversation(int $userId, int $otherId): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT * FROM mensajes
             WHERE (de_usuario_id = :uid AND para_usuario_id = :oid)
                OR (de_usuario_id = :oid2 AND para_usuario_id = :uid2)
             ORDER BY enviado_en ASC"
        );
        $stmt->execute([
            ':uid' => $userId, ':oid' => $otherId,
            ':uid2' => $userId, ':oid2' => $otherId,
        ]);
        return $stmt->fetchAll();
    }
}
