<?php

namespace JpoLaplateforme\Backend\Core;

use JpoLaplateforme\Backend\Core\Response;

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller): void
    {
        $this->addRoute('GET', $path, $controller);
    }

    public function post(string $path, string $controller): void
    {
        $this->addRoute('POST', $path, $controller);
    }

    public function put(string $path, string $controller): void
    {
        $this->addRoute('PUT', $path, $controller);
    }

    public function delete(string $path, string $controller): void
    {
        $this->addRoute('DELETE', $path, $controller);
    }

    private function addRoute(string $method, string $path, string $controller): void
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // ✅ CORRECTION : Nettoyer l'URI pour extraire seulement la partie API
        $uri = $this->cleanUri($uri);

        Response::handlePreflight();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                $this->callController($route['controller'], $matches);
                return;
            }
        }

        Response::error('Route non trouvée', 404);
    }

    /**
     * Nettoie l'URI pour extraire seulement la partie relative à l'API
     */
    private function cleanUri(string $uri): string
    {
        // Récupérer le chemin du script pour déterminer la base
        $scriptName = $_SERVER['SCRIPT_NAME']; // Ex: /JPO-Laplateforme_/Backend/public/index.php
        $basePath = dirname($scriptName); // Ex: /JPO-Laplateforme_/Backend/public
        
        // Retirer /public de la base pour avoir : /JPO-Laplateforme_/Backend
        $basePath = dirname($basePath); // Ex: /JPO-Laplateforme_/Backend
        
        // Si l'URI commence par cette base, la retirer
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // S'assurer que l'URI commence par /
        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }
        
        return $uri;
    }

    private function callController(string $controller, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $controller);
        
        // ✅ Namespace corrigé avec Backend en majuscule et Controllers
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