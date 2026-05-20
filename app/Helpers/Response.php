<?php

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(mixed $data = null): void
    {
        $response = ['ok' => true];
        if (is_array($data)) {
            $response = array_merge($response, $data);
        } elseif ($data !== null) {
            $response['data'] = $data;
        }
        self::json($response);
    }

    public static function error(string $message, int $status = 400): void
    {
        self::json(['ok' => false, 'error' => $message], $status);
    }

    public static function input(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return $_POST;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
