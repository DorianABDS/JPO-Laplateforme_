<?php

namespace JpoLaplateforme\Backend\Core;

class Response
{
    /**
     * Envoie une réponse de succès
     */
    public static function success($data = null, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'timestamp' => date('c')
        ];

        if ($data !== null) {
            if (is_array($data) && isset($data['message'])) {
                $response['message'] = $data['message'];
                unset($data['message']);
                
                if (!empty($data)) {
                    $response['data'] = $data;
                }
            } else {
                $response['data'] = $data;
            }
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Envoie une réponse d'erreur
     */
    public static function error(string $message, int $statusCode = 400, $details = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $statusCode
            ],
            'timestamp' => date('c')
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Envoie une réponse avec pagination
     */
    public static function paginated(array $data, int $total, int $page = 1, int $perPage = 20): void
    {
        $totalPages = ceil($total / $perPage);
        
        self::success([
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    }

    /**
     * Envoie une réponse de validation d'erreur
     */
    public static function validationError(array $errors): void
    {
        self::error('Données de validation invalides', 422, [
            'validation_errors' => $errors
        ]);
    }

    /**
     * Envoie une réponse 404
     */
    public static function notFound(string $resource = 'Ressource'): void
    {
        self::error("{$resource} non trouvée", 404);
    }

    /**
     * Envoie une réponse 401 (non autorisé)
     */
    public static function unauthorized(string $message = 'Accès non autorisé'): void
    {
        self::error($message, 401);
    }

    /**
     * Envoie une réponse 403 (interdit)
     */
    public static function forbidden(string $message = 'Accès interdit'): void
    {
        self::error($message, 403);
    }

    /**
     * Envoie une réponse 500 (erreur serveur)
     */
    public static function serverError(string $message = 'Erreur interne du serveur', $debug = null): void
    {
        $details = null;
        
        if ($debug !== null && ($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            $details = ['debug' => $debug];
        }
        
        self::error($message, 500, $details);
    }
}