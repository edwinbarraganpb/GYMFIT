<?php
/**
 * GYMFIT - Configuración global
 * Conexión a PostgreSQL (pgAdmin) + helpers de sesión + JSON.
 *
 * Ajusta los valores DB_* según tu instalación local de PostgreSQL.
 */

declare(strict_types=1);

// --------- Configuración de la base de datos ---------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'gymfit');
define('DB_USER', 'postgres');
define('DB_PASS', '1234');


// --------- Sesión ---------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------- Conexión PDO a PostgreSQL ---------
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// --------- Helpers JSON ---------
function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return $_POST;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// --------- Auth helpers ---------
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_auth(?string $rol = null): array {
    $u = current_user();
    if (!$u) {
        json_response(['ok' => false, 'error' => 'No autenticado'], 401);
    }
    if ($rol && $u['rol'] !== $rol) {
        json_response(['ok' => false, 'error' => 'Acceso denegado'], 403);
    }
    return $u;
}
