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
                <p class="light text-justify flow-text">
                            La base de données contient des gènes provenant de 14 espèces d'insectes différentes. 
                            Chaque gène dispose d'un nom, d'un identifiant unique, d'une ou plusieurs voies immunitaires associées
                            ainsi que des gènes homologues si ils existent.
                            La base de données à été crée avec MySQL et est accessible via le site web, qui permet d'effectuer différentes requêtes.
                </p>
            </div>
            <div class="section">
                <h5 class='title'>
                    Recherche
                </h5>
                <p class="light text-justify flow-text">
                            Le site web permet d'effectuer des recherches variées sur l'ensemble ou non de la base de données. 
                            Vous pouvez effectuer des recherches de 4 types : par nom, par identifiant, par voie ou recherche avancée qui vous permet de
                            cumuler les paramètres pour faire une recherche spécifique.
                </p>
            </div>
            <div class="section">
                <h5 class='title'>
                    BLAST
                </h5>
                <p class="light text-justify flow-text">
                            Il est possible d'aligner des séquences importées depuis votre ordinateur sur l'ensemble ou une partie de la base de données en utilisant l'outil "BLAST".
                            Il vous sera nécessaire d'avoir un fichier au format fasta ou bien de copier vos séquences directement dans la case associée.
                            Le résultat de l'alignement sera présenté sous la forme d'une page web, contenant les résultats de l'alignement au format standard pour
                            les différents gènes avec lesquels vous avez choisi d'aligner votre séquence.
                </p>
            </div>
        </div>
    </div>
    <?php
}
