<?php

require 'inc/GeneObject.php';

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

////// ID //////
function searchById() : array {
    $r = [];

    if (isset($_GET['id']) && is_string($_GET['id'])) {
        $r['previous_search'] = [];
        $r['previous_search']['id'] = htmlspecialchars($_GET['id'], ENT_QUOTES);

        global $sql;
        // Recherche de l'identifiant dans la base de données
        $id = mysqli_real_escape_string($sql, $_GET['id']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, 
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
             FROM Pathways p 
             WHERE g.id = p.id) as pathways 
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE a.gene_id='$id'
        GROUP BY a.gene_id, g.id ORDER BY g.id");

        if (!$q) {
            throw new UnexpectedValueException("Echec de la requête SQL");
        }

        if (mysqli_num_rows($q)) { // Il y a un identifiant trouvé, on le récupère
            $row = mysqli_fetch_assoc($q); // Il y en a qu'un seul possible, pas de besoin de mettre ça dans une boucle

            // results empêche la génération du formulaire de recherche,
            // et affiche les résultats à la page
            $r['results'][] = new GeneObject($row);
        }
        else {
            $r['previous_search']['empty_id'] = true;
        }
    }

    return $r;
}

function showSearchById(array $data) : void {
    if (! isset($data['results'])) { // Aucun résultat : formulaire à générer
        generateSearchForm('id', $data['previous_search'] ?? []);
    }
    else { // résultats : tableau de résultat à générer
        generateSearchResultsArray($data['results']);
    }
}

////// NAME //////
function searchByName() : array {
    $r = [];

    if (isset($_GET['name']) && is_string($_GET['name'])) {
        $r['previous_search'] = [];
        $r['previous_search']['name'] = htmlspecialchars($_GET['name'], ENT_QUOTES);

        global $sql;
        // Recherche du nom dans la base de données
        $name = mysqli_real_escape_string($sql, $_GET['name']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, 
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
             FROM Pathways p 
             WHERE g.id = p.id) as pathways 
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE g.gene_name LIKE '$name%'
        GROUP BY a.gene_id, g.id ORDER BY g.id");

        if (!$q) {
            throw new UnexpectedValueException("Echec de la requête SQL");
        }

        if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
            while($row = mysqli_fetch_assoc($q)) { // Il peut y avoir plusieurs occurences, on met ça dans une boucle
                $r['results'][] = new GeneObject($row);
            } 
            // results empêche la génération du formulaire de recherche,
            // et affiche les résultats à la page
        }
        else {
            $r['results'] = [];
        }
    }

    return $r;
}

function showSearchByName(array $data) : void {
    generateSearchForm('name', $data['previous_search'] ?? []);

    if (isset($data['results'])) {
        generateSearchResultsArray($data['results']);
    }
}
////// FONCTIONS GENERALES //////
function generateSearchForm($mode = 'id', $previous_data = []) {
    ?>
    <div class='container'>
        <div class='row section'>
            <div class='card col s12 card-border'>
                <div class='card-content'>
                    <form method='get' action='/search/<?= $mode ?>'>
                        <?php if ($mode === 'id') { ?>
                            <div class='input-field col s12'>
                                <i class="material-icons prefix">label</i>
                                <input type='text' autocomplete='off' name="id" id="gene_id">
                                <label for='gene_id'>Identifiant</label>
                            </div>

                        <?php } elseif ($mode ==='name') { ?>
                            <div class='input-field col s12'>
                                <i class="material-icons prefix">label</i>
                                <input type='text' autocomplete='off' name="name" id="gene_name" value='<?= $previous_data['name'] ?? '' ?>'>
                                <label for='gene_name'>Nom</label>
                            </div>
                        <?php } ?>
                        <button type='submit' class='btn-flat right blue-text'>Rechercher</button>
                        <div class='clearb'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
}

function generateSearchResultsArray(array $res) : void {
    ?>

    <div class='container'>
        <div class='row'>
            <div class='col s12'>
                <h3>
                    <a href='#!' onclick='window.history.back()'>
                        <i class='material-icons left mat-title' style='margin-top: 3px;'>arrow_back</i>
                    </a>
                    Résultats de votre recherche
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Pathway</th>
                            <th>Fullname</th>
                            <th>Family</th>
                            <th>Subfamily</th>
                            <th>Specie</th>
                            <th>Gene ID</th>
                            <th>Sequence ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        foreach ($res as $gene) {
                            generateArrayLine($gene);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
}
function generateArrayLine(Gene $line) : void { ?>
    <tr>
        <td><?= $line->getName() ?></td>
        <td><?= $line->getFunction() ?></td>
        <td><?= implode(', ', $line->getPathways()) ?></td>
        <td><?= $line->getFullName() ?></td>
        <td><?= $line->getFamily() ?></td>
        <td><?= $line->getSubFamily() ?></td>
        <td><?= $line->getSpecie() ?></td>
        <td><?= $line->getID() ?></td>
        <td><?= $line->getSequenceID() ?></td>
    </tr>

    <?php
}
