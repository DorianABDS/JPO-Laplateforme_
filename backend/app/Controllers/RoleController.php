<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\Role;
use JpoLaplateforme\Backend\Config\Database;
use Exception;

class RoleController
{
    private Role $roleModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->roleModel = new Role($pdo);
    }

    /**
     * Liste tous les rôles
     * Route : GET /api/roles
     */
    public function index(): void
    {
        try {
            // Récupère les filtres depuis la requête GET
            $filters = [
                'search' => $_GET['search'] ?? null,
            ];

            // Valide les filtres
            $validationErrors = $this->validateListFilters($filters);
            if (!empty($validationErrors)) {
                Response::error('Paramètres invalides', 400, $validationErrors);
                return;
            }

            // Supprime les filtres vides
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // Récupère les rôles depuis le modèle
            $roles = $this->roleModel->getAll($filters);

            // Formate les données pour la réponse
            $formattedRoles = array_map([$this, 'formatRoleForList'], $roles);

            // Envoie la réponse JSON
            Response::success([
                'roles' => $formattedRoles,
                'count' => count($formattedRoles),
                'filters_applied' => $filters
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des rôles',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère un rôle précis via son ID
     * Route : GET /api/roles/{id}
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
            $role = $this->roleModel->getById($id);

            if (!$role) {
                Response::error('Rôle non trouvé', 404);
                return;
            }

            $formattedRole = $this->formatRoleForDetails($role);
            Response::success($formattedRole);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération du rôle',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère tous les utilisateurs d'un rôle
     * Route : GET /api/roles/{id}/users
     */
    public function getUsers(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;
            $users = $this->roleModel->getUsersByRole($id);

            Response::success([
                'role_id' => $id,
                'users' => $users,
                'count' => count($users)
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des utilisateurs du rôle',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Crée un nouveau rôle
     * Route : POST /api/roles
     */
    public function create(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                Response::error('Données JSON invalides', 400);
                return;
            }

            // Valide les données requises
            $validationErrors = $this->validateCreateData($input);
            if (!empty($validationErrors)) {
                Response::error('Données invalides', 400, $validationErrors);
                return;
            }

            // Crée le rôle
            $roleId = $this->roleModel->create($input);
            
            // Récupère le rôle créé
            $role = $this->roleModel->getById($roleId);
            $formattedRole = $this->formatRoleForDetails($role);

            Response::success($formattedRole, 201);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la création du rôle',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Met à jour un rôle
     * Route : PUT /api/roles/{id}
     */
    public function update(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                Response::error('Données JSON invalides', 400);
                return;
            }

            $id = (int) $id;

            // Vérifie que le rôle existe
            if (!$this->roleModel->getById($id)) {
                Response::error('Rôle non trouvé', 404);
                return;
            }

            // Met à jour le rôle
            $this->roleModel->update($id, $input);
            
            // Récupère le rôle mis à jour
            $role = $this->roleModel->getById($id);
            $formattedRole = $this->formatRoleForDetails($role);

            Response::success($formattedRole);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la mise à jour du rôle',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Supprime un rôle
     * Route : DELETE /api/roles/{id}
     */
    public function delete(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;

            // Vérifie que le rôle existe
            $role = $this->roleModel->getById($id);
            if (!$role) {
                Response::error('Rôle non trouvé', 404);
                return;
            }

            // Vérifie qu'aucun utilisateur n'a ce rôle
            $users = $this->roleModel->getUsersByRole($id);
            if (!empty($users)) {
                Response::error('Impossible de supprimer un rôle qui a des utilisateurs assignés', 400, [
                    'users_count' => count($users)
                ]);
                return;
            }

            // Supprime le rôle
            $this->roleModel->delete($id);

            Response::success(['message' => 'Rôle supprimé avec succès']);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la suppression du rôle',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    private function validateListFilters(array $filters): array
    {
        $errors = [];

        if ($filters['search'] !== null && strlen(trim($filters['search'])) < 2) {
            $errors['search'] = 'La recherche doit contenir au moins 2 caractères';
        }

        return $errors;
    }

    private function validateCreateData(array $data): array
    {
        $errors = [];

        if (!isset($data['role_name']) || trim($data['role_name']) === '') {
            $errors['role_name'] = 'Le nom du rôle est requis';
        }

        if (isset($data['role_name']) && strlen(trim($data['role_name'])) < 2) {
            $errors['role_name'] = 'Le nom du rôle doit contenir au moins 2 caractères';
        }

        if (isset($data['role_name']) && strlen(trim($data['role_name'])) > 50) {
            $errors['role_name'] = 'Le nom du rôle ne peut pas dépasser 50 caractères';
        }

        return $errors;
    }

    private function formatRoleForList(array $role): array
    {
        return [
            'id' => (int) $role['role_id'],
            'name' => $role['role_name']
        ];
    }

    private function formatRoleForDetails(array $role): array
    {
        $formatted = $this->formatRoleForList($role);
        
        // Ajouter des statistiques si disponibles
        if (isset($role['users_count'])) {
            $formatted['statistics'] = [
                'total_users' => (int) $role['users_count'],
                'active_users' => (int) ($role['active_users'] ?? 0)
            ];
        }

        return $formatted;
    }
}