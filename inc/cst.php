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
const PAGES_REF = [
    'home' => ['file' => 'static/start.php', 'view' => 'homeView', 'controller' => 'homeControl'],
    'fullBdd' => ['file' => 'php/readFile.php', 'view' => 'readFileView', 'controller' => 'readFileControl']
];
