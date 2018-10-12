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
    $page_arguments = [];

    if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] !== '/') { 
        // Si la requête est définie et que on ne vise pas la racine (page d'accueil)

        // REQUEST_URI contient la query string GET, on l'enlève
        $request_without_query_string = explode('?', $_SERVER['REQUEST_URI'])[0];

        // Redirection par Apache, stockée dans cette variable
        // Possible par le .htaccess
        $page_arguments = explode('/', $request_without_query_string);
        $page_name = $page_arguments[1];

        // Récupère les arguments après la page
        // Équivaut à $page_arguments[2:] en Python
        $page_arguments = array_slice($page_arguments, 2);

        if (!isset(PAGES_REF[$page_name])) { 
            // Si la page demandée n'existe pas
            $page_name = '404';
        }
    }

    // Charge le fichier demandé
    require_once PAGES_REF[$page_name]['file'];
    // Récupère le nom de la fonction servant à charger la vue
    $view = PAGES_REF[$page_name]['view'];

    $error = null;

    // Appelle la fonction servant à initialiser le Controller
    // et le stocke dans $ctrl 
    // (on peut appeler des variables qui sont une chaîne de caractères nommant une fonction en PHP, cherchez pas)
    try {
        // Tente d'inclure l'original. Si il lance une exception, elle est attrapée en dessous et appelle
        // les pages adéquates
        $ctrl = PAGES_REF[$page_name]['controller']($page_arguments);
    } 
    catch (ForbiddenPageException $f) {
        $error = ['403', $f];
    } 
    catch (PageNotFoundException $n) {
        $error = ['404', $n];
    } 
    catch (Throwable $e) { // Toute autre exception
        $error = ['500', $e];
    }

    if ($error) {
        $code = $error[0]; $ex = $error[1];
        require_once PAGES_REF[$code]['file'];
        $ctrl = PAGES_REF[$code]['controller']($ex);
        $view = PAGES_REF[$code]['view']; 
    }

    // Enregistre la fonction de vue dans le contrôleur
    $ctrl->setViewFunction($view);

    // Retourne le contrôleur
    return $ctrl;
}

connectBD();
