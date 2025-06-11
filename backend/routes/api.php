<?php

// Headers CORS - ABSOLUMENT EN PREMIER !
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");
header('Content-Type: application/json; charset=utf-8');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Chargement des classes nécessaires avec les bons chemins
require_once __DIR__ . '/../config/database.php';  // Version sans namespace

// Créons temporairement la classe Response ici si elle n'existe pas
if (!file_exists(__DIR__ . '/../Core/Response.php')) {
    class Response {
        public static function success($data = null, int $statusCode = 200): void {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'data' => $data, 'timestamp' => date('c')], JSON_PRETTY_PRINT);
        }
        
        public static function error(string $message, int $statusCode = 400, $details = null): void {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => ['message' => $message, 'code' => $statusCode, 'details' => $details], 'timestamp' => date('c')], JSON_PRETTY_PRINT);
        }
    }
} else {
    require_once __DIR__ . '/../Core/Response.php';
}

// Pour l'instant, commentons les autres fichiers jusqu'à ce qu'on les crée
/*
// Modèles
require_once __DIR__ . '/../Models/Jpo.php';
require_once __DIR__ . '/../Models/Campus.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Registration.php';
require_once __DIR__ . '/../Models/Comment.php';
require_once __DIR__ . '/../Models/Role.php';

// Contrôleurs
require_once __DIR__ . '/../Controllers/JpoController.php';
require_once __DIR__ . '/../Controllers/CampusController.php';
require_once __DIR__ . '/../Controllers/UserController.php';
require_once __DIR__ . '/../Controllers/RegistrationController.php';
require_once __DIR__ . '/../Controllers/CommentController.php';
require_once __DIR__ . '/../Controllers/RoleController.php';
*/

/*
use JpoLaplateforme\Backend\Controllers\JpoController;
use JpoLaplateforme\Backend\Controllers\CampusController;
use JpoLaplateforme\Backend\Controllers\UserController;
use JpoLaplateforme\Backend\Controllers\RegistrationController;
use JpoLaplateforme\Backend\Controllers\CommentController;
use JpoLaplateforme\Backend\Controllers\RoleController;
use JpoLaplateforme\Backend\Core\Response;
*/

try {
    // Router simple
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    // Route ping
    if ($path === '/api/ping') {
        Response::success([
            'message' => 'API fonctionne parfaitement !',
            'timestamp' => date('c'),
            'method' => $method,
            'path' => $path,
            'cors_enabled' => true
        ]);
        exit();
    }

    // Route de test temporaire pour voir la structure des fichiers
    if ($path === '/api/debug') {
        Response::success([
            'message' => 'Debug info',
            'file_structure' => [
                'current_dir' => __DIR__,
                'database_found' => file_exists(__DIR__ . '/../app/config/Database.php'),
                'files_in_backend' => is_dir(__DIR__ . '/..') ? scandir(__DIR__ . '/..') : 'not accessible'
            ]
        ]);
        exit();
    }

    // Route de test pour récupérer les JPO depuis la base de données
    if ($path === '/api/jpo' && $method === 'GET') {
        try {
            // Utiliser la classe Database simple (sans namespace)
            $database = new Database();
            $pdo = $database->connect();
            
            // Requête simple pour récupérer les JPO avec les campus
            $sql = "
                SELECT 
                    od.jpo_id as id,
                    od.name as nom,
                    od.date,
                    od.max_capacity,
                    c.name as campus_name,
                    c.city as campus_city,
                    COUNT(r.registration_id) as registered_count
                FROM open_day od
                LEFT JOIN campus c ON od.campus_id = c.campus_id
                LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
                GROUP BY od.jpo_id
                ORDER BY od.date ASC
            ";
            
            $stmt = $pdo->query($sql);
            $jpos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            Response::success([
                'message' => 'Liste des JPO récupérée depuis la base de données',
                'data' => $jpos,
                'count' => count($jpos)
            ]);
            
        } catch (Exception $e) {
            Response::error('Erreur de connexion à la base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Temporarily comment out all other routes until we have the files
    /*
    // Routes JPO
    if (preg_match('/^\/api\/jpo(?:\/(\d+))?(?:\/(\w+))?$/', $path, $matches)) {
        $controller = new JpoController();
        $id = $matches[1] ?? null;
        $action = $matches[2] ?? null;

        if ($method === 'GET') {
            if ($id && $action) {
                // GET /api/jpo/{id}/{action} - Actions spécifiques sur une JPO
                switch ($action) {
                    case 'registrations':
                        $controller->getRegistrations(['id' => $id]);
                        break;
                    case 'comments':
                        $controller->getComments(['id' => $id]);
                        break;
                    default:
                        Response::error('Action non trouvée', 404);
                }
            } elseif ($id) {
                // GET /api/jpo/{id} - JPO spécifique
                $controller->show(['id' => $id]);
            } else {
                // GET /api/jpo - Liste des JPO
                $controller->index();
            }
        } elseif ($method === 'POST' && !$id) {
            // POST /api/jpo - Créer une JPO
            $controller->create();
        } elseif ($method === 'PUT' && $id) {
            // PUT /api/jpo/{id} - Mettre à jour une JPO
            $controller->update(['id' => $id]);
        } elseif ($method === 'DELETE' && $id) {
            // DELETE /api/jpo/{id} - Supprimer une JPO
            $controller->delete(['id' => $id]);
        } else {
            Response::error('Méthode non autorisée', 405);
        }
        exit();
    }

    // Routes Campus
    if (preg_match('/^\/api\/campus(?:\/(\d+))?(?:\/(\w+))?$/', $path, $matches)) {
        $controller = new CampusController();
        $id = $matches[1] ?? null;
        $action = $matches[2] ?? null;

        if ($method === 'GET') {
            if ($id && $action === 'jpo') {
                // GET /api/campus/{id}/jpo - JPO d'un campus
                $controller->getJpos(['id' => $id]);
            } elseif ($id) {
                // GET /api/campus/{id} - Campus spécifique
                $controller->show(['id' => $id]);
            } else {
                // GET /api/campus - Liste des campus
                $controller->index();
            }
        } else {
            Response::error('Méthode non autorisée', 405);
        }
        exit();
    }

    // Routes Users
    if (preg_match('/^\/api\/users(?:\/(\d+))?(?:\/(\w+))?$/', $path, $matches)) {
        $controller = new UserController();
        $id = $matches[1] ?? null;
        $action = $matches[2] ?? null;

        if ($method === 'GET') {
            if ($id && $action) {
                // GET /api/users/{id}/{action} - Actions spécifiques sur un utilisateur
                switch ($action) {
                    case 'registrations':
                        $controller->getRegistrations(['id' => $id]);
                        break;
                    case 'comments':
                        $controller->getComments(['id' => $id]);
                        break;
                    default:
                        Response::error('Action non trouvée', 404);
                }
            } elseif ($id) {
                // GET /api/users/{id} - Utilisateur spécifique
                $controller->show(['id' => $id]);
            } else {
                // GET /api/users - Liste des utilisateurs
                $controller->index();
            }
        } else {
            Response::error('Méthode non autorisée', 405);
        }
        exit();
    }

    // Routes Registrations
    if (preg_match('/^\/api\/registrations(?:\/(\d+))?$/', $path, $matches)) {
        $controller = new RegistrationController();
        $id = $matches[1] ?? null;

        if ($method === 'GET') {
            if ($id) {
                // GET /api/registrations/{id} - Inscription spécifique
                $controller->show(['id' => $id]);
            } else {
                // GET /api/registrations - Liste des inscriptions
                $controller->index();
            }
        } elseif ($method === 'POST' && !$id) {
            // POST /api/registrations - Créer une inscription
            $controller->create();
        } elseif ($method === 'PUT' && $id) {
            // PUT /api/registrations/{id} - Mettre à jour une inscription
            $controller->update(['id' => $id]);
        } elseif ($method === 'DELETE' && $id) {
            // DELETE /api/registrations/{id} - Supprimer une inscription
            $controller->delete(['id' => $id]);
        } else {
            Response::error('Méthode non autorisée', 405);
        }
        exit();
    }

    // Routes Comments
    if (preg_match('/^\/api\/comments(?:\/(\d+))?(?:\/(\w+))?$/', $path, $matches)) {
        $controller = new CommentController();
        $id = $matches[1] ?? null;
        $action = $matches[2] ?? null;

        if ($method === 'GET') {
            if ($id) {
                // GET /api/comments/{id} - Commentaire spécifique
                $controller->show(['id' => $id]);
            } else {
                // GET /api/comments - Liste des commentaires
                $controller->index();
            }
        } elseif ($method === 'POST') {
            if ($id && $action === 'reply') {
                // POST /api/comments/{id}/reply - Ajouter une réponse de modérateur
                $controller->addModeratorReply(['id' => $id]);
            } elseif (!$id) {
                // POST /api/comments - Créer un commentaire
                $controller->create();
            } else {
                Response::error('Action non trouvée', 404);
            }
        } elseif ($method === 'PUT' && $id) {
            // PUT /api/comments/{id} - Mettre à jour un commentaire
            $controller->update(['id' => $id]);
        } elseif ($method === 'DELETE' && $id) {
            // DELETE /api/comments/{id} - Supprimer un commentaire
            $controller->delete(['id' => $id]);
        } else {
            Response::error('Méthode non autorisée', 405);
        }
        exit();
    }

    // Routes Roles
    if (preg_match('/^\/api\/roles(?:\/(\d+))?(?:\/(\w+))?$/', $path, $matches)) {
        $controller = new RoleController();
        $id = $matches[1] ?? null;
        $action = $matches[2] ?? null;

        if ($method === 'GET') {
            if ($id && $action === 'users') {
                // GET /api/roles/{id}/users - Utilisateurs d'un rôle
                $controller->getUsers(['id' => $id]);
            } elseif ($id) {
                // GET /api/roles/{id} - Rôle spécifique
                $controller->show(['id' => $id]);
            } else {
                // GET /api/roles - Liste des rôles
                $controller->index();
            }
        } elseif ($method === 'POST' && !$id) {
            // POST /api/roles - Créer un rôle
            $controller->create();
        } elseif ($method === 'PUT' && $id) {
            // PUT /api/roles/{id} - Mettre à jour un rôle
            $controller->update(['id' => $id]);
        } elseif ($method === 'DELETE' && $id) {
            // DELETE /api/roles/{id} - Supprimer un rôle
            $controller->delete(['id' => $id]);
        } else {
            Response::error('Méthode non autorisée', 405);
        }
        exit();
    }
    */

    // Route racine - Documentation de l'API
    if ($path === '/' || $path === '/api') {
        Response::success([
            'message' => '🚀 API JPO La Plateforme',
            'version' => '1.0.0',
            'endpoints' => [
                'ping' => 'GET /api/ping',
                'jpo' => [
                    'list' => 'GET /api/jpo',
                    'show' => 'GET /api/jpo/{id}',
                    'registrations' => 'GET /api/jpo/{id}/registrations',
                    'comments' => 'GET /api/jpo/{id}/comments',
                    'create' => 'POST /api/jpo',
                    'update' => 'PUT /api/jpo/{id}',
                    'delete' => 'DELETE /api/jpo/{id}'
                ],
                'campus' => [
                    'list' => 'GET /api/campus',
                    'show' => 'GET /api/campus/{id}',
                    'jpo' => 'GET /api/campus/{id}/jpo'
                ],
                'users' => [
                    'list' => 'GET /api/users',
                    'show' => 'GET /api/users/{id}',
                    'registrations' => 'GET /api/users/{id}/registrations',
                    'comments' => 'GET /api/users/{id}/comments'
                ],
                'registrations' => [
                    'list' => 'GET /api/registrations',
                    'show' => 'GET /api/registrations/{id}',
                    'create' => 'POST /api/registrations',
                    'update' => 'PUT /api/registrations/{id}',
                    'delete' => 'DELETE /api/registrations/{id}'
                ],
                'comments' => [
                    'list' => 'GET /api/comments',
                    'show' => 'GET /api/comments/{id}',
                    'create' => 'POST /api/comments',
                    'update' => 'PUT /api/comments/{id}',
                    'delete' => 'DELETE /api/comments/{id}',
                    'reply' => 'POST /api/comments/{id}/reply'
                ],
                'roles' => [
                    'list' => 'GET /api/roles',
                    'show' => 'GET /api/roles/{id}',
                    'users' => 'GET /api/roles/{id}/users',
                    'create' => 'POST /api/roles',
                    'update' => 'PUT /api/roles/{id}',
                    'delete' => 'DELETE /api/roles/{id}'
                ]
            ],
            'timestamp' => date('c')
        ]);
        exit();
    }

    // 404 - Endpoint non trouvé
    Response::error('Endpoint non trouvé', 404, [
        'path' => $path,
        'method' => $method,
        'available_endpoints' => [
            '/api/ping',
            '/api/jpo',
            '/api/campus',
            '/api/users',
            '/api/registrations',
            '/api/comments',
            '/api/roles'
        ]
    ]);

} catch (Exception $e) {
    Response::error(
        'Erreur interne du serveur',
        500,
        $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
    );
}
