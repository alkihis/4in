<?php

// Paramètres MySQL
const MYSQL_USER = 'projet';
const MYSQL_PASSWORD = 'psw';
const MYSQL_BASE = 'projet';
const MYSQL_SERVER = 'localhost';

// Nom textuel du site
const SITE_NAME = '4IN';

// Emplacements (par rapport à DOCUMENT_ROOT)
const FASTA_ADN_DIR = '/assets/fasta/adn/';
const FASTA_PRO_DIR = '/assets/fasta/pro/';
const MAPPING_DIR = '/assets/mapping/';

// Active l'affichage des erreurs BLAST dans l'HTML (sinon, l'erreur est loggée)
// Active l'affichage des notices/warning à l'écran
// Active l'affichage du texte des Exceptions lors d'une rencontre
const DEBUG_MODE = false;

// Protected genomes : hide species defined in PROTECTED_SPECIES
const LIMIT_GENOMES = true;

// Link save : Checker for link validity
// First %s goes for specie acronym, second one for gene_id (or alias, if exists)
const LINK_CHECKER = "http://bf2i200.insa-lyon.fr:4555/%s/NEW-IMAGE?type=GENE&object=%s";

// Link to external database. First one is used for "Protected species", second is used for other species
const LINK_PROTECTED_SPECIE = 'http://bf2i200.insa-lyon.fr:3555/%s/NEW-IMAGE?type=GENE&object=%s';
// const LINK_GENERAL = 'http://arthropodacyc.cycadsys.org/%s/NEW-IMAGE?type=GENE&object=%s'; // < Does not work ?
const LINK_GENERAL = 'http://bf2i200.insa-lyon.fr:4555/%s/NEW-IMAGE?type=GENE&object=%s';

// Définition des pages disponibles sur le site web
// nom_page => [
//      'file' => fichier_de_la_page, 
//      'view' => fonction_generation_vue, 
//      'controller' => fonction_generation_controller
// ]
const PAGES_REF = [
    'home' => ['file' => 'static/start.php', 'view' => 'homeView', 'controller' => 'homeControl'],
    'about' => ['file' => 'pages/about.php', 'view' => 'aboutView', 'controller' => 'aboutControl'],
    'login' => ['file' => 'pages/login.php', 'view' => 'loginView', 'controller' => 'loginControl'],
    'search' => ['file' => 'pages/search.php', 'view' => 'searchView', 'controller' => 'searchControl'],
    'blast_search' => ['file' => 'pages/blast_search.php', 'view' => 'searchBlastView', 'controller' => 'searchBlastControl'],
    'help' => ['file' => 'pages/help.php', 'view' => 'helpView', 'controller' => 'helpControl'],
    'gene' => ['file' => 'pages/gene.php', 'view' => 'geneView', 'controller' => 'geneControl'],
    'modify' => ['file' => 'pages/modify.php', 'view' => 'modifyView', 'controller' => 'modifyControl'],
    'add' => ['file' => 'pages/add.php', 'view' => 'addView', 'controller' => 'addControl'],
    'add_o' => ['file' => 'pages/add_ortho.php', 'view' => 'addOView', 'controller' => 'addOControl'],
    'admin' => ['file' => 'pages/admin.php', 'view' => 'adminView', 'controller' => 'adminControl'],
    'contact' => ['file' => 'pages/contact.php', 'view' => 'contactView', 'controller' => 'contactControl'],
    '404' => ['file' => 'static/404.php', 'view' => 'notFoundView', 'controller' => 'notFoundControl'],
    '403' => ['file' => 'static/403.php', 'view' => 'forbiddenView', 'controller' => 'forbiddenControl'],
    '500' => ['file' => 'static/500.php', 'view' => 'serverErrorView', 'controller' => 'serverErrorControl'],
    '501' => ['file' => 'static/501.php', 'view' => 'serverImplementView', 'controller' => 'serverImplementControl'],
    '503' => ['file' => 'static/503.php', 'view' => 'maintenanceView', 'controller' => 'maintenanceControl'],
];

// DO NOT TOUCH
// THE FOLLOWING
// CODE !
//
// DEFINING CONSTANTS FROM JSON FILE, THIS PARAMETERS SHOULD BE
// MODIFIED THROUGH THE ADMIN CONSOLE INTERFACE ONLY.

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
