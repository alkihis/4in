<?php

function searchControl(array $args) : Controller {
    if (! isset($args[0])) {
        $data = searchHomePage();
        $data['mode'] = "home";
    }
    else if ($args[0] === 'id') {
        $data = searchById();
        $data['mode'] = "id";
    }
    else if ($args[0] === 'name') {
        $data = searchByName();
        $data['mode'] = "name";
    }
    else if ($args[0] === 'global') {
        $data = searchAdvanced();
        $data['mode'] = "global";
    }
    else {
        throw new PageNotFoundException();
    }

    return new Controller($data, "Recherche");
}

function searchView(Controller $c) : void {
    $d = $c->getData();

    switch ($d['mode']) {
        case 'home':
            showSearchHome($d);
            break;
        case 'id':
            showSearchById($d);
            break;
        case 'name':
            showSearchByName($d);
            break;
        case 'global':
            showGlobalSearch($d);
            break;
    }
}

////// HOME //////
function searchHomePage() : array {
    return [];
}

function showSearchHome(array $data) : void {
    ?>
    <div class="parallax-container parallax-search-page">
        <div class="parallax"><img src="/img/mol_search.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h4 class='header'>
                    Quelle recherche souhaitez-vous effectuer ?
                </h4>
            </div>
            <div class="row">
                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/name'><i class="material-icons mat-title light-blue-text">assignment</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/name' class='no-link-color'>Nom</a>
                        </h5>

                        <p class="light text-justify">
                            En connaissant le nom formel d'un gène, trouvez ses occurences connues dans 
                            les différentes espèces de la base de données.
                        </p>
                    </div>
                </div>

                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/id'><i class="material-icons mat-title light-blue-text">label</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/id' class='no-link-color'>Identifiant</a>
                        </h5>

                        <p class="light text-justify">
                            Consultez le détail d'un gène précis via son identifiant unique.
                        </p>
                    </div>
                </div>

                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/global'><i class="material-icons mat-title light-blue-text">all_inclusive</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/global' class='no-link-color'>Avancée</a>
                        </h5>

                        <p class="light text-justify">
                            Combinez les paramètres, recherchez par voie, espèce, nom et d'autres critères.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
    </div>

    <?php
}
