<?php

const MYSQL_USER = 'projet';
const MYSQL_PASSWORD = 'psw';
const MYSQL_BASE = 'projet';

// Définition des pages disponibles sur le site web
// nom_page => [
//      'file' => fichier_de_la_page, 
//      'view' => fonction_generation_vue, 
//      'controller' => fonction_generation_controller
// ]
// Intérêt, dans l'url, taper localhost/fullBdd accède à la page
// pages/readFile.php
const PAGES_REF = [
    'home' => ['file' => 'static/start.php', 'view' => 'homeView', 'controller' => 'homeControl'],
    'fullBdd' => ['file' => 'pages/readFile.php', 'view' => 'readFileView', 'controller' => 'readFileControl']
];
