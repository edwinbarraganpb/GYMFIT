<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connect()->prepare(
            "SELECT id, nombre, email, password_hash, rol, avatar_url, edad, objetivo, nivel, creado_en
             FROM usuarios WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::connect()->prepare(
            "SELECT id, nombre, email, rol, avatar_url, edad, objetivo, nivel, creado_en
             FROM usuarios WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): array
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO usuarios (nombre, email, password_hash, rol)
             VALUES (:nombre, :email, :password_hash, :rol)"
        );
        $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':rol'           => $data['rol'],
        ]);

        return [
            'id'     => (int)$db->lastInsertId(),
            'nombre' => $data['nombre'],
            'email'  => $data['email'],
            'rol'    => $data['rol'],
        ];
    }

    public static function countByRol(string $rol): int
    {
        $stmt = Database::connect()->prepare(
            "SELECT COUNT(*) AS total FROM usuarios WHERE rol = :rol"
        );
        $stmt->execute([':rol' => $rol]);
        return (int)$stmt->fetch()['total'];
    }

    public static function all(): array
    {
        $stmt = Database::connect()->query(
            "SELECT id, nombre, email, rol, avatar_url, edad, objetivo, nivel, creado_en
             FROM usuarios ORDER BY creado_en DESC"
        );
        return $stmt->fetchAll();
    }

    public static function countByMonth(): array
    {
        $stmt = Database::connect()->query(
            "SELECT strftime('%Y-%m', creado_en) AS mes, COUNT(*) AS total
             FROM usuarios GROUP BY mes ORDER BY mes ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countByObjetivo(): array
    {
        $stmt = Database::connect()->query(
            "SELECT COALESCE(objetivo, 'Sin definir') AS objetivo, COUNT(*) AS total
             FROM usuarios WHERE rol = 'cliente'
             GROUP BY objetivo ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }

    public static function countByNivel(): array
    {
        $stmt = Database::connect()->query(
            "SELECT COALESCE(nivel, 'Sin definir') AS nivel, COUNT(*) AS total
             FROM usuarios WHERE rol = 'cliente'
             GROUP BY nivel ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }
}
