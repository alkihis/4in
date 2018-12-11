<?php

function homeControl() : Controller {
    // On choisit aléatoirement une image

    $imgs = glob($_SERVER['DOCUMENT_ROOT'] . '/img/home/*.jpg');

    if (count($imgs) === 0) {
        throw new ErrorException("No image is set");
    }

    $number = random_int(0, count($imgs)-1);
    $name = basename($imgs[$number]);

    global $sql;

    $beginOfDay = strtotime("midnight");

    mt_srand($beginOfDay);

    // sélection d'un gène aléatoire à présenter
    $protected = "";
    if (LIMIT_GENOMES && !isUserLogged() && !empty(getProtectedSpecies())) {
        $protected = " WHERE ";

        $fir = true;
        foreach (getProtectedSpecies() as $specie) {
            if ($fir) {
                $fir = false;
            }
            else {
                $protected .= " AND ";
            }

            $specie = mysqli_real_escape_string($sql, $specie);
            $protected .= " specie != '$specie' ";
        }
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations $protected");

    $rows = mysqli_fetch_all($q, MYSQLI_ASSOC); // Récupération de toutes les lignes en même temps

    /** @noinspection RandomApiMigrationInspection */
    $random_gene = mt_rand(0, count($rows) - 1);

    // Reset de la seed
    mt_srand();

    $gene_id_random = $rows[$random_gene]['gene_id'];

    return new Controller(['image' => $name, 'gene' => new Gene($gene_id_random)], 'Home');
}

function homeView(Controller $c) : void { 
    $data = $c->getData();
    $image = $data['image'];

    ?>
    <div class="parallax-container parallax-home-page">
        <div class="section no-pad-bot" style="position: relative; z-index: 1;">
            <div class="container">
                <br><br>
                <h1 class="header center header-main white-text">Interactive Innate Immunity of INsect</h1>
                <div class="row center">
                    <p class='flow-text head-main-title white-text border-head'>
                        A database for insects' innate immunity genes. 
                    </p>
                </div>
                <br><br>

            </div>
        </div>
        <div class="parallax"><img alt="Home image" src="/img/home/<?= $image ?>" style='filter: brightness(0.80)'></div>
    </div>

    <div class="container">
        <div class="section">
            <!--   Icon Section   -->
            <div class="row no-margin-bottom">
                <?php if (SITE_MAINTENANCE)  { ?>
                    <p class='flow-text red-text'>
                        Website is in maintenance mode. Access 
                        <a href="/admin" class="black-text underline-hover">administration console</a> to change status.
                    </p>
                <?php } ?>

                <p class='flow-text text-justify head-main-title'>
                    Developed in order to facilitate access to the data harvested from different species of insects,
                    this database is centered around the genetic study of the rice weevil, also called Sitophilus oryzae.
                </p>
                
                <div class="col s12 m<?= (isContributorLogged() ? '4' : '5') ?>">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/search'><i class="material-icons mat-title light-blue-text">search</i></a>
                        </h2>
                        <h5 class="center">Search</h5>

                        <p class="light text-justify">
                            You can search immunity genes by ID, pathway, species and name here, 
                            across all of our insect genes database.
                        </p>
                    </div>
                </div>

                <div class="col s12 m<?= (isContributorLogged() ? '4' : '5 offset-m2') ?>">
                    <div class="icon-block">
                        <h2 class="center">
                            <a href='/blast_search'><i class="material-icons mat-title light-blue-text">sort</i></a>
                        </h2>
                        <h5 class="center">BLAST</h5>

                        <p class="light text-justify">
                            Align your own sequences using BLAST's algorithm on our database's sequences.
                            You can choose whether to align nucleotides or amino acids sequences.
                        </p>
                    </div>
                </div>

                <?php if (isContributorLogged()) { ?>
                    <div class="col s12 m4">
                        <div class="icon-block">
                            <h2 class="center">
                                <a href='/add'><i class="material-icons mat-title light-blue-text">add</i></a>
                            </h2>
                            <h5 class="center">Add gene</h5>

                            <p class="light text-justify">
                                Add manually a gene into the database by writing all its specifications.
                            </p>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="divider divider-margin"></div>
            <div class="row">
                <div class="col s12">
                    <h4>Gene of the day <span class="block right light-text"><?= $data['gene']->getSpecie() ?></span></h4>
                    <div class="clearb"></div>
                    <h5 class="center">
                        <a target='_blank' href="/gene/<?= $data['gene']->getID() ?>"><?= $data['gene']->getID() ?></a>
                        <?php if ($data['gene']->getAlias()) { ?>
                            <span class="very-tiny-text"><?= $data['gene']->getAlias() ?></span>
                        <?php } ?>
                    </h5>
                    <?php 
                    // Assemblage des données du gène
                    $str = [];

                    if ($data['gene']->getName()) {
                        $str[] = "<h5>Name</h5><div class='gene-info center-force'>" . $data['gene']->getName() . '</div>';
                    }

                    if ($data['gene']->getFullname()) {
                        $str[] = "<h5>Fullname</h5><div class='gene-info center-force'>" . $data['gene']->getFullname() . '</div>';  
                    }

                    if ($data['gene']->getFamily()) {
                        $str[] = "<h5>Family</h5><div class='gene-info center-force'>" . $data['gene']->getFamily() . '</div>';
                    }
                    
                    if ($data['gene']->getSubFamily()) {
                        $str[] = "<h5>Sub-family</h5><div class='gene-info center-force'>" . $data['gene']->getSubFamily() . '</div>';
                    }  

                    if ($data['gene']->getFunction()) {
                        $str[] = "<h5>Function</h5><div class='gene-info center-force'>" . $data['gene']->getFunction() . '</div>';
                    }

                    if (!empty($data['gene']->getPathways())) {
                        $str[] = "<h5>Pathway</h5><div class='black-text'>" . implode('<br>', $data['gene']->getPathways()) . '</div><br>';
                    }

                    foreach ($str as $key => $s) { 
                        // Affichage des informations par ligne de deux éléments
                        if ($key % 2 === 0) {
                            echo '<div class="row no-margin-bottom">';
                            if ($key === (count($str)-1)) { // Le dernier élément est un début de ligne : prend toute la ligne
                                echo "<div class='col s12 no-pad center-force'>$s</div></div>";
                            }
                            else {
                                echo "<div class='col s12 l6 no-pad center-force'><div style='width: 95%; margin: auto;'>$s</div></div>";
                            }
                        }
                        else {
                            echo "<div class='col s12 l6 no-pad center-force'><div style='width: 95%;  margin: auto;'>$s</div></div>";
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <div class="clearb"></div>
            </div>
        </div>
        <br><br>
    </div>

    <div class="parallax-container parallax-home-page">
        <div class="parallax"><img alt="DNA image" src="/img/ADN.jpg"></div>
    </div>

    <?php 
}

// Il ne doit subsister aucun code HTML dans la page, tout est dans des fonctions PHP
