<?php
// Fichier de test simple à placer dans : backend/public/test-ping.php

// Headers CORS - DOIT être en premier !
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

// Répondre aux requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Réponse simple
$response = [
    'success' => true,
    'message' => 'API Test fonctionnel',
    'timestamp' => date('c'),
    'data' => [
        'version' => '1.0.0',
        'environment' => 'development',
        'server' => $_SERVER['SERVER_NAME'] ?? 'localhost'
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>