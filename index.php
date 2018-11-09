<?php

// Active l'affichage des erreurs sur le site web quand PHP en rencontre une
ini_set('display_errors', 'on');

// Initialise la session (permet d'être connecté)
session_start();

require 'inc/cst.php';
require 'inc/func.php';
require 'inc/CustomExceptions.php';
require 'inc/Controller.php';
require 'inc/Logger.php';
require 'inc/Gene.php';

try {
    $GLOBALS['logger'] = new Logger();

    // Redirige les erreurs dans les fichiers de log
    set_error_handler('Logger::errorHandler', E_ALL);
} catch (Exception $e) {
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/assets/log/critical.log', 
        "Unable to open log file." . $e->getMessage() . ". Log date: " . date('Y-m-d') . "\n", 
        FILE_APPEND
    );

    // Empêche de lancer des fatal exceptions lors de l'appel de méthodes
    $GLOBALS['logger'] = new stdClass();
}

// Tente de connecter si un cookie est set
tryLogIn();

// Obtention du Controller pour afficher la page
// D'abord, on obtient l'url
$parms = getSelectedUrl();
$maintenance_mode = false;

// On vérifie si le site n'est pas en maintenance
if (SITE_MAINTENANCE && $parms[0] !== 'login' && !isUserLogged()) {
    $parms[0] = "503";
    $maintenance_mode = true;
}

// On obtient le contrôleur
$ctrl = getRoute(...$parms);

?>

<!DOCTYPE html>
<html>
    <head>
        <!--Import Google Icon Font-->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!--Import materialize.css-->
        <link type="text/css" rel="stylesheet" href="/css/materialize.min.css"  media="screen,projection"/>
        <link type="text/css" rel="stylesheet" href="/css/style.css"/>

        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <!-- favicon -->
        <link rel="icon" type="image/png" href="/img/favicon.png">

        <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>

        <title>
            <?= $ctrl->getTitle() ?? SITE_NAME ?>
        </title>
    </head>

    <body>
        <header>
        <?php if (!$maintenance_mode) { require 'static/nav.php'; } ?>
        </header>

        <main>
        <?php
        // On appelle la fonction de "vue" de la page chargée dans le Controller
        $ctrl();
        ?>
        </main>
        
        <?php if (!$maintenance_mode) { require 'static/footer.php'; } // footer.php contient déjà <footer></footer> ?>

        <!--JavaScript at end of body for optimized loading-->
        <script type="text/javascript" src="/js/materialize.min.js"></script>
        <script type="text/javascript" src="/js/script.js"></script>
    </body>
</html>
