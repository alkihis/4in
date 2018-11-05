<?php

const MYSQL_USER = 'projet';
const MYSQL_PASSWORD = 'psw';
const MYSQL_BASE = 'projet';

const SITE_NAME = 'NC3I';
const DEBUG_MODE = true;

// Protected genomes : hide species defined in PROTECTED_SPECIES
const LIMIT_GENOMES = true;

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
    'login' => ['file' => 'pages/login.php', 'view' => 'loginView', 'controller' => 'loginControl'],
    'search' => ['file' => 'pages/search.php', 'view' => 'searchView', 'controller' => 'searchControl'],
    'blast' => ['file' => 'pages/blast.php', 'view' => 'blastView', 'controller' => 'blastControl'],
    'blast_search' => ['file' => 'pages/blast_search.php', 'view' => 'searchBlastView', 'controller' => 'searchBlastControl'],
    'help' => ['file' => 'pages/help.php', 'view' => 'helpView', 'controller' => 'helpControl'],
    'gene' => ['file' => 'pages/gene.php', 'view' => 'geneView', 'controller' => 'geneControl'],
    'admin' => ['file' => 'pages/admin.php', 'view' => 'adminView', 'controller' => 'adminControl'],
    'team' => ['file' => 'pages/team.php', 'view' => 'teamView', 'controller' => 'teamControl'],
    'contact' => ['file' => 'pages/contact.php', 'view' => 'contactView', 'controller' => 'contactControl'],
    '404' => ['file' => 'static/404.php', 'view' => 'notFoundView', 'controller' => 'notFoundControl'],
    '403' => ['file' => 'static/403.php', 'view' => 'forbiddenView', 'controller' => 'forbiddenControl'],
    '500' => ['file' => 'static/500.php', 'view' => 'serverErrorView', 'controller' => 'serverErrorControl'],
    '501' => ['file' => 'static/501.php', 'view' => 'serverImplementView', 'controller' => 'serverImplementControl'],
    '503' => ['file' => 'static/503.php', 'view' => 'maintenanceView', 'controller' => 'maintenanceControl'],
];

// NEW NOTE: Protected species are now loaded through a independant PHP file.
// Note that value of specie is useless : Associative array is used like a set container here
// Protected specie must be defined as key of the array

define('PARAMETERS_FILE', $_SERVER['DOCUMENT_ROOT'] . '/assets/db/site_parameters.json');

function loadSiteParameters() : void {
    $parameters = json_decode(file_get_contents(PARAMETERS_FILE), true);

    // Possibilité de définir des tableaux depuis PHP 7.0
    $pro = [];
    foreach ($parameters['protected'] as $p) {
        $pro[$p] = true;
    }

    define('PROTECTED_SPECIES', $pro);
    define('SPECIE_TO_NAME', $parameters['species']);
    define('SITE_PARAMETERS_ARRAY', $parameters);
    define('ORDERED_SPECIES', $parameters['species_ordered']);
    define('SITE_MAINTENANCE', !$parameters['accessible']);
}

loadSiteParameters();
