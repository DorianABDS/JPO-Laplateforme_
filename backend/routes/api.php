<?php

use JpoLaplateforme\Backend\Core\Router;

// Initialisation du routeur
$router = new Router();

// Routes API
$router->get('/api/ping', 'ApiController@ping');        // Test de connexion
$router->get('/api/jpo', 'JpoController@index');         // Liste des JPO
$router->get('/api/jpo/{id}', 'JpoController@show');     // Détail d'une JPO

// Lancement du dispatching des routes
$router->dispatch();
