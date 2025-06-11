<?php
// Fichier: C:\wamp64\www\JPO-Laplateforme_\backend\public\index.php

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

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour charger le .env
function loadEnv() {
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
}

// Charger le fichier .env
loadEnv();

// Définir les variables d'environnement par défaut si pas dans .env
$_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? 'true';
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'jpo-laplateforme_';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'root';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? '';

// Charger l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Charger la classe Database
require_once __DIR__ . '/../Config/database.php';

// Classe Response simple directement ici pour éviter les problèmes
class SimpleResponse {
    public static function success($data = null, int $statusCode = 200): void {
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

    public static function error(string $message, int $statusCode = 400, $details = null): void {
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
}

// Classe Database simple
class SimpleDatabase {
    private $host;
    private $dbname;
    private $username;
    private $password;
    
    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }
    
    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch(PDOException $e) {
            throw new Exception("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
}

try {
    // Router
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Nettoyer le chemin WAMP
    $basePath = '/JPO-Laplateforme_/backend/public';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    if (empty($path) || $path === '/') {
        $path = '/';
    }

    // Route ping
    if ($path === '/api/ping') {
        SimpleResponse::success([
            'message' => 'API JPO La Plateforme fonctionne parfaitement !',
            'version' => '1.0.0',
            'timestamp' => date('c'),
            'method' => $method,
            'path' => $path,
            'cors_enabled' => true
        ]);
        exit();
    }

    // Route test base de données
    if ($path === '/api/test-db') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            SimpleResponse::success([
                'message' => 'Connexion à la base de données réussie !',
                'database' => 'jpo_laplateforme',
                'tables_found' => $tables,
                'table_count' => count($tables)
            ]);
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de connexion à la base de données', 500, [
                'error' => $e->getMessage(),
                'suggestion' => 'Vérifiez que WAMP est démarré et que la base "jpo_laplateforme" existe'
            ]);
        }
        exit();
    }

    // Route Campus
    if ($path === '/api/campus' && $method === 'GET') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $sql = "
                SELECT 
                    c.campus_id as id,
                    c.name,
                    c.city,
                    COUNT(od.jpo_id) as jpo_count
                FROM campus c
                LEFT JOIN open_day od ON c.campus_id = od.campus_id
                GROUP BY c.campus_id
                ORDER BY c.name ASC
            ";
            
            $stmt = $pdo->query($sql);
            $campus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            SimpleResponse::success([
                'message' => 'Liste des campus récupérée avec succès',
                'campus' => $campus,
                'count' => count($campus)
            ]);
            
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de base de données', 500, [
                'error' => $e->getMessage(),
                'query_info' => 'Erreur lors de la récupération des campus'
            ]);
        }
        exit();
    }

    // Route JPO
    if ($path === '/api/jpo' && $method === 'GET') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $sql = "
                SELECT 
                    od.jpo_id as id,
                    od.name,
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
            
            SimpleResponse::success([
                'message' => 'Liste des JPO récupérée avec succès',
                'jpos' => $jpos,
                'count' => count($jpos)
            ]);
            
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de base de données', 500, [
                'error' => $e->getMessage(),
                'query_info' => 'Erreur lors de la récupération des JPO'
            ]);
        }
        exit();
    }

    // Route Users
    if ($path === '/api/users' && $method === 'GET') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $sql = "
                SELECT 
                    u.user_id as id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.user_type,
                    u.created_at,
                    r.role_name,
                    COUNT(reg.registration_id) as registrations_count
                FROM user u
                LEFT JOIN role r ON u.role_id = r.role_id
                LEFT JOIN registration reg ON u.user_id = reg.user_id AND reg.status = 'registered'
                GROUP BY u.user_id
                ORDER BY u.created_at DESC
            ";
            
            $stmt = $pdo->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            SimpleResponse::success([
                'message' => 'Liste des utilisateurs récupérée avec succès',
                'users' => $users,
                'count' => count($users)
            ]);
            
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de base de données', 500, [
                'error' => $e->getMessage(),
                'query_info' => 'Erreur lors de la récupération des utilisateurs'
            ]);
        }
        exit();
    }

    // Route Registrations
    if ($path === '/api/registrations' && $method === 'GET') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $sql = "
                SELECT 
                    r.registration_id as id,
                    r.registration_date,
                    r.status,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.user_type,
                    od.name as jpo_name,
                    od.date as jpo_date,
                    c.name as campus_name
                FROM registration r
                LEFT JOIN user u ON r.user_id = u.user_id
                LEFT JOIN open_day od ON r.jpo_id = od.jpo_id
                LEFT JOIN campus c ON od.campus_id = c.campus_id
                ORDER BY r.registration_date DESC
            ";
            
            $stmt = $pdo->query($sql);
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            SimpleResponse::success([
                'message' => 'Liste des inscriptions récupérée avec succès',
                'registrations' => $registrations,
                'count' => count($registrations)
            ]);
            
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de base de données', 500, [
                'error' => $e->getMessage(),
                'query_info' => 'Erreur lors de la récupération des inscriptions'
            ]);
        }
        exit();
    }

    // Route Comments
    if ($path === '/api/comments' && $method === 'GET') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $sql = "
                SELECT 
                    c.comment_id as id,
                    c.content,
                    c.comment_date,
                    c.moderator_reply,
                    c.reply_date,
                    u.first_name,
                    u.last_name,
                    u.user_type,
                    od.name as jpo_name,
                    od.date as jpo_date
                FROM comment c
                LEFT JOIN user u ON c.user_id = u.user_id
                LEFT JOIN open_day od ON c.jpo_id = od.jpo_id
                ORDER BY c.comment_date DESC
            ";
            
            $stmt = $pdo->query($sql);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            SimpleResponse::success([
                'message' => 'Liste des commentaires récupérée avec succès',
                'comments' => $comments,
                'count' => count($comments)
            ]);
            
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de base de données', 500, [
                'error' => $e->getMessage(),
                'query_info' => 'Erreur lors de la récupération des commentaires'
            ]);
        }
        exit();
    }

    // Route Roles
    if ($path === '/api/roles' && $method === 'GET') {
        try {
            $database = new SimpleDatabase();
            $pdo = $database->connect();
            
            $sql = "
                SELECT 
                    r.role_id as id,
                    r.role_name,
                    COUNT(u.user_id) as users_count
                FROM role r
                LEFT JOIN user u ON r.role_id = u.role_id
                GROUP BY r.role_id
                ORDER BY r.role_name ASC
            ";
            
            $stmt = $pdo->query($sql);
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            SimpleResponse::success([
                'message' => 'Liste des rôles récupérée avec succès',
                'roles' => $roles,
                'count' => count($roles)
            ]);
            
        } catch (Exception $e) {
            SimpleResponse::error('Erreur de base de données', 500, [
                'error' => $e->getMessage(),
                'query_info' => 'Erreur lors de la récupération des rôles'
            ]);
        }
        exit();
    }

    // Route racine - Documentation
    if ($path === '/' || $path === '/api') {
        SimpleResponse::success([
            'message' => '🚀 API JPO La Plateforme',
            'version' => '1.0.0',
            'status' => 'Opérationnel ✅',
            'endpoints' => [
                'ping' => 'GET /api/ping - Test de l\'API',
                'test-db' => 'GET /api/test-db - Test de la base de données', 
                'campus' => 'GET /api/campus - Liste des campus',
                'jpo' => 'GET /api/jpo - Liste des JPO',
                'users' => 'GET /api/users - Liste des utilisateurs',
                'registrations' => 'GET /api/registrations - Liste des inscriptions',
                'comments' => 'GET /api/comments - Liste des commentaires',
                'roles' => 'GET /api/roles - Liste des rôles'
            ],
            'timestamp' => date('c')
        ]);
        exit();
    }

    // 404
    SimpleResponse::error('Endpoint non trouvé', 404, [
        'path_received' => $path,
        'method' => $method,
        'available_endpoints' => [
            '/api/ping',
            '/api/test-db',
            '/api/campus',
            '/api/jpo',
            '/api/users',
            '/api/registrations',
            '/api/comments',
            '/api/roles'
        ]
    ]);

} catch (Exception $e) {
    SimpleResponse::error(
        'Erreur interne du serveur',
        500,
        [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    );
}