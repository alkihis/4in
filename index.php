<?php

// Active l'affichage des erreurs sur le site web quand PHP en rencontre une
ini_set('display_errors', 'on');

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
        <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
        <link type="text/css" rel="stylesheet" href="css/style.css"/>

        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <title>
            <?= $ctrl->getTitle() ?? 'NC3I' ?>
        </title>
    </head>

    <body>
        <?php require 'static/nav.html' ?>
        
        <?php
        // $page_name = 'static/start.php';
        // if (isset($_GET['page']) && is_string($_GET['page']) && $_GET['page'] !== '') {
        //     if(file_exists(addslashes('pages/' . $_GET['page']))) // le fichier existe
        //         $page_name = addslashes('pages/' . $_GET['page']);
        // }

        // include $page_name;

        // var_dump($_SERVER);

        // On appelle la fonction de "vue" de la page chargée dans le Controller
        $ctrl();

        require 'static/footer.php';

        ?>

        <!--JavaScript at end of body for optimized loading-->
        <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="js/materialize.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
    </body>
</html>
