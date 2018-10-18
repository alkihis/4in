<?php 

////// HOME //////
function helpHomePage() : array {
    
    return [];
}

function helpControl(array $args) : Controller {

    if (! isset($args[0])) {
        $data['mode'] = "home";
        $title = "Help";
    }
    else if ($args[0] === 'DB') {
        $data['mode'] = "DB";
        $title = "Help - Database";
    }
    else if ($args[0] === 'search') {
        $data['mode'] = "search";
        $title = "Help - Search";
    }
    else if ($args[0] === 'BLAST') {
        $data['mode'] = "BLAST";
        $title = "Help - BLAST";
    }
    else {
        throw new PageNotFoundException();
    }

    return new Controller($data, $title);
}

function helpView(Controller $c) : void {
    $d = $c->getData();

    switch ($d['mode']) {
        case 'home':
            showHelpHome($d);
            break;
        case 'DB':
            showHelpDB($d);
            break;
        case 'search':
            showHelpSearch($d);
            break;
        case 'BLAST':
            showHelpBlast($d);
            break;
    }
}

////// HOME //////
function showHelpHome(array $data) : void {
    ?>
    <div class="parallax-container parallax-search-page">
        <div class="parallax"><img src="/img/mol_search.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h4 class='header'>
                    Help section
                </h4>
            </div>
            <div class="row no-margin-bottom">
                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/help/DB'><i class="material-icons mat-title light-blue-text">settings_system_daydream</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/help/DB' class='no-link-color'>Database</a>
                        </h5>

                        <p class="light text-justify">
                            La base de données contient des gènes provenant de 14 espèces d'insectes différentes. 
                            Chaque gène dispose d'un nom, d'un identifiant unique, d'une ou plusieurs voies immunitaires associées
                            ainsi que des gènes homologues si ils existent.
                            La base de données à été crée avec MySQL et est accessible via le site web, qui permet d'effectuer différentes requêtes.
                        </p>
                    </div>
                </div>

                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/help/search'><i class="material-icons mat-title light-blue-text">search</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/help/search' class='no-link-color'>Search</a>
                        </h5>

                        <p class="light text-justify">
                            Le site web permet d'effectuer des recherches variées sur l'ensemble ou non de la base de données. 
                            Vous pouvez effectuer des recherches de 4 types : par nom, par identifiant, par voie ou recherche avancée qui vous permet de
                            cumuler les paramètres pour faire une recherche spécifique.
                        </p>
                    </div>
                </div>
                <div class="col s12 m4  ">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/help/BLAST'><i class="material-icons mat-title light-blue-text">sort</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/help/BLAST' class='no-link-color'>BLAST</a>
                        </h5>

                        <p class="light text-justify">
                            Il est possible d'aligner des séquences importées depuis votre ordinateur sur l'ensemble ou une partie de la base de données en utilisant l'outil "BLAST".
                            Il vous sera nécessaire d'avoir un fichier au format fasta ou bien de copier vos séquences directement dans la case associée.
                            Le résultat de l'alignement sera présenté sous la forme d'une page web, contenant les résultats de l'alignement au format standard pour
                            les différents gènes avec lesquels vous avez choisi d'aligner votre séquence.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

////// DATABASE //////
function showHelpDB(array $data) : void {
    ?>
    <div class="parallax-container parallax-search-page">
        <div class="parallax"><img src="/img/database.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h3 class='header'>
                    Database
                </h3>
            </div>
            <p class="flow-text">
                The database is home to over 8000 genes implicated in the innate immunity of 14 sepecies of insects.
            </p>
        </div>
    </div>

    <?php
}

////// SEARCH //////
function showHelpSearch(array $data) : void {
    ?>
    <div class="parallax-container parallax-search-page">
        <div class="parallax"><img src="/img/database.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h3 class='header'>
                    Search
                </h3>
            </div>
            <p class="flow-text">
                
            </p>
        </div>
    </div>

    <?php
}

////// BLAST //////
function showHelpBlast(array $data) : void {
    ?>
    <div class="parallax-container parallax-search-page">
        <div class="parallax"><img src="/img/dna.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h3 class='header'>
                    BLAST
                </h3>
            </div>
            <p class="flow-text">
                
            </p>
        </div>
    </div>

    <?php
}
