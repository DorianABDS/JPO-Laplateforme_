<?php
// Configuration d'erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// ✅ Chemin corrigé vers database.php
require_once __DIR__ . '/../app/Config/database.php';

// Chargement des variables d'environnement
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

date_default_timezone_set('Europe/Paris');

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && $error['type'] === E_ERROR) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => 'Erreur interne du serveur',
                'code' => 500
            ],
            'timestamp' => date('c')
        ]);
    }
});

set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'error' => [
            'message' => 'Erreur interne du serveur',
            'code' => 500
        ],
        'timestamp' => date('c')
    ];
    
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        $response['error']['debug'] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
});

try {
    require_once __DIR__ . '/../routes/api.php';
} catch (Exception $e) {
    throw $e;
}