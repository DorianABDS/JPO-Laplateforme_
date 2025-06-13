<?php

namespace JpoLaplateforme\Backend\Models;

use PDO;
use Exception;

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les utilisateurs avec filtres optionnels
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT 
                u.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.user_type,
                u.created_at,
                u.role_id,
                r.role_name,
                COUNT(DISTINCT reg.registration_id) as registrations_count,
                COUNT(DISTINCT c.comment_id) as comments_count
            FROM user u
            LEFT JOIN role r ON u.role_id = r.role_id
            LEFT JOIN registration reg ON u.user_id = reg.user_id AND reg.status = 'registered'
            LEFT JOIN comment c ON u.user_id = c.user_id
        ";

        $conditions = [];
        $params = [];

        // Filtres
        if (!empty($filters['user_type'])) {
            $conditions[] = "u.user_type = :user_type";
            $params['user_type'] = $filters['user_type'];
        }

        if (!empty($filters['role_id'])) {
            $conditions[] = "u.role_id = :role_id";
            $params['role_id'] = $filters['role_id'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['created_from'])) {
            $conditions[] = "DATE(u.created_at) >= :created_from";
            $params['created_from'] = $filters['created_from'];
        }

        if (!empty($filters['created_to'])) {
            $conditions[] = "DATE(u.created_at) <= :created_to";
            $params['created_to'] = $filters['created_to'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY u.user_id ORDER BY u.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                u.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.user_type,
                u.created_at,
                u.role_id,
                r.role_name,
                COUNT(DISTINCT reg.registration_id) as registrations_count,
                COUNT(DISTINCT c.comment_id) as comments_count
            FROM user u
            LEFT JOIN role r ON u.role_id = r.role_id
            LEFT JOIN registration reg ON u.user_id = reg.user_id AND reg.status = 'registered'
            LEFT JOIN comment c ON u.user_id = c.user_id
            WHERE u.user_id = :id
            GROUP BY u.user_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Récupère les inscriptions d'un utilisateur
     */
    public function getRegistrationsByUser(int $userId): array
    {
        $sql = "
            SELECT 
                r.registration_id,
                r.registration_date,
                r.status,
                od.jpo_id,
                od.name as jpo_name,
                od.date as jpo_date,
                c.name as campus_name,
                c.city as campus_city
            FROM registration r
            LEFT JOIN open_day od ON r.jpo_id = od.jpo_id
            LEFT JOIN campus c ON od.campus_id = c.campus_id
            WHERE r.user_id = :user_id
            ORDER BY r.registration_date DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les commentaires d'un utilisateur
     */
    public function getCommentsByUser(int $userId): array
    {
        $sql = "
            SELECT 
                c.comment_id,
                c.content,
                c.comment_date,
                c.moderator_reply,
                c.reply_date,
                od.jpo_id,
                od.name as jpo_name,
                od.date as jpo_date
            FROM comment c
            LEFT JOIN open_day od ON c.jpo_id = od.jpo_id
            WHERE c.user_id = :user_id
            ORDER BY c.comment_date DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO user (first_name, last_name, email, user_type, role_id, created_at) 
            VALUES (:first_name, :last_name, :email, :user_type, :role_id, NOW())
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'user_type' => $data['user_type'],
            'role_id' => $data['role_id'] ?? null
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['first_name'])) {
            $fields[] = "first_name = :first_name";
            $params['first_name'] = $data['first_name'];
        }

        if (isset($data['last_name'])) {
            $fields[] = "last_name = :last_name";
            $params['last_name'] = $data['last_name'];
        }

        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params['email'] = $data['email'];
        }

        if (isset($data['user_type'])) {
            $fields[] = "user_type = :user_type";
            $params['user_type'] = $data['user_type'];
        }

        if (isset($data['role_id'])) {
            $fields[] = "role_id = :role_id";
            $params['role_id'] = $data['role_id'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE user SET " . implode(', ', $fields) . " WHERE user_id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime un utilisateur
     */
    public function delete(int $id): bool
    {
        // Vérifier s'il y a des inscriptions ou commentaires
        $checkSql = "
            SELECT 
                COUNT(DISTINCT r.registration_id) as registrations,
                COUNT(DISTINCT c.comment_id) as comments
            FROM user u
            LEFT JOIN registration r ON u.user_id = r.user_id
            LEFT JOIN comment c ON u.user_id = c.user_id
            WHERE u.user_id = :id
        ";

        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute(['id' => $id]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($result['registrations'] > 0 || $result['comments'] > 0) {
            throw new Exception('Impossible de supprimer un utilisateur qui a des inscriptions ou des commentaires');
        }

        $sql = "DELETE FROM user WHERE user_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}