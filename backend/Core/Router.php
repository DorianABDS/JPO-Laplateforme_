<?php

namespace JpoLaplateforme\Backend\Core;

use JpoLaplateforme\Backend\Core\Response;

class Router
{
    private array $routes = [];

    // Route GET
    public function get(string $path, string $controller): void
    {
        $this->addRoute('GET', $path, $controller);
    }

    // Route POST
    public function post(string $path, string $controller): void
    {
        $this->addRoute('POST', $path, $controller);
    }

    // Route PUT
    public function put(string $path, string $controller): void
    {
        $this->addRoute('PUT', $path, $controller);
    }

    // Route DELETE
    public function delete(string $path, string $controller): void
    {
        $this->addRoute('DELETE', $path, $controller);
    }

    // Ajoute une route avec méthode, chemin et contrôleur
    private function addRoute(string $method, string $path, string $controller): void
    {
        // Remplace les paramètres dynamiques par des regex
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller
        ];
    }

    // Cherche et exécute la route correspondante
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Nettoie l'URI
        $uri = $this->cleanUri($uri);

        // Gère les requêtes préflight (CORS)
        Response::handlePreflight();

        // Parcourt les routes pour trouver une correspondance
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $this->callController($route['controller'], $matches);
                return;
            }
        }

        // Si aucune route trouvée
        Response::error('Route non trouvée', 404);
    }

    // Supprime la base du chemin pour obtenir une URI propre
    private function cleanUri(string $uri): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        $basePath = dirname($basePath);

        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        return $uri;
    }

    // Appelle la méthode d’un contrôleur
    private function callController(string $controller, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $controller);
        $controllerClass = "JpoLaplateforme\\Backend\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            Response::error("Contrôleur {$controllerName} non trouvé", 500);
            return;
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $method)) {
            Response::error("Méthode {$method} non trouvée dans {$controllerName}", 500);
            return;
        }

        // Récupère les paramètres nommés (issus de l'URL)
        $namedParams = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);

        try {
            $controllerInstance->$method($namedParams);
        } catch (\Exception $e) {
            Response::error(
                $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Erreur interne du serveur',
                500
            );
        }
    }
}
