<?php

function homeView(Controller $c) : void { ?>
    <div class="parallax-container parallax-home-page">
        <div class="section no-pad-bot">
            <div class="container">
                <br><br>
                <h1 class="header center blue-text text-lighten-4">NC Insect Innate Immunity DB</h1>
                <div class="row center">
                    <p class='flow-text head-main-title white-text'>
                        A database for insects' innate immunity genes. 
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
                    Developped in order to ease access to the data harvested from different species,
                    this database is centered around the genetic study of the "rice charançon", also called Sitophilus oryzae.
                </p>
                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search'><i class="material-icons mat-title light-blue-text">search</i></a>
                        </h2>
                        <h5 class="center">Search</h5>

                        <p class="light text-justify">
                            You can search our insect's immunity genes by ID, pathway, species and name here.
                        </p>
                    </div>
                </div>

                <div class="col s12 m4">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/full_database'><i class="material-icons mat-title light-blue-text">settings_system_daydream</i></a>
                        </h2>
                        <h5 class="center">Database</h5>

                        <p class="light text-justify">
                            Check out our entire database here. Download the data in the format of your choice, 
                            sort your results according to your needs and learn about the functionnal annotations available. 
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
                            Align your own sequences using BLAST's algorithm on our database's sequences.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
    </div>

    <div class="parallax-container parallax-home-page">
        <div class="parallax"><img src="/img/ADN.jpg"></div>
    </div>

<?php }

function homeControl() : Controller {
    // Insérer ici des traitements à réaliser dans la page d'accueil
    // Pour le moment aucun contenu dynamique n'est créé, on renvoie donc un Controller vide
    // Si il y avait des traitements, ils seraient dans le tableau passé au Controller

    return new Controller([], 'Home');
}

// Il ne doit subsister aucun code HTML dans la page, tout est dans des fonctions PHP
