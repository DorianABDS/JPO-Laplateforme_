<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\User;
use JpoLaplateforme\Backend\Config\Database;
use Exception;

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->userModel = new User($pdo);
    }

    /**
     * Liste tous les utilisateurs
     * Route : GET /api/users
     */
    public function index(): void
    {
        try {
            // Récupère les filtres depuis la requête GET
            $filters = [
                'user_type' => $_GET['user_type'] ?? null,
                'role_id' => $_GET['role_id'] ?? null,
                'search' => $_GET['search'] ?? null,
                'created_from' => $_GET['created_from'] ?? null,
                'created_to' => $_GET['created_to'] ?? null,
            ];

            // Valide les filtres
            $validationErrors = $this->validateListFilters($filters);
            if (!empty($validationErrors)) {
                Response::error('Paramètres invalides', 400, $validationErrors);
                return;
            }

            // Supprime les filtres vides
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // Récupère les utilisateurs depuis le modèle
            $users = $this->userModel->getAll($filters);

            // Formate les données pour la réponse
            $formattedUsers = array_map([$this, 'formatUserForList'], $users);

            // Envoie la réponse JSON
            Response::success([
                'users' => $formattedUsers,
                'count' => count($formattedUsers),
                'filters_applied' => $filters
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des utilisateurs',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère un utilisateur précis via son ID
     * Route : GET /api/users/{id}
     */
    public function show(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;
            $user = $this->userModel->getById($id);

            if (!$user) {
                Response::error('Utilisateur non trouvé', 404);
                return;
            }

            $formattedUser = $this->formatUserForDetails($user);
            Response::success($formattedUser);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération de l\'utilisateur',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère les inscriptions d'un utilisateur
     * Route : GET /api/users/{id}/registrations
     */
    public function getRegistrations(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;
            $registrations = $this->userModel->getRegistrationsByUser($id);

            Response::success([
                'user_id' => $id,
                'registrations' => $registrations,
                'count' => count($registrations)
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des inscriptions',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère les commentaires d'un utilisateur
     * Route : GET /api/users/{id}/comments
     */
    public function getComments(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;
            $comments = $this->userModel->getCommentsByUser($id);

            Response::success([
                'user_id' => $id,
                'comments' => $comments,
                'count' => count($comments)
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des commentaires',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    private function validateListFilters(array $filters): array
    {
        $errors = [];

        if ($filters['user_type'] !== null && !in_array($filters['user_type'], ['student', 'parent', 'marketing_member'])) {
            $errors['user_type'] = 'Type d\'utilisateur invalide';
        }

        if ($filters['role_id'] !== null && (!is_numeric($filters['role_id']) || $filters['role_id'] <= 0)) {
            $errors['role_id'] = 'L\'ID du rôle doit être un nombre entier positif';
        }

        if ($filters['search'] !== null && strlen(trim($filters['search'])) < 2) {
            $errors['search'] = 'La recherche doit contenir au moins 2 caractères';
        }

        if ($filters['created_from'] !== null && !$this->isValidDate($filters['created_from'])) {
            $errors['created_from'] = 'La date de début doit être au format YYYY-MM-DD';
        }

        if ($filters['created_to'] !== null && !$this->isValidDate($filters['created_to'])) {
            $errors['created_to'] = 'La date de fin doit être au format YYYY-MM-DD';
        }

        return $errors;
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function formatUserForList(array $user): array
    {
        return [
            'id' => (int) $user['user_id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'user_type' => $user['user_type'],
            'created_at' => $user['created_at'],
            'role' => [
                'id' => (int) $user['role_id'],
                'name' => $user['role_name'] ?? null
            ]
        ];
    }

    private function formatUserForDetails(array $user): array
    {
        $formatted = $this->formatUserForList($user);
        
        // Ajouter des statistiques si disponibles
        if (isset($user['registrations_count'])) {
            $formatted['statistics'] = [
                'total_registrations' => (int) $user['registrations_count'],
                'active_registrations' => (int) ($user['active_registrations'] ?? 0),
                'total_comments' => (int) ($user['comments_count'] ?? 0)
            ];
        }

        return $formatted;
    }
}