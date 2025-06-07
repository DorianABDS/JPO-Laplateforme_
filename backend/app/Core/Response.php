<?php

namespace JpoLaplateforme\Backend\Core;

class Response
{
    public static function success($data = null, int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ], $statusCode);
    }

    public static function error(string $message, int $statusCode = 400, ?array $errors = null): void
    {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $statusCode
            ],
            'timestamp' => date('c')
        ];

        if ($errors !== null) {
            $response['error']['details'] = $errors;
        }

        self::json($response, $statusCode);
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        self::setCorsHeaders();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private static function setCorsHeaders(): void
    {
        $allowedOrigins = explode(',', $_ENV['CORS_ORIGINS'] ?? 'http://localhost:5173');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
    }

    public static function handlePreflight(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::setCorsHeaders();
            http_response_code(200);
            exit;
        }
    }
}