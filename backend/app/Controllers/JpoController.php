<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\Jpo;
use JpoLaplateforme\Backend\Config\Database;
use Exception;

class JpoController
{
    private Jpo $jpoModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->jpoModel = new Jpo($pdo);
    }

    /**
     * Récupère la liste de toutes les JPO
     * Route: GET /api/jpo
     */
    public function index(): void
    {
        try {
            $filters = [
                'campus_id' => $_GET['campus_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'search' => $_GET['search'] ?? null,
            ];

            $validationErrors = $this->validateListFilters($filters);
            if (!empty($validationErrors)) {
                Response::error('Paramètres invalides', 400, $validationErrors);
                return;
            }

            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });

            $jpos = $this->jpoModel->getAll($filters);
            $formattedJpos = array_map([$this, 'formatJpoForList'], $jpos);

            Response::success([
                'jpos' => $formattedJpos,
                'count' => count($formattedJpos),
                'filters_applied' => $filters
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des JPO',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère les détails d'une JPO par son ID
     * Route: GET /api/jpo/{id}
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
            $jpo = $this->jpoModel->getById($id);

            if (!$jpo) {
                Response::error('JPO non trouvée', 404);
                return;
            }

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

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

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

    private function formatJpoForDetails(array $jpo): array
    {
        $formatted = $this->formatJpoForList($jpo);
        
        $formatted['statistics'] = [
            'registered_count' => (int) $jpo['registered_count'],
            'comments_count' => (int) $jpo['comments_count'],
            'capacity_percentage' => $jpo['max_capacity'] > 0 
                ? round(((int) $jpo['registered_count'] / (int) $jpo['max_capacity']) * 100, 2)
                : 0
        ];

        $formatted['comments'] = array_map(function($comment) {
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