<?php

namespace JpoLaplateforme\Backend\Models;

use PDO;
use Exception;

class Campus
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les campus avec filtres optionnels
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT 
                c.campus_id,
                c.name,
                c.city,
                COUNT(DISTINCT od.jpo_id) as jpo_count,
                COUNT(DISTINCT od.jpo_id) FILTER (WHERE od.date >= CURRENT_DATE) as upcoming_jpo_count,
                COUNT(DISTINCT r.registration_id) as total_registrations
            FROM campus c
            LEFT JOIN open_day od ON c.campus_id = od.campus_id
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
        ";

        $conditions = [];
        $params = [];

        // Filtre par ville
        if (!empty($filters['city'])) {
            $conditions[] = "c.city = :city";
            $params['city'] = $filters['city'];
        }

        // Recherche textuelle
        if (!empty($filters['search'])) {
            $conditions[] = "(c.name LIKE :search OR c.city LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Ajouter les conditions WHERE si nécessaire
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY c.campus_id ORDER BY c.name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un campus par son ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                c.campus_id,
                c.name,
                c.city,
                COUNT(DISTINCT od.jpo_id) as jpo_count,
                COUNT(DISTINCT od.jpo_id) FILTER (WHERE od.date >= CURRENT_DATE) as upcoming_jpo_count,
                COUNT(DISTINCT r.registration_id) as total_registrations
            FROM campus c
            LEFT JOIN open_day od ON c.campus_id = od.campus_id
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            WHERE c.campus_id = :id
            GROUP BY c.campus_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Récupère toutes les JPO d'un campus
     */
    public function getJposByCampus(int $campusId): array
    {
        $sql = "
            SELECT 
                od.jpo_id,
                od.name,
                od.date,
                od.max_capacity,
                COUNT(DISTINCT r.registration_id) as registered_count,
                COUNT(DISTINCT c.comment_id) as comments_count
            FROM open_day od
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            LEFT JOIN comment c ON od.jpo_id = c.jpo_id
            WHERE od.campus_id = :campus_id
            GROUP BY od.jpo_id
            ORDER BY od.date ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['campus_id' => $campusId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les statistiques d'un campus
     */
    public function getStatisticsByCampus(int $campusId): array
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT od.jpo_id) as total_jpo,
                COUNT(DISTINCT od.jpo_id) FILTER (WHERE od.date >= CURRENT_DATE) as upcoming_jpo,
                COUNT(DISTINCT od.jpo_id) FILTER (WHERE od.date < CURRENT_DATE) as past_jpo,
                COUNT(DISTINCT r.registration_id) as total_registrations,
                AVG(od.max_capacity) as avg_capacity
            FROM open_day od
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            WHERE od.campus_id = :campus_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['campus_id' => $campusId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau campus
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO campus (name, city)
            VALUES (:name, :city)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'city' => $data['city']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour un campus
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }

        if (isset($data['city'])) {
            $fields[] = "city = :city";
            $params['city'] = $data['city'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE campus SET " . implode(', ', $fields) . " WHERE campus_id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime un campus
     */
    public function delete(int $id): bool
    {
        // Vérifier s'il y a des JPO liées à ce campus
        $checkSql = "SELECT COUNT(*) FROM open_day WHERE campus_id = :id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute(['id' => $id]);

        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception('Impossible de supprimer un campus qui a des JPO associées');
        }

        $sql = "DELETE FROM campus WHERE campus_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un campus existe
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM campus WHERE campus_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère la liste des villes uniques
     */
    public function getCities(): array
    {
        $sql = "SELECT DISTINCT city FROM campus ORDER BY city ASC";
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère les statistiques générales
     */
    public function getStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_campus,
                COUNT(DISTINCT city) as total_cities,
                AVG(jpo_stats.jpo_count) as avg_jpo_per_campus
            FROM campus c
            LEFT JOIN (
                SELECT campus_id, COUNT(*) as jpo_count 
                FROM open_day 
                GROUP BY campus_id
            ) jpo_stats ON c.campus_id = jpo_stats.campus_id
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}