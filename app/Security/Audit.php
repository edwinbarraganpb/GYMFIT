<?php

declare(strict_types=1);

namespace App\Security;

use App\Helpers\Database;

class Audit
{
    public static function log(string $accion, ?array $detalle = null, ?int $usuarioId = null): void
    {
        $db = Database::connect();
        $stmt = $db->prepare(
            "INSERT INTO auditoria (usuario_id, accion, detalle, ip, user_agent)
             VALUES (:uid, :accion, :detalle, :ip, :ua)"
        );
        $stmt->execute([
            ':uid'     => $usuarioId,
            ':accion'  => $accion,
            ':detalle' => $detalle ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : null,
            ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    /**
     * Escape output for safe HTML display (XSS prevention).
     */
    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize input: strip tags, trim whitespace.
     */
    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }

    public static function recentLogs(int $limit = 50): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT a.*, u.nombre AS usuario_nombre
             FROM auditoria a
             LEFT JOIN usuarios u ON u.id = a.usuario_id
             ORDER BY a.creado_en DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
