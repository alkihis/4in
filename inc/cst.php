<?php

// Paramètres MySQL
const MYSQL_USER = 'projet';
const MYSQL_PASSWORD = 'psw';
const MYSQL_BASE = 'projet';
const MYSQL_SERVER = 'localhost';

// Nom textuel du site
const SITE_NAME = '4IN';
// Nom long du site
const SITE_LONG_NAME = 'Insect Innate Immunity Interactive database';

// Charge la configuration initiale:
// Construit la base SQL,
// le visiteur devient administrateur
// Après activation à true, allez sur {site.address.com}/initial
// pour débuter
const INITIAL_CONFIGURATION = false;

// ---------------------
// Niveaux de permission
// ---------------------

// Aucune permission, non connecté
const USER_PERM_UNLOGGED = -2;

// Permissions minimales; Pas de visiblité des espèces protégées
// Vérifiée avec la fonction isBasicUserLogged()
const USER_PERM_BASIC = -1; 

// Permissions minimales + visibilité des espèces protégées
// Vérifiée avec la fonction isUserLogged()
const USER_PERM_VISITOR = 0;

// Permissions moyennes : Visibilité des espèces protégées + ajout de gène/orthologues autorisé
// Vérifiée avec la fonction isContributorLogged()
const USER_PERM_CONTRIBUTOR = 1;

// Permissions administrateur : Espèces protégées + ajout de gène/orthologues + accès console d'administration
// Vérifiée avec la fonction isAdminLogged()
const USER_PERM_ADMINISTRATOR = 2;

// Les fonctions de permissions sont avec "fallback" : si un admin est connecté, 
// isBasicUserLogged(), isUserLogged(), isContributorLogged() et isAdminLogged() retourneront toutes vrai.
// Pour tester les droits réels d'un utilisateur, utilisez $_SESSION['user']['rights'] === USER_PERM_*** 
// Pour créer un utilisateur, toujours utiliser les constantes ci-dessus.
// ---------------------

// ------------------------
// Limitations de recherche
// ------------------------
// Limiter les résultats de recherche
const LIMIT_SEARCH_RESULTS = true;

// Nombre de résultats maximum en limitant
const LIMIT_SEARCH_NUMBER = 2000;

// Niveau pour lequel la recherche n'est plus limitée
const LIMIT_SEARCH_LEVEL = USER_PERM_VISITOR;
// ---------------------

// Emplacements (par rapport à DOCUMENT_ROOT)
// Les slashs de début et de fin sont obligatoires
const FASTA_ADN_DIR = '/assets/fasta/adn/';
const FASTA_PRO_DIR = '/assets/fasta/pro/';
const MAPPING_DIR = '/assets/mapping/';

// Active l'affichage des erreurs BLAST dans l'HTML (sinon, l'erreur est loggée)
// Active l'affichage des notices/warning à l'écran
// Active l'affichage du texte des Exceptions lors d'une rencontre
const DEBUG_MODE = true;

// Protected genomes : hide species defined in PROTECTED_SPECIES
const LIMIT_GENOMES = true;

// _----- PAGE DE CONTACT -----_
// Seuil (entre 0 et 1) pour lequel on admet qu'un utilisateur du ReCaptcha n'est pas un robot
const THRESHOLD_RECAPTCHA = 0.5;
// Temps d'attente entre deux messages (en secondes)
const TIME_BEFORE_NEW_MESSAGE = 120;
// Limite de caractères (ISO-8859-1) d'un message de contact
const MAX_LEN_MESSAGE = 5000;
// Limite de caractères (ISO-8859-1) d'un e-mail
const MAX_LEN_EMAIL = 70;
// _----- -----_

const REGEX_USERNAME = "/^[A-Za-z]{1}[A-Za-z0-9_-]{3,31}$/";

// Link save : Checker for link validity
// First %s goes for specie acronym, second one for gene_id (or alias, if exists)
const LINK_CHECKER = "http://bf2i200.insa-lyon.fr:4555/%s/NEW-IMAGE?type=GENE&object=%s";

// Link to external database. First one is used for "Protected species", second is used for other species
const LINK_PROTECTED_SPECIE = 'http://bf2i200.insa-lyon.fr:3555/%s/NEW-IMAGE?type=GENE&object=%s';
const LINK_GENERAL = 'http://arthropodacyc.cycadsys.org/%s/NEW-IMAGE?type=GENE&object=%s';
// const LINK_GENERAL = 'http://bf2i200.insa-lyon.fr:4555/%s/NEW-IMAGE?type=GENE&object=%s';

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
    'initial' => ['file' => 'pages/initial.php', 'view' => 'iView', 'controller' => 'iControl'],
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
