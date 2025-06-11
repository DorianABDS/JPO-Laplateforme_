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

        if (!empty($filters['search'])) {
            $conditions[] = "(od.name LIKE :search OR c.name LIKE :search OR c.city LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Ajoute les conditions à la requête
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Regroupe les résultats et les trie par date
        $sql .= " GROUP BY od.jpo_id, od.name, od.date, od.max_capacity, od.campus_id, c.name, c.city";
        $sql .= " ORDER BY od.date ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Erreur lors de la récupération des JPO : " . $e->getMessage());
        }
    }

    // Récupère les détails d'une JPO par son ID
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                od.jpo_id,
                od.name,
                od.date,
                od.max_capacity,
                od.campus_id,
                c.name as campus_name,
                c.city as campus_city,
                COALESCE(COUNT(DISTINCT r.registration_id), 0) as registered_count,
                COALESCE(COUNT(DISTINCT com.comment_id), 0) as comments_count
            FROM open_day od
            LEFT JOIN campus c ON od.campus_id = c.campus_id
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            LEFT JOIN comment com ON od.jpo_id = com.jpo_id
            WHERE od.jpo_id = :id
            GROUP BY od.jpo_id, od.name, od.date, od.max_capacity, od.campus_id, c.name, c.city
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $jpo = $stmt->fetch(PDO::FETCH_ASSOC);

            // Retourne null si la JPO n'existe pas
            if (!$jpo) {
                return null;
            }

            // Ajoute les commentaires liés à cette JPO
            $jpo['comments'] = $this->getCommentsByJpoId($id);

            return $jpo;
        } catch (\PDOException $e) {
            throw new Exception("Erreur lors de la récupération de la JPO : " . $e->getMessage());
        }
    }

    // Récupère tous les commentaires liés à une JPO
    private function getCommentsByJpoId(int $jpoId): array
    {
        $sql = "
            SELECT 
                c.comment_id,
                c.content,
                c.comment_date,
                c.moderator_reply,
                c.reply_date,
                u.first_name,
                u.last_name,
                u.user_type
            FROM comment c
            LEFT JOIN user u ON c.user_id = u.user_id
            WHERE c.jpo_id = :jpo_id
            ORDER BY c.comment_date DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['jpo_id' => $jpoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Erreur lors de la récupération des commentaires : " . $e->getMessage());
        }
    }
}
