<?php

// Active l'affichage des erreurs sur le site web quand PHP en rencontre une
ini_set('display_errors', 'on');

// Initialise la session (inutilisé actuellement)
// session_start();

require 'inc/cst.php';
require 'inc/func.php';
require 'inc/Controller.php';

// Obtention du Controller pour afficher la page
$ctrl = getRoute();

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

        <title>
            <?= $ctrl->getTitle() ?? SITE_NAME ?>
        </title>
    </head>

    <body>
        <header>
        <?php require 'static/nav.php'; ?>
        </header>

        <main>
        <?php
        // On appelle la fonction de "vue" de la page chargée dans le Controller
        $ctrl();
        ?>
        </main>
        
        <?php require 'static/footer.php'; // footer.php contient déjà <footer></footer> ?>

        <!--JavaScript at end of body for optimized loading-->
        <script type="text/javascript" src="/js/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/js/materialize.min.js"></script>
        <script type="text/javascript" src="/js/script.js"></script>
    </body>
</html>
