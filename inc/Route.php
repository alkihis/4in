<?php

// Définition des pages disponibles sur le site web
// nom_page => [
//      'file' => fichier_de_la_page,
//      'view' => fonction_generation_vue,
//      'controller' => fonction_generation_controller
// ]
// Intérêt, dans l'url, taper localhost/full_database accède à la page
// pages/readFile.php

class Route {
    static protected $routes = [
        'home' => ['file' => 'static/start.php', 'view' => 'homeView', 'controller' => 'homeControl'],
        'full_database' => ['file' => 'pages/readFile.php', 'view' => 'readFileView', 'controller' => 'readFileControl'],
        'login' => ['file' => 'pages/login.php', 'view' => 'loginView', 'controller' => 'loginControl'],
        'search' => ['file' => 'pages/search.php', 'view' => 'searchView', 'controller' => 'searchControl'],
        '404' => ['file' => 'static/404.php', 'view' => 'notFoundView', 'controller' => 'notFoundControl'],
        '403' => ['file' => 'static/403.php', 'view' => 'forbiddenView', 'controller' => 'forbiddenControl'],
        '500' => ['file' => 'static/500.php', 'view' => 'serverErrorView', 'controller' => 'serverErrorControl'],
    ];

    public function routeExists(string $name) : bool {
        return isset(self::$routes[$name]);
    }

    public function getRoute(string $name) : ?array {
        return ($this->routeExists($name) ? self::$routes[$name] : null);
    }
}
