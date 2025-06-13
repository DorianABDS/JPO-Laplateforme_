<?php

namespace JpoLaplateforme\Backend\Models;

use PDO;
use Exception;

class Comment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les commentaires avec filtres optionnels
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT 
                c.comment_id,
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
                od.date as jpo_date,
                od.campus_id,
                campus.name as campus_name,
                campus.city as campus_city
            FROM comment c
            LEFT JOIN user u ON c.user_id = u.user_id
            LEFT JOIN open_day od ON c.jpo_id = od.jpo_id
            LEFT JOIN campus ON od.campus_id = campus.campus_id
        ";

        $conditions = [];
        $params = [];

        // Filtre par utilisateur
        if (!empty($filters['user_id'])) {
            $conditions[] = "c.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        // Filtre par JPO
        if (!empty($filters['jpo_id'])) {
            $conditions[] = "c.jpo_id = :jpo_id";
            $params['jpo_id'] = $filters['jpo_id'];
        }

        // Filtre par présence de réponse modérateur
        if (isset($filters['has_reply'])) {
            if ($filters['has_reply'] === 'true' || $filters['has_reply'] === '1') {
                $conditions[] = "c.moderator_reply IS NOT NULL";
            } else {
                $conditions[] = "c.moderator_reply IS NULL";
            }
        }

        // Filtre par date de début
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(c.comment_date) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        // Filtre par date de fin
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(c.comment_date) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        // Recherche textuelle
        if (!empty($filters['search'])) {
            $conditions[] = "(c.content LIKE :search OR c.moderator_reply LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Ajouter les conditions WHERE si nécessaire
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY c.comment_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un commentaire par son ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                c.comment_id,
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
                od.date as jpo_date,
                od.campus_id,
                campus.name as campus_name,
                campus.city as campus_city
            FROM comment c
            LEFT JOIN user u ON c.user_id = u.user_id
            LEFT JOIN open_day od ON c.jpo_id = od.jpo_id
            LEFT JOIN campus ON od.campus_id = campus.campus_id
            WHERE c.comment_id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crée un nouveau commentaire
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO comment (user_id, jpo_id, content, comment_date)
            VALUES (:user_id, :jpo_id, :content, :comment_date)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'jpo_id' => $data['jpo_id'],
            'content' => $data['content'],
            'comment_date' => $data['comment_date'] ?? date('Y-m-d H:i:s')
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour un commentaire
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['content'])) {
            $fields[] = "content = :content";
            $params['content'] = $data['content'];
        }

        if (isset($data['moderator_reply'])) {
            $fields[] = "moderator_reply = :moderator_reply";
            $params['moderator_reply'] = $data['moderator_reply'];
        }

        if (isset($data['reply_date'])) {
            $fields[] = "reply_date = :reply_date";
            $params['reply_date'] = $data['reply_date'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE comment SET " . implode(', ', $fields) . " WHERE comment_id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime un commentaire
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM comment WHERE comment_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Ajoute une réponse de modérateur
     */
    public function addModeratorReply(int $id, string $reply): bool
    {
        $sql = "UPDATE comment SET moderator_reply = :reply, reply_date = :date WHERE comment_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'reply' => $reply,
            'date' => date('Y-m-d H:i:s'),
            'id' => $id
        ]);
    }

    /**
     * Vérifie si un commentaire existe
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM comment WHERE comment_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les statistiques des commentaires
     */
    public function getStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_comments,
                COUNT(CASE WHEN moderator_reply IS NOT NULL THEN 1 END) as replied_comments,
                COUNT(CASE WHEN moderator_reply IS NULL THEN 1 END) as pending_comments,
                COUNT(DISTINCT user_id) as unique_commenters,
                COUNT(DISTINCT jpo_id) as jpo_with_comments
            FROM comment
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}