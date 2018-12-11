<?php

require 'pages/search/view.php';
require 'pages/search/id.php';
require 'pages/search/name.php';
require 'pages/search/pathway.php';
require 'pages/search/global.php';

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
    else if ($args[0] === 'pathway') {
        $data = searchPathway();
        $data['mode'] = "pathway";
    }
    else {
        throw new PageNotFoundException();
    }

    return new Controller($data, "Search");
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
        case 'pathway':
            showSearchByPathway($d);
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
        <div class="parallax"><img alt="Home image" src="/img/mol_search.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h4 class='header'>
                    What do you want to search ?
                </h4>
            </div>
            <div class="row no-margin-bottom">
                <div class="col s12 m5">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/name'><i class="material-icons mat-title light-blue-text">assignment</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/name' class='no-link-color'>Name</a>
                        </h5>

                        <p class="light text-justify">
                            Find every gene with the same name across all our database's species.
                        </p>
                    </div>
                </div>

                <div class="col s12 m5 offset-m2">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/id'><i class="material-icons mat-title light-blue-text">label</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/id' class='no-link-color'>ID</a>
                        </h5>

                        <p class="light text-justify">
                            Access details of a specific gene with an identifier search.
                        </p>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class="col s12 m5">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/pathway'><i class="material-icons mat-title light-blue-text">call_split</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/pathway' class='no-link-color'>Pathway</a>
                        </h5>

                        <p class="light text-justify">
                            Browse through the genes involved in a specific pathway.
                        </p>
                    </div>
                </div>

                <div class="col s12 m5 offset-m2">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search/global'><i class="material-icons mat-title light-blue-text">all_inclusive</i></a>
                        </h2>
                        <h5 class="center">
                            <a href='/search/global' class='no-link-color'>Advanced</a>
                        </h5>

                        <p class="light text-justify">
                            Combine your criterias using keywords to search by names, IDs, families and genes roles
                            at the same time, filter your search with specific pathways and species to find 
                            the associated genes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
    </div>

    <?php
}
