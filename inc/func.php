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
            // Si la page demandée n'existe pas
            $page_name = 'home';
        }
    }

    // Charge le fichier demandé
    require PAGES_REF[$page_name]['file'];
    // Récupère le nom de la fonction servant à charger la vue
    $view = PAGES_REF[$page_name]['view'];
    // Appelle la fonction servant à initialiser le Controller
    // et le stocke dans $ctrl 
    // (on peut appeler des variables qui sont une chaîne de caractères nommant une fonction en PHP, cherchez pas)
    $ctrl = PAGES_REF[$page_name]['controller']();

    // Enregistre la fonction de vue dans le contrôleur
    $ctrl->setViewFunction($view);

    // Retourne le contrôleur
    return $ctrl;
}

connectBD();
