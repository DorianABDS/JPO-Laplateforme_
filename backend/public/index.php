<?php

// Headers CORS
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

// Chargement de la classe Database
require_once __DIR__ . '/../app/config/database.php';

// Classe Response
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

try {
    // Router
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

    // Routes JPO - CRUD complet
    if (preg_match('/^\/api\/jpo(?:\/(\d+))?$/', $path, $matches)) {
        $id = $matches[1] ?? null;
        
        try {
            $database = new Database();
            $pdo = $database->connect();
            
            if ($method === 'GET') {
                if ($id) {
                    // GET /api/jpo/{id} - JPO spécifique
                    $sql = "
                        SELECT 
                            od.jpo_id as id,
                            od.name,
                            od.date,
                            od.max_capacity,
                            od.campus_id,
                            c.name as campus_name,
                            c.city as campus_city,
                            COUNT(r.registration_id) as registered_count
                        FROM open_day od
                        LEFT JOIN campus c ON od.campus_id = c.campus_id
                        LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
                        WHERE od.jpo_id = :id
                        GROUP BY od.jpo_id
                    ";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id' => $id]);
                    $jpo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$jpo) {
                        Response::error('JPO non trouvée', 404);
                        exit();
                    }
                    
                    Response::success($jpo);
                } else {
                    // GET /api/jpo - Liste des JPO
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
                    
                    Response::success([
                        'message' => 'Liste des JPO récupérée depuis la base de données',
                        'jpos' => $jpos,
                        'count' => count($jpos)
                    ]);
                }
            } 
            elseif ($method === 'POST' && !$id) {
                // POST /api/jpo - Créer une JPO
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    Response::error('Données JSON invalides', 400);
                    exit();
                }
                
                // Validation
                $errors = [];
                if (empty($input['name'])) $errors['name'] = 'Le nom est requis';
                if (empty($input['date'])) $errors['date'] = 'La date est requise';
                if (empty($input['max_capacity']) || !is_numeric($input['max_capacity'])) 
                    $errors['max_capacity'] = 'La capacité maximale est requise et doit être un nombre';
                if (empty($input['campus_id']) || !is_numeric($input['campus_id'])) 
                    $errors['campus_id'] = 'L\'ID du campus est requis et doit être un nombre';
                
                if (!empty($errors)) {
                    Response::error('Données invalides', 400, $errors);
                    exit();
                }
                
                $sql = "INSERT INTO open_day (name, date, max_capacity, campus_id) VALUES (:name, :date, :max_capacity, :campus_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'name' => $input['name'],
                    'date' => $input['date'],
                    'max_capacity' => $input['max_capacity'],
                    'campus_id' => $input['campus_id']
                ]);
                
                $newId = $pdo->lastInsertId();
                Response::success(['message' => 'JPO créée avec succès', 'id' => $newId], 201);
            }
            elseif ($method === 'PUT' && $id) {
                // PUT /api/jpo/{id} - Modifier une JPO
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    Response::error('Données JSON invalides', 400);
                    exit();
                }
                
                // Vérifier que la JPO existe
                $checkSql = "SELECT COUNT(*) FROM open_day WHERE jpo_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $id]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    Response::error('JPO non trouvée', 404);
                    exit();
                }
                
                $fields = [];
                $params = ['id' => $id];
                
                if (isset($input['name'])) {
                    $fields[] = "name = :name";
                    $params['name'] = $input['name'];
                }
                if (isset($input['date'])) {
                    $fields[] = "date = :date";
                    $params['date'] = $input['date'];
                }
                if (isset($input['max_capacity'])) {
                    $fields[] = "max_capacity = :max_capacity";
                    $params['max_capacity'] = $input['max_capacity'];
                }
                if (isset($input['campus_id'])) {
                    $fields[] = "campus_id = :campus_id";
                    $params['campus_id'] = $input['campus_id'];
                }
                
                if (empty($fields)) {
                    Response::error('Aucune donnée à mettre à jour', 400);
                    exit();
                }
                
                $sql = "UPDATE open_day SET " . implode(', ', $fields) . " WHERE jpo_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                Response::success(['message' => 'JPO mise à jour avec succès']);
            }
            elseif ($method === 'DELETE' && $id) {
                // DELETE /api/jpo/{id} - Supprimer une JPO
                
                // Vérifier que la JPO existe
                $checkSql = "SELECT COUNT(*) FROM open_day WHERE jpo_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $id]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    Response::error('JPO non trouvée', 404);
                    exit();
                }
                
                $sql = "DELETE FROM open_day WHERE jpo_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                
                Response::success(['message' => 'JPO supprimée avec succès']);
            }
            else {
                Response::error('Méthode non autorisée', 405);
            }
        } catch (Exception $e) {
            Response::error('Erreur de base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Route Campus - Liste tous les campus
    if ($path === '/api/campus' && $method === 'GET') {
        try {
            $database = new Database();
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
            
            Response::success([
                'message' => 'Liste des campus récupérée depuis la base de données',
                'campus' => $campus,
                'count' => count($campus)
            ]);
            
        } catch (Exception $e) {
            Response::error('Erreur de connexion à la base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Route Users - Liste tous les utilisateurs
    if ($path === '/api/users' && $method === 'GET') {
        try {
            $database = new Database();
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
            
            Response::success([
                'message' => 'Liste des utilisateurs récupérée depuis la base de données',
                'users' => $users,
                'count' => count($users)
            ]);
            
        } catch (Exception $e) {
            Response::error('Erreur de connexion à la base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Routes Registrations - CRUD complet
    if (preg_match('/^\/api\/registrations(?:\/(\d+))?$/', $path, $matches)) {
        $id = $matches[1] ?? null;
        
        try {
            $database = new Database();
            $pdo = $database->connect();
            
            if ($method === 'GET') {
                if ($id) {
                    // GET /api/registrations/{id} - Inscription spécifique
                    $sql = "
                        SELECT 
                            r.registration_id as id,
                            r.registration_date,
                            r.status,
                            r.user_id,
                            r.jpo_id,
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
                        WHERE r.registration_id = :id
                    ";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id' => $id]);
                    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$registration) {
                        Response::error('Inscription non trouvée', 404);
                        exit();
                    }
                    
                    Response::success($registration);
                } else {
                    // GET /api/registrations - Liste des inscriptions
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
                    
                    Response::success([
                        'message' => 'Liste des inscriptions récupérée depuis la base de données',
                        'registrations' => $registrations,
                        'count' => count($registrations)
                    ]);
                }
            }
            elseif ($method === 'POST' && !$id) {
                // POST /api/registrations - Créer une inscription
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    Response::error('Données JSON invalides', 400);
                    exit();
                }
                
                // Validation
                $errors = [];
                if (empty($input['user_id']) || !is_numeric($input['user_id'])) 
                    $errors['user_id'] = 'L\'ID utilisateur est requis et doit être un nombre';
                if (empty($input['jpo_id']) || !is_numeric($input['jpo_id'])) 
                    $errors['jpo_id'] = 'L\'ID JPO est requis et doit être un nombre';
                
                if (!empty($errors)) {
                    Response::error('Données invalides', 400, $errors);
                    exit();
                }
                
                $status = $input['status'] ?? 'registered';
                $registration_date = date('Y-m-d H:i:s');
                
                $sql = "INSERT INTO registration (user_id, jpo_id, registration_date, status) VALUES (:user_id, :jpo_id, :registration_date, :status)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'user_id' => $input['user_id'],
                    'jpo_id' => $input['jpo_id'],
                    'registration_date' => $registration_date,
                    'status' => $status
                ]);
                
                $newId = $pdo->lastInsertId();
                Response::success(['message' => 'Inscription créée avec succès', 'id' => $newId], 201);
            }
            elseif ($method === 'PUT' && $id) {
                // PUT /api/registrations/{id} - Modifier une inscription
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    Response::error('Données JSON invalides', 400);
                    exit();
                }
                
                // Vérifier que l'inscription existe
                $checkSql = "SELECT COUNT(*) FROM registration WHERE registration_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $id]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    Response::error('Inscription non trouvée', 404);
                    exit();
                }
                
                if (isset($input['status']) && in_array($input['status'], ['registered', 'unregistered'])) {
                    $sql = "UPDATE registration SET status = :status WHERE registration_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['status' => $input['status'], 'id' => $id]);
                    
                    Response::success(['message' => 'Inscription mise à jour avec succès']);
                } else {
                    Response::error('Statut invalide. Utilisez "registered" ou "unregistered"', 400);
                }
            }
            elseif ($method === 'DELETE' && $id) {
                // DELETE /api/registrations/{id} - Supprimer une inscription
                
                // Vérifier que l'inscription existe
                $checkSql = "SELECT COUNT(*) FROM registration WHERE registration_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $id]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    Response::error('Inscription non trouvée', 404);
                    exit();
                }
                
                $sql = "DELETE FROM registration WHERE registration_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                
                Response::success(['message' => 'Inscription supprimée avec succès']);
            }
            else {
                Response::error('Méthode non autorisée', 405);
            }
        } catch (Exception $e) {
            Response::error('Erreur de base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Routes Comments - CRUD complet
    if (preg_match('/^\/api\/comments(?:\/(\d+))?(?:\/(\w+))?$/', $path, $matches)) {
        $id = $matches[1] ?? null;
        $action = $matches[2] ?? null;
        
        try {
            $database = new Database();
            $pdo = $database->connect();
            
            if ($method === 'GET') {
                if ($id) {
                    // GET /api/comments/{id} - Commentaire spécifique
                    $sql = "
                        SELECT 
                            c.comment_id as id,
                            c.content,
                            c.comment_date,
                            c.moderator_reply,
                            c.reply_date,
                            c.user_id,
                            c.jpo_id,
                            u.first_name,
                            u.last_name,
                            u.user_type,
                            od.name as jpo_name,
                            od.date as jpo_date
                        FROM comment c
                        LEFT JOIN user u ON c.user_id = u.user_id
                        LEFT JOIN open_day od ON c.jpo_id = od.jpo_id
                        WHERE c.comment_id = :id
                    ";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id' => $id]);
                    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$comment) {
                        Response::error('Commentaire non trouvé', 404);
                        exit();
                    }
                    
                    Response::success($comment);
                } else {
                    // GET /api/comments - Liste des commentaires
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
                    
                    Response::success([
                        'message' => 'Liste des commentaires récupérée depuis la base de données',
                        'comments' => $comments,
                        'count' => count($comments)
                    ]);
                }
            }
            elseif ($method === 'POST') {
                if ($id && $action === 'reply') {
                    // POST /api/comments/{id}/reply - Ajouter une réponse de modérateur
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input || empty($input['moderator_reply'])) {
                        Response::error('Réponse du modérateur requise', 400);
                        exit();
                    }
                    
                    // Vérifier que le commentaire existe
                    $checkSql = "SELECT COUNT(*) FROM comment WHERE comment_id = :id";
                    $checkStmt = $pdo->prepare($checkSql);
                    $checkStmt->execute(['id' => $id]);
                    
                    if ($checkStmt->fetchColumn() == 0) {
                        Response::error('Commentaire non trouvé', 404);
                        exit();
                    }
                    
                    $sql = "UPDATE comment SET moderator_reply = :reply, reply_date = :date WHERE comment_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'reply' => $input['moderator_reply'],
                        'date' => date('Y-m-d H:i:s'),
                        'id' => $id
                    ]);
                    
                    Response::success(['message' => 'Réponse de modérateur ajoutée avec succès']);
                } elseif (!$id) {
                    // POST /api/comments - Créer un commentaire
                    $input = json_decode(file_get_contents('php://input'), true);
                    
                    if (!$input) {
                        Response::error('Données JSON invalides', 400);
                        exit();
                    }
                    
                    // Validation
                    $errors = [];
                    if (empty($input['user_id']) || !is_numeric($input['user_id'])) 
                        $errors['user_id'] = 'L\'ID utilisateur est requis et doit être un nombre';
                    if (empty($input['jpo_id']) || !is_numeric($input['jpo_id'])) 
                        $errors['jpo_id'] = 'L\'ID JPO est requis et doit être un nombre';
                    if (empty($input['content'])) 
                        $errors['content'] = 'Le contenu du commentaire est requis';
                    
                    if (!empty($errors)) {
                        Response::error('Données invalides', 400, $errors);
                        exit();
                    }
                    
                    $sql = "INSERT INTO comment (user_id, jpo_id, content, comment_date) VALUES (:user_id, :jpo_id, :content, :date)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'user_id' => $input['user_id'],
                        'jpo_id' => $input['jpo_id'],
                        'content' => $input['content'],
                        'date' => date('Y-m-d H:i:s')
                    ]);
                    
                    $newId = $pdo->lastInsertId();
                    Response::success(['message' => 'Commentaire créé avec succès', 'id' => $newId], 201);
                } else {
                    Response::error('Action non trouvée', 404);
                }
            }
            elseif ($method === 'PUT' && $id) {
                // PUT /api/comments/{id} - Modifier un commentaire
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    Response::error('Données JSON invalides', 400);
                    exit();
                }
                
                // Vérifier que le commentaire existe
                $checkSql = "SELECT COUNT(*) FROM comment WHERE comment_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $id]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    Response::error('Commentaire non trouvé', 404);
                    exit();
                }
                
                if (isset($input['content']) && !empty($input['content'])) {
                    $sql = "UPDATE comment SET content = :content WHERE comment_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['content' => $input['content'], 'id' => $id]);
                    
                    Response::success(['message' => 'Commentaire mis à jour avec succès']);
                } else {
                    Response::error('Contenu du commentaire requis', 400);
                }
            }
            elseif ($method === 'DELETE' && $id) {
                // DELETE /api/comments/{id} - Supprimer un commentaire
                
                // Vérifier que le commentaire existe
                $checkSql = "SELECT COUNT(*) FROM comment WHERE comment_id = :id";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute(['id' => $id]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    Response::error('Commentaire non trouvé', 404);
                    exit();
                }
                
                $sql = "DELETE FROM comment WHERE comment_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                
                Response::success(['message' => 'Commentaire supprimé avec succès']);
            }
            else {
                Response::error('Méthode non autorisée', 405);
            }
        } catch (Exception $e) {
            Response::error('Erreur de base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Route Roles - Liste tous les rôles
    if ($path === '/api/roles' && $method === 'GET') {
        try {
            $database = new Database();
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
            
            Response::success([
                'message' => 'Liste des rôles récupérée depuis la base de données',
                'roles' => $roles,
                'count' => count($roles)
            ]);
            
        } catch (Exception $e) {
            Response::error('Erreur de connexion à la base de données: ' . $e->getMessage(), 500);
        }
        exit();
    }

    // Route racine - Documentation de l'API
    if ($path === '/' || $path === '/api') {
        Response::success([
            'message' => '🚀 API JPO La Plateforme',
            'version' => '1.0.0',
            'endpoints' => [
                'ping' => 'GET /api/ping - Test de l\'API',
                'jpo' => [
                    'list' => 'GET /api/jpo - Liste des JPO',
                    'show' => 'GET /api/jpo/{id} - JPO spécifique',
                    'create' => 'POST /api/jpo - Créer une JPO',
                    'update' => 'PUT /api/jpo/{id} - Modifier une JPO',
                    'delete' => 'DELETE /api/jpo/{id} - Supprimer une JPO'
                ],
                'campus' => 'GET /api/campus - Liste des campus',
                'users' => 'GET /api/users - Liste des utilisateurs',
                'registrations' => [
                    'list' => 'GET /api/registrations - Liste des inscriptions',
                    'show' => 'GET /api/registrations/{id} - Inscription spécifique',
                    'create' => 'POST /api/registrations - Créer une inscription',
                    'update' => 'PUT /api/registrations/{id} - Modifier le statut',
                    'delete' => 'DELETE /api/registrations/{id} - Supprimer une inscription'
                ],
                'comments' => [
                    'list' => 'GET /api/comments - Liste des commentaires',
                    'show' => 'GET /api/comments/{id} - Commentaire spécifique',
                    'create' => 'POST /api/comments - Créer un commentaire',
                    'update' => 'PUT /api/comments/{id} - Modifier un commentaire',
                    'delete' => 'DELETE /api/comments/{id} - Supprimer un commentaire',
                    'reply' => 'POST /api/comments/{id}/reply - Réponse modérateur'
                ],
                'roles' => 'GET /api/roles - Liste des rôles'
            ],
            'status' => 'Opérationnel ✅',
            'database' => 'Connectée ✅',
            'cors' => 'Activé ✅',
            'crud_operations' => 'JPO, Inscriptions, Commentaires ✅'
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
            '/api/jpo/{id}',
            '/api/campus',
            '/api/users',
            '/api/registrations',
            '/api/registrations/{id}',
            '/api/comments',
            '/api/comments/{id}',
            '/api/comments/{id}/reply',
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