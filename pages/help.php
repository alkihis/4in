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
                            This database contains genes from 14 different species of insects. 
                            Every gene has a name, a unique ID, one or several associated immunity pathways,
                            as well as the IDs of any homologous genes that might exist in the database.
                            The database was set up with MySQL and is accessible via our website, which allows user to perform several requests.
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
                            The website is capable of performing a number of search on the totality or part of the database.
                            You have access to 4 different types of searches : by name, by ID, by pathway or the advanced search which allows you 
                            to cumulate your criterias to perform a specific request.
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
                            You can align sequences from your computer on the totality of the database sing the "BLAST" tool.
                            Your results will be rendred in the form of a webpage, containing the alignments and links to those genes' webpage.
                            You will need to provide a '.fasta' file or to copy and paste your sequences in the associated form.
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
                The database is home to over 8000 genes implicated in the innate immunity system of 14 species of insects.
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
