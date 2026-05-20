<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class Contact
{
    public static function create(array $data): int
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO contactos (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)"
        );
        $stmt->execute([
            ':nombre'  => $data['nombre'],
            ':email'   => $data['email'],
            ':mensaje' => $data['mensaje'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function all(): array
    {
        $stmt = Database::connect()->query(
            "SELECT * FROM contactos ORDER BY enviado_en DESC"
        );
        return $stmt->fetchAll();
    }

    public static function countByMonth(): array
    {
        $stmt = Database::connect()->query(
            "SELECT strftime('%Y-%m', enviado_en) AS mes, COUNT(*) AS total
             FROM contactos GROUP BY mes ORDER BY mes ASC"
        );
        return $stmt->fetchAll();
    }
}
