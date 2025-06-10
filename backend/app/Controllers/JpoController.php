<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\Jpo;
use JpoLaplateforme\Backend\Config\database;
use Exception;

class JpoController
{
    private Jpo $jpoModel;

    // Initialise le modèle Jpo avec la connexion PDO
    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->jpoModel = new Jpo($pdo);
    }

    /**
     * Liste toutes les JPO, avec filtres possibles
     * Route : GET /api/jpo
     */
    public function index(): void
    {
        try {
            // Récupère les filtres depuis la requête GET
            $filters = [
                'campus_id' => $_GET['campus_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'search' => $_GET['search'] ?? null,
            ];

            // Valide les filtres reçus
            $validationErrors = $this->validateListFilters($filters);
            if (!empty($validationErrors)) {
                Response::error('Paramètres invalides', 400, $validationErrors);
                return;
            }

            // Supprime les filtres vides pour éviter d'envoyer des valeurs nulles
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // Récupère les JPO depuis le modèle
            $jpos = $this->jpoModel->getAll($filters);

            // Formate les données pour la réponse
            $formattedJpos = array_map([$this, 'formatJpoForList'], $jpos);

            // Envoie la réponse JSON avec les données formatées
            Response::success([
                'jpos' => $formattedJpos,
                'count' => count($formattedJpos),
                'filters_applied' => $filters
            ]);
        } catch (Exception $e) {
            // En cas d'erreur, envoie une erreur générique (avec debug si activé)
            Response::error(
                'Erreur lors de la récupération des JPO',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère une JPO précise via son ID
     * Route : GET /api/jpo/{id}
     */
    public function show(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            // Vérifie que l'ID est valide (existe et est un nombre)
            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $id = (int) $id;
            $jpo = $this->jpoModel->getById($id);

            // Si la JPO n'existe pas, envoie une 404
            if (!$jpo) {
                Response::error('JPO non trouvée', 404);
                return;
            }

            // Formate la JPO pour la réponse
            $formattedJpo = $this->formatJpoForDetails($jpo);
            Response::success($formattedJpo);
        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération de la JPO',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    // Valide les filtres reçus sur la liste des JPO
    private function validateListFilters(array $filters): array
    {
        $errors = [];

        if ($filters['campus_id'] !== null && (!is_numeric($filters['campus_id']) || $filters['campus_id'] <= 0)) {
            $errors['campus_id'] = 'L\'ID du campus doit être un nombre entier positif';
        }

        if ($filters['date_from'] !== null && !$this->isValidDate($filters['date_from'])) {
            $errors['date_from'] = 'La date de début doit être au format YYYY-MM-DD';
        }

        if ($filters['date_to'] !== null && !$this->isValidDate($filters['date_to'])) {
            $errors['date_to'] = 'La date de fin doit être au format YYYY-MM-DD';
        }

        if ($filters['date_from'] && $filters['date_to'] && $filters['date_from'] > $filters['date_to']) {
            $errors['dates'] = 'La date de début doit être antérieure à la date de fin';
        }

        if ($filters['search'] !== null && strlen(trim($filters['search'])) < 2) {
            $errors['search'] = 'La recherche doit contenir au moins 2 caractères';
        }

        return $errors;
    }

    // Vérifie qu'une chaîne correspond bien à une date YYYY-MM-DD valide
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    // Formate les données d'une JPO pour la liste
    private function formatJpoForList(array $jpo): array
    {
        return [
            'id' => (int) $jpo['jpo_id'],
            'name' => $jpo['name'],
            'date' => $jpo['date'],
            'campus' => [
                'id' => (int) $jpo['campus_id'],
                'name' => $jpo['campus_name'],
                'city' => $jpo['campus_city']
            ],
            'capacity' => [
                'max' => (int) $jpo['max_capacity'],
                'registered' => (int) $jpo['registered_count'],
                'available' => (int) $jpo['max_capacity'] - (int) $jpo['registered_count']
            ],
            'is_full' => (int) $jpo['registered_count'] >= (int) $jpo['max_capacity'],
            'is_past' => strtotime($jpo['date']) < strtotime('today')
        ];
    }

    // Formate les données d'une JPO détaillée pour la vue complète
    private function formatJpoForDetails(array $jpo): array
    {
        $formatted = $this->formatJpoForList($jpo);

        // Ajoute les statistiques supplémentaires
        $formatted['statistics'] = [
            'registered_count' => (int) $jpo['registered_count'],
            'comments_count' => (int) $jpo['comments_count'],
            'capacity_percentage' => $jpo['max_capacity'] > 0
                ? round(((int) $jpo['registered_count'] / (int) $jpo['max_capacity']) * 100, 2)
                : 0
        ];

        // Ajoute les commentaires avec leur auteur et réponse modérateur si existante
        $formatted['comments'] = array_map(function ($comment) {
            return [
                'id' => (int) $comment['comment_id'],
                'content' => $comment['content'],
                'date' => $comment['comment_date'],
                'author' => [
                    'name' => $comment['first_name'] . ' ' . $comment['last_name'],
                    'type' => $comment['user_type']
                ],
                'moderator_reply' => $comment['moderator_reply'] ? [
                    'content' => $comment['moderator_reply'],
                    'date' => $comment['reply_date']
                ] : null
            ];
        }, $jpo['comments']);

        return $formatted;
    }
}
