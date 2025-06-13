<?php

namespace JpoLaplateforme\Backend\Models;

use PDO;
use Exception;

class Role
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les rôles avec filtres optionnels
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT 
                r.role_id,
                r.role_name,
                COUNT(u.user_id) as users_count,
                COUNT(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_count
            FROM role r
            LEFT JOIN user u ON r.role_id = u.role_id
        ";

        $conditions = [];
        $params = [];

        // Recherche textuelle
        if (!empty($filters['search'])) {
            $conditions[] = "r.role_name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Filtre par rôles ayant des utilisateurs
        if (!empty($filters['has_users'])) {
            if ($filters['has_users'] === 'true' || $filters['has_users'] === '1') {
                $conditions[] = "u.user_id IS NOT NULL";
            } else {
                $conditions[] = "u.user_id IS NULL";
            }
        }

        // Ajouter les conditions WHERE si nécessaire
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY r.role_id, r.role_name ORDER BY r.role_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un rôle par son ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                r.role_id,
                r.role_name,
                COUNT(u.user_id) as users_count,
                COUNT(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_count,
                COUNT(CASE WHEN u.user_type = 'student' THEN 1 END) as students_count,
                COUNT(CASE WHEN u.user_type = 'parent' THEN 1 END) as parents_count,
                COUNT(CASE WHEN u.user_type = 'marketing_member' THEN 1 END) as marketing_count
            FROM role r
            LEFT JOIN user u ON r.role_id = u.role_id
            WHERE r.role_id = :id
            GROUP BY r.role_id, r.role_name
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Récupère tous les utilisateurs d'un rôle
     */
    public function getUsersByRole(int $roleId): array
    {
        $sql = "
            SELECT 
                u.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.user_type,
                u.created_at,
                COUNT(DISTINCT r.registration_id) as registrations_count,
                COUNT(DISTINCT c.comment_id) as comments_count
            FROM user u
            LEFT JOIN registration r ON u.user_id = r.user_id AND r.status = 'registered'
            LEFT JOIN comment c ON u.user_id = c.user_id
            WHERE u.role_id = :role_id
            GROUP BY u.user_id, u.first_name, u.last_name, u.email, u.user_type, u.created_at
            ORDER BY u.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau rôle
     */
    public function create(array $data): int
    {
        // Vérifier que le nom de rôle n'existe pas déjà
        if ($this->roleNameExists($data['role_name'])) {
            throw new Exception('Un rôle avec ce nom existe déjà');
        }

        $sql = "INSERT INTO role (role_name) VALUES (:role_name)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'role_name' => $data['role_name']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour un rôle
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['role_name'])) {
            // Vérifier que le nouveau nom n'existe pas déjà (sauf pour ce rôle)
            if ($this->roleNameExists($data['role_name'], $id)) {
                throw new Exception('Un rôle avec ce nom existe déjà');
            }
            
            $fields[] = "role_name = :role_name";
            $params['role_name'] = $data['role_name'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE role SET " . implode(', ', $fields) . " WHERE role_id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime un rôle
     */
    public function delete(int $id): bool
    {
        // Vérifier s'il y a des utilisateurs avec ce rôle
        $userCount = $this->getUsersCountByRole($id);
        if ($userCount > 0) {
            throw new Exception("Impossible de supprimer un rôle assigné à {$userCount} utilisateur(s)");
        }

        $sql = "DELETE FROM role WHERE role_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un rôle existe
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM role WHERE role_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie si un nom de rôle existe déjà
     */
    private function roleNameExists(string $roleName, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM role WHERE role_name = :role_name";
        $params = ['role_name' => $roleName];
        
        if ($excludeId !== null) {
            $sql .= " AND role_id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Compte le nombre d'utilisateurs pour un rôle
     */
    private function getUsersCountByRole(int $roleId): int
    {
        $sql = "SELECT COUNT(*) FROM user WHERE role_id = :role_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Assigne un rôle à un utilisateur
     */
    public function assignToUser(int $roleId, int $userId): bool
    {
        // Vérifier que le rôle et l'utilisateur existent
        if (!$this->exists($roleId)) {
            throw new Exception('Rôle non trouvé');
        }

        if (!$this->userExists($userId)) {
            throw new Exception('Utilisateur non trouvé');
        }

        $sql = "UPDATE user SET role_id = :role_id WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['role_id' => $roleId, 'user_id' => $userId]);
    }

    /**
     * Retire un rôle d'un utilisateur
     */
    public function removeFromUser(int $userId): bool
    {
        $sql = "UPDATE user SET role_id = NULL WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Récupère les statistiques des rôles
     */
    public function getStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_roles,
                COUNT(CASE WHEN u.user_id IS NOT NULL THEN 1 END) as roles_with_users,
                COUNT(CASE WHEN u.user_id IS NULL THEN 1 END) as roles_without_users,
                AVG(user_counts.user_count) as avg_users_per_role
            FROM role r
            LEFT JOIN user u ON r.role_id = u.role_id
            LEFT JOIN (
                SELECT role_id, COUNT(*) as user_count 
                FROM user 
                WHERE role_id IS NOT NULL 
                GROUP BY role_id
            ) user_counts ON r.role_id = user_counts.role_id
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la répartition des utilisateurs par rôle
     */
    public function getUserDistribution(): array
    {
        $sql = "
            SELECT 
                r.role_id,
                r.role_name,
                COUNT(u.user_id) as user_count,
                COUNT(CASE WHEN u.user_type = 'student' THEN 1 END) as students,
                COUNT(CASE WHEN u.user_type = 'parent' THEN 1 END) as parents,
                COUNT(CASE WHEN u.user_type = 'marketing_member' THEN 1 END) as marketing_members,
                ROUND((COUNT(u.user_id) * 100.0 / total_users.total), 2) as percentage
            FROM role r
            LEFT JOIN user u ON r.role_id = u.role_id
            CROSS JOIN (SELECT COUNT(*) as total FROM user WHERE role_id IS NOT NULL) total_users
            GROUP BY r.role_id, r.role_name, total_users.total
            ORDER BY user_count DESC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve les rôles les plus populaires
     */
    public function getMostPopularRoles(int $limit = 5): array
    {
        $sql = "
            SELECT 
                r.role_id,
                r.role_name,
                COUNT(u.user_id) as user_count
            FROM role r
            LEFT JOIN user u ON r.role_id = u.role_id
            GROUP BY r.role_id, r.role_name
            HAVING user_count > 0
            ORDER BY user_count DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve les rôles inutilisés
     */
    public function getUnusedRoles(): array
    {
        return $this->getAll(['has_users' => 'false']);
    }

    /**
     * Vérifie si un utilisateur existe
     */
    private function userExists(int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM user WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère un rôle par son nom
     */
    public function getByName(string $roleName): ?array
    {
        $sql = "
            SELECT 
                r.role_id,
                r.role_name,
                COUNT(u.user_id) as users_count
            FROM role r
            LEFT JOIN user u ON r.role_id = u.role_id
            WHERE r.role_name = :role_name
            GROUP BY r.role_id, r.role_name
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_name' => $roleName]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}