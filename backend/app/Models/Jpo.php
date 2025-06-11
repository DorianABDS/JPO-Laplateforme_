<?php

namespace JpoLaplateforme\Backend\Models;

use PDO;
use Exception;

class Jpo
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les JPO avec filtres optionnels
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT 
                od.jpo_id,
                od.name,
                od.date,
                od.max_capacity,
                c.campus_id,
                c.name as campus_name,
                c.city as campus_city,
                COUNT(DISTINCT r.registration_id) as registered_count,
                COUNT(DISTINCT com.comment_id) as comments_count
            FROM open_day od
            LEFT JOIN campus c ON od.campus_id = c.campus_id
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            LEFT JOIN comment com ON od.jpo_id = com.jpo_id
        ";

        $conditions = [];
        $params = [];

        // Filtre par campus
        if (!empty($filters['campus_id'])) {
            $conditions[] = "od.campus_id = :campus_id";
            $params['campus_id'] = $filters['campus_id'];
        }

        // Filtre par date de début
        if (!empty($filters['date_from'])) {
            $conditions[] = "od.date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        // Filtre par date de fin
        if (!empty($filters['date_to'])) {
            $conditions[] = "od.date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        // Recherche textuelle
        if (!empty($filters['search'])) {
            $conditions[] = "(od.name LIKE :search OR c.name LIKE :search OR c.city LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Ajouter les conditions WHERE si nécessaire
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY od.jpo_id ORDER BY od.date ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une JPO par son ID avec tous les détails
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                od.jpo_id,
                od.name,
                od.date,
                od.max_capacity,
                c.campus_id,
                c.name as campus_name,
                c.city as campus_city,
                COUNT(DISTINCT r.registration_id) as registered_count,
                COUNT(DISTINCT com.comment_id) as comments_count
            FROM open_day od
            LEFT JOIN campus c ON od.campus_id = c.campus_id
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            LEFT JOIN comment com ON od.jpo_id = com.jpo_id
            WHERE od.jpo_id = :id
            GROUP BY od.jpo_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $jpo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$jpo) {
            return null;
        }

        // Récupérer les commentaires séparément avec plus de détails
        $jpo['comments'] = $this->getCommentsByJpo($id);

        return $jpo;
    }

    /**
     * Récupère les commentaires d'une JPO
     */
    public function getCommentsByJpo(int $jpoId): array
    {
        $sql = "
            SELECT 
                c.comment_id,
                c.content,
                c.comment_date,
                c.moderator_reply,
                c.reply_date,
                u.user_id,
                u.first_name,
                u.last_name,
                u.user_type
            FROM comment c
            LEFT JOIN user u ON c.user_id = u.user_id
            WHERE c.jpo_id = :jpo_id
            ORDER BY c.comment_date DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['jpo_id' => $jpoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les inscriptions d'une JPO
     */
    public function getRegistrationsByJpo(int $jpoId): array
    {
        $sql = "
            SELECT 
                r.registration_id,
                r.registration_date,
                r.status,
                u.user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.user_type
            FROM registration r
            LEFT JOIN user u ON r.user_id = u.user_id
            WHERE r.jpo_id = :jpo_id
            ORDER BY r.registration_date DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['jpo_id' => $jpoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle JPO
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO open_day (name, date, max_capacity, campus_id)
            VALUES (:name, :date, :max_capacity, :campus_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'date' => $data['date'],
            'max_capacity' => $data['max_capacity'],
            'campus_id' => $data['campus_id']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour une JPO
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        // Construire dynamiquement la requête UPDATE
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }

        if (isset($data['date'])) {
            $fields[] = "date = :date";
            $params['date'] = $data['date'];
        }

        if (isset($data['max_capacity'])) {
            $fields[] = "max_capacity = :max_capacity";
            $params['max_capacity'] = $data['max_capacity'];
        }

        if (isset($data['campus_id'])) {
            $fields[] = "campus_id = :campus_id";
            $params['campus_id'] = $data['campus_id'];
        }

        if (empty($fields)) {
            return false; // Rien à mettre à jour
        }

        $sql = "UPDATE open_day SET " . implode(', ', $fields) . " WHERE jpo_id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime une JPO
     */
    public function delete(int $id): bool
    {
        // Note: Dans un vrai projet, vous pourriez vouloir vérifier
        // s'il y a des inscriptions ou commentaires avant de supprimer
        
        $sql = "DELETE FROM open_day WHERE jpo_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si une JPO existe
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM open_day WHERE jpo_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les statistiques générales
     */
    public function getStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_jpo,
                COUNT(CASE WHEN date >= CURDATE() THEN 1 END) as upcoming_jpo,
                COUNT(CASE WHEN date < CURDATE() THEN 1 END) as past_jpo,
                AVG(max_capacity) as avg_capacity
            FROM open_day
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}