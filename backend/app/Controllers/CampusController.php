<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\Campus;
use JpoLaplateforme\Backend\Config\Database;
use Exception;

class CampusController
{
    private Campus $campusModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->campusModel = new Campus($pdo);
    }

    /**
     * Liste tous les campus
     * Route : GET /api/campus
     */
    public function index(): void
    {
        try {
            // Récupère les filtres depuis la requête GET
            $filters = [
                'city' => $_GET['city'] ?? null,
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

            // Récupère les campus depuis le modèle
            $campus = $this->campusModel->getAll($filters);

            // Formate les données pour la réponse
            $formattedCampus = array_map([$this, 'formatCampusForList'], $campus);

            // Envoie la réponse JSON
            Response::success([
                'campus' => $formattedCampus,
                'count' => count($formattedCampus),
                'filters_applied' => $filters
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des campus',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère un campus précis via son ID
     * Route : GET /api/campus/{id}
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
            $campus = $this->campusModel->getById($id);

            if (!$campus) {
                Response::error('Campus non trouvé', 404);
                return;
            }

            $formattedCampus = $this->formatCampusForDetails($campus);
            Response::success($formattedCampus);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération du campus',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère toutes les JPO d'un campus
     * Route : GET /api/campus/{id}/jpo
     */
    public function getJpos(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;
            $jpos = $this->campusModel->getJposByCampus($id);

            Response::success([
                'campus_id' => $id,
                'jpos' => $jpos,
                'count' => count($jpos)
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des JPO du campus',
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

    private function formatCampusForList(array $campus): array
    {
        return [
            'id' => (int) $campus['campus_id'],
            'name' => $campus['name'],
            'city' => $campus['city']
        ];
    }

    private function formatCampusForDetails(array $campus): array
    {
        $formatted = $this->formatCampusForList($campus);
        
        // Ajouter des statistiques si disponibles
        if (isset($campus['jpo_count'])) {
            $formatted['statistics'] = [
                'total_jpo' => (int) $campus['jpo_count'],
                'upcoming_jpo' => (int) ($campus['upcoming_jpo'] ?? 0),
                'total_registrations' => (int) ($campus['total_registrations'] ?? 0)
            ];
        }

        return $formatted;
    }
}