<?php
// Affichage des erreurs (en développement uniquement)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chargement de l'autoloader de Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Connexion à la base de données
require_once __DIR__ . '/../app/Config/database.php';

// Chargement des variables d'environnement depuis le fichier .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Fuseau horaire par défaut
date_default_timezone_set('Europe/Paris');

// Gestion des erreurs fatales
register_shutdown_function(function () {
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

// Gestion des exceptions non capturées
set_exception_handler(function ($exception) {
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

// Chargement des routes
try {
    require_once __DIR__ . '/../routes/api.php';
} catch (Exception $e) {
    throw $e;
}
