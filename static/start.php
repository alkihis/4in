<?php

function homeView(Controller $c) { ?>
    <div class="section no-pad-bot" id="index-banner">
        <div class="container">
            <h1 class="header center blue-text">NC Insect Innate Immunity</h1>
            <div class="row center">
                <!-- <h5 class="header col s12 light">Une base de données des gènes de l'immunité innée chez les insectes</h5> -->
                <p class='flow-text head-main-title'>
                    Bienvenue sur <?= SITE_NAME ?>, une base de données de gènes de l'immunité innée chez les insectes.
                    Développée dans le but de faciliter l'accès aux données récoltées au sein de différentes espèces,
                    cette base de données est centrée autour d'une étude génétique du charançon du riz (Sitophilus oryzae).
                </p>
            </div>
            <div class="row center">
                <a href="/begin" id="download-button" class="btn-large waves-effect waves-light blue lighten-2">Commencer</a>
            </div>
            <br><br>

        </div>
    </div>

    <div class="parallax-container parallax-home-page">
        <div class="parallax"><img src="/img/ch_jardin.jpg"></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search'><i class="material-icons mat-title light-blue-text">search</i></a>
                        </h2>
                        <h5 class="center">Recherche</h5>

                        <p class="light text-justify">
                            Vous pouvez rechercher les gènes de l'immunité chez les insectes
                            par identifiant, voie, espèce et nom juste ici.
                        </p>
                    </div>
                </div>

                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/full_database'><i class="material-icons mat-title light-blue-text">settings_system_daydream</i></a>
                        </h2>
                        <h5 class="center">Base de données</h5>

                        <p class="light text-justify">
                            Consultez l'ensemble de la base de données. Téléchargez les données au format de votre choix,
                            triez les résultats selon votre besoin et renseignez vous sur les annotations fonctionnelles
                            disponibles.
                        </p>
                    </div>
                </div>

                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/blast_search'><i class="material-icons mat-title light-blue-text">sort</i></a>
                        </h2>
                        <h5 class="center">BLAST</h5>

                        <p class="light text-justify">
                            Alignez vos propres séquences en utilisant l'algorithme de BLAST sur notre base de données.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
    </div>

    <div class="parallax-container parallax-home-page">
        <div class="parallax"><img src="/img/phagocyte.jpg"></div>
    </div>

<?php }

function homeControl() : Controller {
    // Insérer ici des traitements à réaliser dans la page d'accueil
    // Pour le moment aucun contenu dynamique n'est créé, on renvoie donc un Controller vide
    // Si il y avait des traitements, ils seraient dans le tableau passé au Controller

    return new Controller([], 'Accueil');
}

// Il ne doit subsister aucun code HTML dans la page, tout est dans des fonctions PHP
