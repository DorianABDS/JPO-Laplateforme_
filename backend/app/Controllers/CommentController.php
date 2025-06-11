<?php

namespace JpoLaplateforme\Backend\Controllers;

use JpoLaplateforme\Backend\Core\Response;
use JpoLaplateforme\Backend\Models\Comment;
use JpoLaplateforme\Backend\Config\Database;
use Exception;

class CommentController
{
    private Comment $commentModel;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->connect();
        $this->commentModel = new Comment($pdo);
    }

    /**
     * Liste tous les commentaires
     * Route : GET /api/comments
     */
    public function index(): void
    {
        try {
            // Récupère les filtres depuis la requête GET
            $filters = [
                'user_id' => $_GET['user_id'] ?? null,
                'jpo_id' => $_GET['jpo_id'] ?? null,
                'has_reply' => $_GET['has_reply'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
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

            // Récupère les commentaires depuis le modèle
            $comments = $this->commentModel->getAll($filters);

            // Formate les données pour la réponse
            $formattedComments = array_map([$this, 'formatCommentForList'], $comments);

            // Envoie la réponse JSON
            Response::success([
                'comments' => $formattedComments,
                'count' => count($formattedComments),
                'filters_applied' => $filters
            ]);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération des commentaires',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Récupère un commentaire précis via son ID
     * Route : GET /api/comments/{id}
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
            $comment = $this->commentModel->getById($id);

            if (!$comment) {
                Response::error('Commentaire non trouvé', 404);
                return;
            }

            $formattedComment = $this->formatCommentForDetails($comment);
            Response::success($formattedComment);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la récupération du commentaire',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Crée un nouveau commentaire
     * Route : POST /api/comments
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

            // Crée le commentaire
            $commentId = $this->commentModel->create($input);
            
            // Récupère le commentaire créé
            $comment = $this->commentModel->getById($commentId);
            $formattedComment = $this->formatCommentForDetails($comment);

            Response::success($formattedComment, 201);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la création du commentaire',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Met à jour un commentaire
     * Route : PUT /api/comments/{id}
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

            // Vérifie que le commentaire existe
            if (!$this->commentModel->getById($id)) {
                Response::error('Commentaire non trouvé', 404);
                return;
            }

            // Met à jour le commentaire
            $this->commentModel->update($id, $input);
            
            // Récupère le commentaire mis à jour
            $comment = $this->commentModel->getById($id);
            $formattedComment = $this->formatCommentForDetails($comment);

            Response::success($formattedComment);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la mise à jour du commentaire',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Supprime un commentaire
     * Route : DELETE /api/comments/{id}
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

            // Vérifie que le commentaire existe
            if (!$this->commentModel->getById($id)) {
                Response::error('Commentaire non trouvé', 404);
                return;
            }

            // Supprime le commentaire
            $this->commentModel->delete($id);

            Response::success(['message' => 'Commentaire supprimé avec succès']);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de la suppression du commentaire',
                500,
                $_ENV['APP_DEBUG'] === 'true' ? ['debug' => $e->getMessage()] : null
            );
        }
    }

    /**
     * Ajoute une réponse de modérateur à un commentaire
     * Route : POST /api/comments/{id}/reply
     */
    public function addModeratorReply(array $params): void
    {
        try {
            $id = $params['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                Response::error('ID invalide', 400);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['moderator_reply']) || trim($input['moderator_reply']) === '') {
                Response::error('Réponse du modérateur requise', 400);
                return;
            }

            $id = (int) $id;

            // Vérifie que le commentaire existe
            if (!$this->commentModel->getById($id)) {
                Response::error('Commentaire non trouvé', 404);
                return;
            }

            // Ajoute la réponse du modérateur
            $this->commentModel->addModeratorReply($id, $input['moderator_reply']);
            
            // Récupère le commentaire mis à jour
            $comment = $this->commentModel->getById($id);
            $formattedComment = $this->formatCommentForDetails($comment);

            Response::success($formattedComment);

        } catch (Exception $e) {
            Response::error(
                'Erreur lors de l\'ajout de la réponse du modérateur',
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

        if ($filters['has_reply'] !== null && !in_array($filters['has_reply'], ['true', 'false', '1', '0'])) {
            $errors['has_reply'] = 'has_reply doit être true ou false';
        }

        if ($filters['search'] !== null && strlen(trim($filters['search'])) < 2) {
            $errors['search'] = 'La recherche doit contenir au moins 2 caractères';
        }

        if ($filters['date_from'] !== null && !$this->isValidDate($filters['date_from'])) {
            $errors['date_from'] = 'La date de début doit être au format YYYY-MM-DD';
        }

        if ($filters['date_to'] !== null && !$this->isValidDate($filters['date_to'])) {
            $errors['date_to'] = 'La date de fin doit être au format YYYY-MM-DD';
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

        if (!isset($data['content']) || trim($data['content']) === '') {
            $errors['content'] = 'Le contenu du commentaire est requis';
        }

        if (isset($data['content']) && strlen(trim($data['content'])) < 10) {
            $errors['content'] = 'Le commentaire doit contenir au moins 10 caractères';
        }

        return $errors;
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function formatCommentForList(array $comment): array
    {
        return [
            'id' => (int) $comment['comment_id'],
            'content' => $comment['content'],
            'comment_date' => $comment['comment_date'],
            'has_moderator_reply' => !empty($comment['moderator_reply']),
            'user' => [
                'id' => (int) $comment['user_id'],
                'first_name' => $comment['first_name'],
                'last_name' => $comment['last_name'],
                'user_type' => $comment['user_type']
            ],
            'jpo' => [
                'id' => (int) $comment['jpo_id'],
                'name' => $comment['jpo_name'],
                'date' => $comment['jpo_date']
            ]
        ];
    }

    private function formatCommentForDetails(array $comment): array
    {
        $formatted = $this->formatCommentForList($comment);
        
        // Ajouter la réponse du modérateur si elle existe
        if (!empty($comment['moderator_reply'])) {
            $formatted['moderator_reply'] = [
                'content' => $comment['moderator_reply'],
                'reply_date' => $comment['reply_date']
            ];
        }

        // Ajouter des informations supplémentaires sur la JPO si disponibles
        if (isset($comment['campus_name'])) {
            $formatted['jpo']['campus'] = [
                'id' => (int) $comment['campus_id'],
                'name' => $comment['campus_name'],
                'city' => $comment['campus_city']
            ];
        }

        return $formatted;
    }
}