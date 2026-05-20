<?php

declare(strict_types=1);

namespace App\Helpers;

class Auth
{
    private const SESSION_KEY = 'user';

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION[self::SESSION_KEY] = $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function require(?string $rol = null): array
    {
        $u = self::user();
        if (!$u) {
            Response::error('No autenticado', 401);
        }
        if ($rol && $u['rol'] !== $rol) {
            Response::error('Acceso denegado', 403);
        }
        return $u;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate a CSRF token and store in session.
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /**
     * Validate a CSRF token.
     */
    public static function validateCsrf(string $token): bool
    {
        $stored = $_SESSION['_csrf'] ?? '';
        if ($stored === '' || !hash_equals($stored, $token)) {
            return false;
        }
        return true;
    }
}
