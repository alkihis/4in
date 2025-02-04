<?php

/**
 * 4IN (Interactive database for Innate Immunity of INsect)
 * October-december 2018
 * version 1.00~r1-linux
 * 
 * PHP 7.1+, 7.3 ready
 * needs Apache2 server, mod_rewrite, mod_headers, .htaccess file enabled
 * 
 * Developed by
 * Louis BERANGER       <louis.beranger@etu.univ-lyon1.fr>
 * Waël ZAMOUM          <wael.zamoum@etu.univ-lyon1.fr>
 * Aymeric BONNAMOUR    <aymeric.bonnamour@etu.univ-lyon1.fr>
 * 
 */

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

// Démarre le logging dans les fichiers texte
initErrorLogging();

// Init night mode
initNightMode();

// Tente de connecter si un cookie est set
tryLogIn();

// Obtention du Controller pour afficher la page
// D'abord, on obtient l'url
$parms = getSelectedUrl();
$maintenance_mode = false;

// On vérifie si le site n'est pas en maintenance
if (SITE_MAINTENANCE && $parms[0] !== 'login' && !isAdminLogged()) {
    $parms[0] = "503";
    $maintenance_mode = true;
}

// On obtient le contrôleur
$ctrl = getRoute(...$parms);

?><!DOCTYPE html>
<html lang="en">
    <head>
        <!-- 
            Developed by
            * Louis BERANGER
            * Waël ZAMOUM
            * Aymeric BONNAMOUR
            students at Claude Bernard Lyon 1 University

            October 2018
        -->

        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link type="text/css" rel="stylesheet" href="/css/materialize.min.css"  media="screen"/>
        <link type="text/css" rel="stylesheet" href="/css/style.css"/>
        <?= ($GLOBALS['night_mode'] ? '<link type="text/css" rel="stylesheet" id="dark-mode-css" href="/css/dark.css"/>' : '') ?>

        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <!-- favicon -->
        <link rel="icon" type="image/png" href="/img/favicon.png">

        <script src="/js/jquery-3.3.1.min.js"></script>

        <title>
            <?= ($ctrl->getTitle() ? $ctrl->getTitle() . ' - ' : '') . SITE_NAME ?>
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
        <script src="/js/materialize.min.js"></script>
        <script src="/js/script.js"></script>
        <script src="/js/jarallax.min.js"></script>
    </body>
</html>
