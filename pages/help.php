<?php 

////// HOME //////
function helphHomePage() : array {
    return [];
}

function helpControl(array $args) : Controller {
    if (! isset($args[0])) {
        $data = helphHomePage();
        $data['mode'] = "home";
    }
    else {
        throw new PageNotFoundException();
    }

    return new Controller($data, "Aide");
}

function helpView(Controller $c) : void {
    ?>
    <div class="parallax-container parallax-search-page">
        <div class="parallax"><img src="/img/mol_search.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <h4 class='header'>
                    Rubrique Aide
                </h4>
            </div>
            <div class="section">
                <h5 class='title'>
                    Base de données
                </h5>
                <p class="light text-justify">
                            La base de données contient des gènes provenant de 14 espèces d'insectes différentes. 
                            Chaque gène dispose d'un nom, d'un identifiant unique, d'une ou plusieurs voies immunitaires associées
                            ainsi que des gènes homologues si ils existent.
                            La base de données à été crée avec MySQL et est accessible via le site web, qui permet d'effectuer différentes requêtes.
                </p>
            </div>
        </div>
    </div>
    <?php
}
