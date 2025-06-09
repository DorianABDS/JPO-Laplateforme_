<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\Registration;
use JpoLaplateforme\Backend\Config\Database;
use Exception;

class RegistrationController
{
    private Registration $registrationModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->registrationModel = new Registration($pdo);
    }

    /**
     * Liste toutes les inscriptions
     * Route : GET /api/registrations
     */
    public function index(): void
    {
        try {
            // Récupère les filtres depuis la requête GET
            $filters = [
                'user_id' => $_GET['user_id'] ?? null,
                'jpo_id' => $_GET['jpo_id'] ?? null,
                'status' => $_GET['status'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'user_type' => $_GET['user_type'] ?? null,
            ];

            // Valide les filtres
            $validationErrors = $this->validateListFilters($filters);
            if (!empty($validationErrors)) {
                Response::error('Paramètres invalides', 400, $validationErrors);
                return;
            }

            // Supprime les filtres vides
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // Récupère les inscriptions depuis le modèle
            $registrations = $this->registrationModel->getAll($filters);

            // Formate les données pour la réponse
            $formattedRegistrations = array_map([$this, 'formatRegistrationForList'], $registrations);

            // Envoie la réponse JSON
            Response::success([
                'registrations' => $formattedRegistrations,
                'count' => count($formattedRegistrations),
                'filters_applied' => $filters
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
     * Récupère une inscription précise via son ID
     * Route : GET /api/registrations/{id}
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
            $registration = $this->registrationModel->getById($id);

            if (!$registration) {
                Response::error('Inscription non trouvée', 404);
                return;
            }

            $formattedRegistration = $this->formatRegistrationForDetails($registration);
            Response::success($formattedRegistration);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération de l\'inscription',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Crée une nouvelle inscription
     * Route : POST /api/registrations
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

            // Crée l'inscription
            $registrationId = $this->registrationModel->create($input);
            
            // Récupère l'inscription créée
            $registration = $this->registrationModel->getById($registrationId);
            $formattedRegistration = $this->formatRegistrationForDetails($registration);

            Response::success($formattedRegistration, 201);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la création de l\'inscription',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Met à jour une inscription
     * Route : PUT /api/registrations/{id}
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

            // Vérifie que l'inscription existe
            if (!$this->registrationModel->getById($id)) {
                Response::error('Inscription non trouvée', 404);
                return;
            }

            // Met à jour l'inscription
            $this->registrationModel->update($id, $input);
            
            // Récupère l'inscription mise à jour
            $registration = $this->registrationModel->getById($id);
            $formattedRegistration = $this->formatRegistrationForDetails($registration);

            Response::success($formattedRegistration);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la mise à jour de l\'inscription',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Supprime une inscription
     * Route : DELETE /api/registrations/{id}
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

            // Vérifie que l'inscription existe
            if (!$this->registrationModel->getById($id)) {
                Response::error('Inscription non trouvée', 404);
                return;
            }

            // Supprime l'inscription
            $this->registrationModel->delete($id);

            Response::success(['message' => 'Inscription supprimée avec succès']);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la suppression de l\'inscription',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    private function validateListFilters(array $filters): array
    {
        $errors = [];

        if ($filters['user_id'] !== null && (!is_numeric($filters['user_id']) || $filters['user_id'] <= 0)) {
            $errors['user_id'] = 'L\'ID utilisateur doit être un nombre entier positif';
        }

        if ($filters['jpo_id'] !== null && (!is_numeric($filters['jpo_id']) || $filters['jpo_id'] <= 0)) {
            $errors['jpo_id'] = 'L\'ID JPO doit être un nombre entier positif';
        }

        if ($filters['status'] !== null && !in_array($filters['status'], ['registered', 'unregistered'])) {
            $errors['status'] = 'Le statut doit être "registered" ou "unregistered"';
        }

        if ($filters['user_type'] !== null && !in_array($filters['user_type'], ['student', 'parent', 'marketing_member'])) {
            $errors['user_type'] = 'Type d\'utilisateur invalide';
        }

        return $errors;
    }

    private function validateCreateData(array $data): array
    {
        $errors = [];

        if (!isset($data['user_id']) || !is_numeric($data['user_id']) || $data['user_id'] <= 0) {
            $errors['user_id'] = 'L\'ID utilisateur est requis et doit être un nombre entier positif';
        }

        if (!isset($data['jpo_id']) || !is_numeric($data['jpo_id']) || $data['jpo_id'] <= 0) {
            $errors['jpo_id'] = 'L\'ID JPO est requis et doit être un nombre entier positif';
        }

        return $errors;
    }

    private function formatRegistrationForList(array $registration): array
    {
        return [
            'id' => (int) $registration['registration_id'],
            'registration_date' => $registration['registration_date'],
            'status' => $registration['status'],
            'user' => [
                'id' => (int) $registration['user_id'],
                'first_name' => $registration['first_name'],
                'last_name' => $registration['last_name'],
                'email' => $registration['email'],
                'user_type' => $registration['user_type']
            ],
            'jpo' => [
                'id' => (int) $registration['jpo_id'],
                'name' => $registration['jpo_name'],
                'date' => $registration['jpo_date']
            ]
        ];
    }

    private function formatRegistrationForDetails(array $registration): array
    {
        $formatted = $this->formatRegistrationForList($registration);
        
        // Ajouter des informations supplémentaires si disponibles
        if (isset($registration['campus_name'])) {
            $formatted['jpo']['campus'] = [
                'id' => (int) $registration['campus_id'],
                'name' => $registration['campus_name'],
                'city' => $registration['campus_city']
            ];
        }

        return $formatted;
    }
}