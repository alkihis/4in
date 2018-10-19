<?php

const MYSQL_USER = 'projet';
const MYSQL_PASSWORD = 'psw';
const MYSQL_BASE = 'projet';

const SITE_NAME = 'NC3I';
const DEBUG_MODE = true;

// Définition des pages disponibles sur le site web
// nom_page => [
//      'file' => fichier_de_la_page, 
//      'view' => fonction_generation_vue, 
//      'controller' => fonction_generation_controller
// ]
// Intérêt, dans l'url, taper localhost/full_database accède à la page
// pages/readFile.php
const PAGES_REF = [
    'home' => ['file' => 'static/start.php', 'view' => 'homeView', 'controller' => 'homeControl'],
    'full_database' => ['file' => 'pages/readFile.php', 'view' => 'readFileView', 'controller' => 'readFileControl'],
    'login' => ['file' => 'pages/login.php', 'view' => 'loginView', 'controller' => 'loginControl'],
    'search' => ['file' => 'pages/search.php', 'view' => 'searchView', 'controller' => 'searchControl'],
    'blast' => ['file' => 'pages/blast.php', 'view' => 'blastView', 'controller' => 'blastControl'],
    'help' => ['file' => 'pages/help.php', 'view' => 'helpView', 'controller' => 'helpControl'],
    'gene' => ['file' => 'pages/gene.php', 'view' => 'geneView', 'controller' => 'geneControl'],
    'import_fasta' => ['file' => 'pages/readFasta.php', 'view' => 'readFastaView', 'controller' => 'readFastaControl'],
    '404' => ['file' => 'static/404.php', 'view' => 'notFoundView', 'controller' => 'notFoundControl'],
    '403' => ['file' => 'static/403.php', 'view' => 'forbiddenView', 'controller' => 'forbiddenControl'],
    '500' => ['file' => 'static/500.php', 'view' => 'serverErrorView', 'controller' => 'serverErrorControl'],
    '501' => ['file' => 'static/501.php', 'view' => 'serverImplementView', 'controller' => 'serverImplementControl'],
];
