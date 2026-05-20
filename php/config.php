<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Helpers\Database;
use App\Helpers\Response;
use App\Helpers\Auth;
use App\Middleware\SecurityHeaders;

SecurityHeaders::apply();

/**
 * Legacy helper: PDO connection.
 */
function db(): \PDO
{
    return Database::connect();
}

/**
 * Legacy helper: JSON response.
 */
function json_response($data, int $status = 200): void
{
    Response::json($data, $status);
}

/**
 * Legacy helper: parse JSON input.
 */
function json_input(): array
{
    return Response::input();
}

/**
 * Legacy helper: current user from session.
 */
function current_user(): ?array
{
    return Auth::user();
}

/**
 * Legacy helper: require authentication.
 */
function require_auth(?string $rol = null): array
{
    return Auth::require($rol);
}
