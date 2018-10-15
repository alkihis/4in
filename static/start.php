<?php

function homeView(Controller $c) : void { ?>
    <div class="parallax-container parallax-home-page">
        <div class="section no-pad-bot">
            <div class="container">
                <br><br>
                <h1 class="header center blue-text text-lighten-4">NC Insect Innate Immunity</h1>
                <div class="row center">
                    <p class='flow-text head-main-title white-text'>
                        Une base de données de gènes de l'immunité innée chez les insectes
                    </p>
                </div>
                <br><br>

            </div>
        </div>
        <div class="parallax" style='z-index: -1;'><img src="/img/ch_jardin.jpg" style='filter: brightness(0.80)'></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row">
                <p class='flow-text text-justify head-main-title'>
                    Développée dans le but de faciliter l'accès aux données récoltées au sein de différentes espèces,
                    cette base de données est centrée autour de l'étude génétique du charançon du riz, 
                    également nommé Sitophilus oryzae.
                </p>
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
