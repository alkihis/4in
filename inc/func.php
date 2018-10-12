<?php

$sql = null;

function connectBD() : void {
    global $sql;
    $sql = mysqli_connect('localhost', MYSQL_USER, MYSQL_PASSWORD, MYSQL_BASE);
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
    }
    else 
        mysqli_query($sql, 'SET NAMES UTF8mb4'); // requete pour avoir les noms en UTF8mb4
}

function getRoute() : Controller {
    // Get Controller object for asked page, Controller for home page if page undefined otherwise
    $page_name = 'home';

    if (isset($_SERVER['REDIRECT_URL'])) { 
        // Redirection par Apache, stockée dans cette variable
        // Possible par le .htaccess
        $page_name = explode('/', $_SERVER['REDIRECT_URL'])[1];

        if (! isset(PAGES_REF[$page_name])) {
            $page_name = 'home';
        }
    }

    require PAGES_REF[$page_name]['file'];
    $view = PAGES_REF[$page_name]['view'];
    $ctrl = PAGES_REF[$page_name]['controller']();

    $ctrl->setViewFunction($view);

    return $ctrl;
}
