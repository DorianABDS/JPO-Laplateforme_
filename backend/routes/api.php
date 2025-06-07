<?php

// ✅ Namespace corrigé avec Backend en majuscule
use JpoLaplateforme\Backend\Core\Router;

$router = new Router();

// Route de ping pour tester la connexion
$router->get('/api/ping', 'ApiController@ping');

// Routes des JPO
$router->get('/api/jpo', 'JpoController@index');
$router->get('/api/jpo/{id}', 'JpoController@show');

// Traitement de la requête
$router->dispatch();