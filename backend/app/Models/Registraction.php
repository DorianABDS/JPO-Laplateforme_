<?php

namespace JpoLaplateforme\Backend\Models;

use PDO;
use Exception;

class Registration
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les inscriptions avec filtres optionnels
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT 
                r.registration_id,
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
                od.max_capacity,
                c.name as campus_name,
                c.city as campus_city
            FROM registration r
            LEFT JOIN user u ON r.user_id = u.user_id
            LEFT JOIN open_day od ON r.jpo_id = od.jpo_id
            LEFT JOIN campus c ON od.campus_id = c.campus_id
        ";

        $conditions = [];
        $params = [];

        // Filtre par utilisateur
        if (!empty($filters['user_id'])) {
            $conditions[] = "r.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        // Filtre par JPO
        if (!empty($filters['jpo_id'])) {
            $conditions[] = "r.jpo_id = :jpo_id";
            $params['jpo_id'] = $filters['jpo_id'];
        }

        // Filtre par statut
        if (!empty($filters['status'])) {
            $conditions[] = "r.status = :status";
            $params['status'] = $filters['status'];
        }

        // Filtre par type d'utilisateur
        if (!empty($filters['user_type'])) {
            $conditions[] = "u.user_type = :user_type";
            $params['user_type'] = $filters['user_type'];
        }

        // Filtre par date de début
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(r.registration_date) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        // Filtre par date de fin
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(r.registration_date) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        // Ajouter les conditions WHERE si nécessaire
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY r.registration_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une inscription par son ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT 
                r.registration_id,
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
                od.max_capacity,
                c.campus_id,
                c.name as campus_name,
                c.city as campus_city
            FROM registration r
            LEFT JOIN user u ON r.user_id = u.user_id
            LEFT JOIN open_day od ON r.jpo_id = od.jpo_id
            LEFT JOIN campus c ON od.campus_id = c.campus_id
            WHERE r.registration_id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Crée une nouvelle inscription
     */
    public function create(array $data): int
    {
        // Vérifier si l'utilisateur est déjà inscrit à cette JPO
        if ($this->isUserRegistered($data['user_id'], $data['jpo_id'])) {
            throw new Exception('L\'utilisateur est déjà inscrit à cette JPO');
        }

        // Vérifier si la JPO n'est pas complète
        if ($this->isJpoFull($data['jpo_id'])) {
            throw new Exception('Cette JPO est complète, aucune inscription supplémentaire possible');
        }

        $sql = "
            INSERT INTO registration (user_id, jpo_id, registration_date, status)
            VALUES (:user_id, :jpo_id, :registration_date, :status)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'jpo_id' => $data['jpo_id'],
            'registration_date' => $data['registration_date'] ?? date('Y-m-d H:i:s'),
            'status' => $data['status'] ?? 'registered'
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour une inscription
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params['status'] = $data['status'];
        }

        if (isset($data['registration_date'])) {
            $fields[] = "registration_date = :registration_date";
            $params['registration_date'] = $data['registration_date'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE registration SET " . implode(', ', $fields) . " WHERE registration_id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprime une inscription
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM registration WHERE registration_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vérifie si un utilisateur est déjà inscrit à une JPO
     */
    public function isUserRegistered(int $userId, int $jpoId): bool
    {
        $sql = "
            SELECT COUNT(*) 
            FROM registration 
            WHERE user_id = :user_id AND jpo_id = :jpo_id AND status = 'registered'
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'jpo_id' => $jpoId]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie si une JPO est complète
     */
    public function isJpoFull(int $jpoId): bool
    {
        $sql = "
            SELECT 
                od.max_capacity,
                COUNT(r.registration_id) as current_registrations
            FROM open_day od
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            WHERE od.jpo_id = :jpo_id
            GROUP BY od.jpo_id, od.max_capacity
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['jpo_id' => $jpoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return true; // JPO non trouvée, considérée comme complète
        }
        
        return $result['current_registrations'] >= $result['max_capacity'];
    }

    /**
     * Récupère les inscriptions par utilisateur
     */
    public function getByUser(int $userId): array
    {
        return $this->getAll(['user_id' => $userId]);
    }

    /**
     * Récupère les inscriptions par JPO
     */
    public function getByJpo(int $jpoId): array
    {
        return $this->getAll(['jpo_id' => $jpoId]);
    }

    /**
     * Récupère les statistiques des inscriptions
     */
    public function getStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_registrations,
                COUNT(CASE WHEN status = 'registered' THEN 1 END) as active_registrations,
                COUNT(CASE WHEN status = 'unregistered' THEN 1 END) as cancelled_registrations,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT jpo_id) as jpo_with_registrations
            FROM registration
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les statistiques par JPO
     */
    public function getStatisticsByJpo(): array
    {
        $sql = "
            SELECT 
                od.jpo_id,
                od.name as jpo_name,
                od.max_capacity,
                COUNT(r.registration_id) as registration_count,
                (od.max_capacity - COUNT(r.registration_id)) as available_spots,
                ROUND((COUNT(r.registration_id) / od.max_capacity) * 100, 2) as fill_rate
            FROM open_day od
            LEFT JOIN registration r ON od.jpo_id = r.jpo_id AND r.status = 'registered'
            GROUP BY od.jpo_id, od.name, od.max_capacity
            ORDER BY fill_rate DESC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Annule une inscription (change le statut)
     */
    public function cancel(int $id): bool
    {
        return $this->update($id, ['status' => 'unregistered']);
    }

    /**
     * Réactive une inscription (change le statut)
     */
    public function reactivate(int $id): bool
    {
        // Vérifier si la JPO n'est pas complète avant de réactiver
        $registration = $this->getById($id);
        if (!$registration) {
            return false;
        }

        if ($this->isJpoFull($registration['jpo_id'])) {
            throw new Exception('Impossible de réactiver : la JPO est complète');
        }

        return $this->update($id, ['status' => 'registered']);
    }
}