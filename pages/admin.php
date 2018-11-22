<?php

require 'pages/adm/password.php';
require 'pages/adm/reset_link.php';
require 'pages/adm/build.php';
require 'pages/adm/import.php';
require 'pages/adm/species.php';
require 'pages/adm/db_species.php';
require 'pages/adm/alias.php';
require 'pages/adm/fasta_converter.php';
require 'pages/adm/fasta_checker.php';
require 'pages/adm/stats.php';

function adminControl(array $args) : Controller {
    if (!isAdminLogged()) {
        throw new ForbiddenPageException();
    }

    if (!isset($args[0]) || empty($args[0])) {
        $page = homePageController();
    }
    else if ($args[0] === 'import') {
        if (!isset($args[1])) {
            throw new PageNotFoundException();
        }
        else if ($args[1] === 'genome') {
            $page = importGenomeController();
        }
        else if ($args[1] === 'blast') {
            $page = importBlastController();
        }
        else {
            throw new PageNotFoundException();
        }
    }
    else if ($args[0] === 'build') {
        if (!isset($args[1])) {
            throw new PageNotFoundException();
        }
        else if ($args[1] === 'genome') {
            $page = buildGenomeController();
        }
        else if ($args[1] === 'blast') {
            $page = buildBlastController();
        }
        else {
            throw new PageNotFoundException();
        }
    }
    else if ($args[0] === 'alias') {
        if (!isset($args[1])) {
            throw new PageNotFoundException();
        }
        else if ($args[1] === 'import') {
            $page = importAliasController();
        }
        else if ($args[1] === 'build') {
            $page = buildAliasController();
        }
        else {
            throw new PageNotFoundException();
        }
    }
    else if ($args[0] === 'reset_link') {
        $page = resetLinkController();
    }
    else if ($args[0] === 'password') {
        $page = passwordController();
    }
    else if ($args[0] === 'species') {
        $page = speciesController();
    }
    else if ($args[0] === 'db_species') {
        $page = databaseSpeciesController();
    }
    else if ($args[0] === 'converter') {
        $page = converterController();
    }
    else if ($args[0] === 'checker') {
        $page = checkerController();
    }
    else if ($args[0] === 'stats') {
        $page = statsController();
    }
    else {
        throw new PageNotFoundException();
    }

    return new Controller($page, 'Admin console');
}

function homePageController() : array {
    return ['active_page' => 'home', 'accessible' => !SITE_MAINTENANCE];
}

function adminView(Controller $c) : void {
    $d = $c->getData();

    ?>
    <!-- Modal for confirmation -->
    <div class="row no-margin-bottom">
        <!-- Modal Structure -->
        <div id="modal_build" class="modal bottom-sheet">
            <div class="modal-content">
                <h4 id="build_header">Build database from selected file ?</h4>
                <p id="build_text">
                    Building will clear current database, then try to import selected file.
                </p>
            </div>
            <div class="modal-footer">
                <a href="#!" class="waves-effect red-text btn-flat modal-close">
                    Cancel
                </a>
                <a href="#!" class="waves-effect green-text btn-flat modal-close" id="setter_builder">
                    Build
                </a>
            </div>
        </div>
    </div>

    <!-- Modal for clear -->
    <div class="row no-margin-bottom">
        <!-- Modal Structure -->
        <div id="modal_wipe" class="modal bottom-sheet">
            <div class="modal-content">
                <h4 id="wipe_header">Wipe genome database ?</h4>
                <p id="wipe_text">
                    All data in database will be lost and you will need to import a tabulated file again to
                    restore informations.
                </p>
            </div>
            <div class="modal-footer">
                <form method="post" action="#">
                    <div id="wipe_additionnal"></div>
                    <a href="#!" class="waves-effect blue-text btn-flat modal-close">
                        Cancel
                    </a>
                
                    <input type="hidden" name="erase" value="true">
                    <a href="#!" onclick="this.parentElement.submit()" 
                        class="waves-effect red-text btn-flat modal-close" id="setter_wiper">
                        Wipe
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="row no-margin-bottom">
        <div class="modal not-dismissible" id="modal-admin"></div>
    </div>

    <div class="linear-nav-to-white tiny-top-float"></div>
    <div class="container">
        <div class="row no-margin-bottom">
            <div class="col s12">
                <!-- Button for sidenav show in mobile -->
                <a href="#" data-target="slide-out-admin" class="sidenav-trigger hide-on-large-only btn btn-personal center-block blue-grey"
                    style="width: 90%; margin-top: 20px;">
                    <i class="material-icons sub">menu</i> Menu
                </a>
            </div>
        </div>

        <?php
        // Traitement spécifique
        switch($d['active_page']) {
            case 'home':
                homePageView($d);
                break;
            case 'build_genome':
                buildGenomeView($d);
                break;
            case 'build_blast':
                buildBlastView($d);
                break;
            case 'import_genome':
                importGenomeView($d);
                break;
            case 'import_blast':
                importBlastView($d);
                break;
            case 'alias_import':
                aliasImportView($d);
                break;
            case 'alias_build':
                aliasBuildView($d);
                break;
            case 'reset':
                resetLinkView($d);
                break;
            case 'db_species':
                databaseSpeciesView($d);
                break;
            case 'species':
                speciesView($d);
                break;
            case 'password':
                passwordView($d);
                break;
            case 'converter':
                converterView($d);
                break;
            case 'checker':
                checkerView($d);
                break;
            case 'stats':
                statsView($d);
                break;
        }
        ?>
    </div>

    <script src="/js/admin.js"></script>
    <script src="/js/Sortable.min.js"></script>

    <!-- Sidenav -->
    <ul id="slide-out-admin" class="sidenav sidenav-fixed">
        <li>
            <div class="user-view">
                <div class="background">
                    <img alt="Site logo" src="/img/ADN.jpg">
                </div>
                <a href="/admin"><img class="circle" alt="Site logo" src="/img/logo.png"></a>
                <a href="/admin"><span class="white-text name">Admin Console</span></a>
                <a><span class="white-text email"></span></a>
            </div>
        </li>

        <?php if (SITE_MAINTENANCE) { ?>
            <li>
                <a class="waves-effect red-text" href="/admin">
                    Website is in maintenance mode
                </a>
            </li>
            <li><div class="divider"></div></li>
        <?php } ?>
        
        <li><a class="subheader">Import</a></li>
        <li <?= ($d['active_page'] === 'import_genome' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/import/genome"><i class="material-icons">backup</i>Import genome file</a>
        </li>
        <li <?= ($d['active_page'] === 'alias_import' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/alias/import"><i class="material-icons">import_export</i>Import mapping files</a>
        </li>
        <li <?= ($d['active_page'] === 'import_blast' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/import/blast"><i class="material-icons">playlist_add</i>Import sequence files</a>
        </li>
    
        <li><div class="divider"></div></li>

        <li><a class="subheader">Build</a></li>
        <li <?= ($d['active_page'] === 'build_genome' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/build/genome"><i class="material-icons">storage</i>Build genome database</a>
        </li>
        <li <?= ($d['active_page'] === 'alias_build' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/alias/build"><i class="material-icons">merge_type</i>Build alias mapping</a>
        </li>
        <li <?= ($d['active_page'] === 'build_blast' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/build/blast"><i class="material-icons">sort</i>Build BLAST database</a>
        </li>
    
        <li><div class="divider"></div></li>

        <li><a class="subheader">Manage</a></li>
        <li <?= ($d['active_page'] === 'db_species' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/db_species"><i class="material-icons">bug_report</i>Database species</a>
        </li>
        <li <?= ($d['active_page'] === 'species' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/species"><i class="material-icons">lock</i>Protected species</a>
        </li>
        <li <?= ($d['active_page'] === 'reset' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/reset_link"><i class="material-icons">refresh</i>Reset link status</a>
        </li>
        <li <?= ($d['active_page'] === 'password' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/password"><i class="material-icons">vpn_key</i>Change password</a>
        </li>

        <li><div class="divider"></div></li>

        <li><a class="subheader">Tools</a></li>
        <li <?= ($d['active_page'] === 'stats' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/stats"><i class="material-icons">show_chart</i>Statistics</a>
        </li>
        <li <?= ($d['active_page'] === 'converter' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/converter"><i class="material-icons">redo</i>FASTA converter</a>
        </li>
        <li <?= ($d['active_page'] === 'checker' ? 'class="active"' : '') ?>>
            <a class="waves-effect" href="/admin/checker"><i class="material-icons">check</i>FASTA checker</a>
        </li>
    </ul>

    <style>
        header, main, footer {
            padding-left: 300px;
        }
        @media only screen and (max-width : 992px) {
            header, main, footer {
                padding-left: 0;
            }
        }
    </style>
    <?php
}

function homePageView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Administration console let you manage database, FASTA and mapping files stored in this server.<br>
                    This interface can edit protected species, change administrator password, check the validity of a FASTA
                    file with present data in the database or modify a FASTA file using a simple convertor.<br><br>

                    For the first configuration of this website, you must import first base TSV file using "Import genome file"
                    utility.<br>
                    After, you're free to import mapping files and sequences in FASTA format, and add it to the built database
                    with "Build alias mapping" or "Build BLAST database" modules.
                </p>
            </div>
            <p class="flow-text">
                Please choose a module in the side-navigation menu.
            </p>

            <div class='divider divider-margin'></div>

            <div class="col s12">
                <p>
                    Website is currently 
                    <span class="underline" id="accessible_text"><?= ($data['accessible'] ? 'accessible' : 'in maintenance mode') ?></span>.
                
                <?php if ($data['accessible']) { ?>
                    <a href="#!" class="btn-flat btn-perso red-text" onclick="changeWebsiteAccess(this)" data-access="1">
                        Toggle site maintenance mode
                    </a>
                <?php } else { ?>
                    <a href="#!" class="btn-flat btn-perso green-text" onclick="changeWebsiteAccess(this)" data-access="0">
                        Restore site visibility
                    </a>
                <?php } ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Print available FASTA files on screen
 *
 * @param array $files
 * @param boolean $with_delete_input
 * @return void
 */
function showFastaFiles(array $files, bool $with_delete_input) : void {
    ?>
    <div class="row">
        <div class="col s12">
            <?php if (count($files['adn']) !== 0) { ?>
                <h5>DNA FASTA files</h5>
                <ul class="collection">
                    <?php foreach($files['adn'] as $f) { ?>
                        <li class="collection-item avatar">
                            <i class="material-icons circle">insert_drive_file</i>
                            <span class="title"><?= $f['name'] ?></span>
                            <p>
                                <?= $f['size'] ?> Mo<br>
                                Imported <?= date('Y-m-d', $f['date']) ?>
                            </p>
                            <?php if ($with_delete_input) { ?>
                                <a href="#!" onclick="deleteFile('<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>', 'adn')" 
                                class="secondary-content">
                                    <i class="material-icons red-text">delete_forever</i>
                                </a>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <?php if (count($files['adn']) !== 0 && count($files['pro']) !== 0) { ?>
                <div class='divider divider-margin'></div>
            <?php } ?>

            <?php if (count($files['pro']) !== 0) { ?>
                <h5>Proteic FASTA files</h5>
                <ul class="collection">
                    <?php foreach($files['pro'] as $f) { ?>
                        <li class="collection-item avatar">
                            <i class="material-icons circle">insert_drive_file</i>
                            <span class="title"><?= $f['name'] ?></span>
                            <p>
                                <?= $f['size'] ?> Mo<br>
                                Imported <?= date('Y-m-d', $f['date']) ?>
                            </p>
                            <?php if ($with_delete_input) { ?>
                                <a href="#!" onclick="deleteFile('<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>', 'pro')" 
                                class="secondary-content">
                                    <i class="material-icons red-text">delete_forever</i>
                                </a>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>

    <?php
}

/**
 * Print $files in screen
 * $files = [['name':"", 'size':0, 'date':10000000], ...]
 *
 * @param array $files
 * @param boolean $with_delete_input
 * @return void
 */
function showMappingFiles(array $files, bool $with_delete_input) : void {
    ?>
    <div class="row">
        <div class="col s12">
            <ul class="collection">
                <?php foreach($files as $f) { ?>
                    <li class="collection-item avatar">
                        <i class="material-icons circle">insert_drive_file</i>
                        <span class="title"><?= $f['name'] ?></span>
                        <p>
                            <?= $f['size'] ?> Ko<br>
                            Imported <?= date('Y-m-d', $f['date']) ?>
                        </p>
                        <?php if ($with_delete_input) { ?>
                            <a href="#!" onclick="deleteFile('<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>')" 
                            class="secondary-content">
                                <i class="material-icons red-text">delete_forever</i>
                            </a>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <?php
}

/**
 * Find name
 * Recursive function
 *
 * @param array $base_file
 * @param array $files
 * @param string $location
 * @param string $basename
 * @param string|null $initial
 * @param integer $number
 * @return string|null
 */
function findName(array $base_file, array $files, string $location, string $basename, ?string $initial = null, int $number = 1) : ?string {
    $initial = $initial ?? $basename;

    $pos = array_search($basename, $base_file);

    if ($pos !== false) { // L'élément existe dans les fichiers existants
        if (filesize($location) === filesize($files[$pos]) && md5_file($location) === md5_file($files[$pos])) {
            // Fichier déjà présent
            return null;
        }
        
        $pos_point = strrpos($initial, '.');

        if ($pos_point !== false) { // Il y a un point
            $explode[0] = substr($initial, 0, $pos_point);
            $explode[1] = substr($initial, $pos_point);

            $new_name = $explode[0] . '_' . $number . $explode[1];

            return findName($base_file, $files, $location, $new_name, $initial, $number + 1);
        }
        else {
            $new_name = $initial . '_' . $number;

            return findName($base_file, $files, $location, $new_name, $initial, $number + 1);
        }
    }

    return $basename;
}

/**
 * Find a usable free name in $location using $name and $files
 *
 * @param array $files
 * @param string $location
 * @param string $name
 * @return string|null
 */
function findSafeName(array $files, string $location, string $name) : ?string {
    $base_files = array_map('basename', $files);
    $basename = basename($name);

    return findName($base_files, $files, $location, $basename);
}
