<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;

class ApiController
{
    /**
     * Route de ping pour tester la connexion à l'API
     * Route: GET /api/ping
     */
    public function ping(): void
    {
        Response::success([
            'message' => 'API JPO La Plateforme opérationnelle',
            'version' => '1.0.0',
            'timestamp' => date('c'),
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'endpoints' => [
                'ping' => 'GET /api/ping',
                'jpo_list' => 'GET /api/jpo',
                'jpo_details' => 'GET /api/jpo/{id}'
            ]
        ]);
    }
}