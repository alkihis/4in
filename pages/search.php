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
        <div class="parallax"><img src="/img/mol_search.jpg"></div>
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
                            Find every gene bearing the same name across all our database's species.
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
                            Check the details of a specific gene with an ID search.
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
                            Combine your search criterias, with pathways, species, names and others to find a 
                            specific gene or group of genes.
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

    if (isset($_GET['id']) && is_string($_GET['id']) && strlen($_GET['id']) > 1) {
        $r['form_data'] = [];
        $r['form_data']['id'] = htmlspecialchars($_GET['id'], ENT_QUOTES);

        global $sql;
        // Recherche de l'identifiant dans la base de données
        $id = mysqli_real_escape_string($sql, $_GET['id']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, 
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
             FROM Pathways p 
             WHERE g.id = p.id) as pathways 
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE a.gene_id LIKE '$id%'
        GROUP BY a.gene_id, g.id ORDER BY g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        if (mysqli_num_rows($q)) { // Il y a un identifiant trouvé, on le récupère
            while ($row = mysqli_fetch_assoc($q)) {
                // results empêche la génération du formulaire de recherche,
                // et affiche les résultats à la page
                $r['results'][] = new GeneObject($row);
            }            
        }
        else {
            $r['results'] = []; // Résultats vides
        }
    }

    return $r;
}

function showSearchById(array $data) : void {
    generateSearchForm('id', $data['previous_search'] ?? []);

    if (isset($data['results'])) { // résultats : tableau de résultat à générer
        generateSearchResultsArray($data['results']);
    }
}

////// NAME //////
function searchByName() : array {
    $r = [];

    if (isset($_GET['name']) && is_string($_GET['name'])) {
        $r['form_data'] = [];
        $r['form_data']['name'] = htmlspecialchars($_GET['name'], ENT_QUOTES);

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
        GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
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

////// PATHWAY //////
function searchPathway() : array {
    $r = [];

    // On récupère d'abord les différents pathways disponibles
    global $sql;
    $q = mysqli_query($sql, "SELECT DISTINCT pathway FROM Pathways;");

    if (!$q) {
        throw new Exception("Inoperative SQL base");
    }

    $r['form_data'] = [];
    $r['form_data']['no_search_btn'] = true;

    while($row = mysqli_fetch_assoc($q)) {
        $r['form_data']['select'][] = $row['pathway'];
    }

    if (isset($_GET['pathway']) && is_string($_GET['pathway'])) {
        $r['form_data']['pathway'] = htmlspecialchars($_GET['pathway'], ENT_QUOTES);

        // Recherche du nom dans la base de données
        // Normalement, le pathway est UNIQUEMENT un md5, on cherche donc avec la fonction sql MD5
        $pathway = mysqli_real_escape_string($sql, $_GET['pathway']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie,
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
            FROM Pathways p 
            WHERE g.id = p.id) as pathways 
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        JOIN Pathways pa ON pa.id=g.id
        WHERE MD5(pa.pathway)='$pathway'
        GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
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

function showSearchByPathway(array $data) : void {
    generateSearchForm('pathway', $data['form_data'] ?? []);

    if (isset($data['results'])) {
        generateSearchResultsArray($data['results']);
    }
}

////// AVANCEE //////
function searchAdvanced() : array {
    throw new NotImplementedException("Advanced search not yet implemented.");
}

function showGlobalSearch(array $data) : void {
    // TODO
}

////// FONCTIONS GENERALES //////
function generateSearchForm(string $mode = 'id', array $form_data = []) : void { ?>
    <div class='container'>
    <div class='row section no-margin-bottom'>
    <div class='card col s12 card-border'>
        <div class='card-content'>
            <form method='get' id='submit_form' action='#'>
                <?php if ($mode === 'id') { ?>
                    <div class='input-field col s12'>
                        <i class="material-icons prefix">label</i>
                        <input type='text' autocomplete='off' name="id"
                            id="gene_id" value='<?= $form_data['id'] ?? '' ?>'>
                        <label for='gene_id'>ID</label>
                    </div>

                    <script>
                        $(document).ready(function() {
                            // Récupération du tableau d'ID
                            $.get(
                                "/api/search/ids.json", 
                                { } 
                            ).then(function (json) {
                                $('#gene_id').autocomplete({
                                    data: json,
                                    limit: 6,
                                    minLength: 2,
                                    onAutocomplete: function() {
                                        document.getElementById('submit_form').submit();
                                    }
                                });
                            });
                        });
                    </script>

                <?php } 
                else if ($mode === 'name') { ?>
                    <div class='input-field col s12'>
                        <i class="material-icons prefix">assignment</i>
                        <input type='text' autocomplete='off' name="name" id="gene_name" 
                            value='<?= $form_data['name'] ?? '' ?>'>
                        <label for='gene_name'>Name</label>
                    </div>

                    <script>
                        $(document).ready(function() {
                            // Récupération du tableau de noms
                            $.get(
                                "/api/search/names.json", 
                                { } 
                            ).then(function (json) {
                                var g = document.getElementById('gene_name');
                                $(g).autocomplete({
                                    data: json,
                                    limit: 6,
                                    minLength: 0,
                                    onAutocomplete: function() {
                                        document.getElementById('submit_form').submit();
                                    }
                                });
                            });
                        });
                    </script>

                <?php } 
                else if ($mode === 'pathway') { ?>
                    <div class='input-field col s12'>
                        <select id='pathway_select' 
                            name='pathway' onchange="document.getElementById('submit_form').submit();">
                            <?php 
                            // Génération des options du select en fonction des pathways dans la base de données
                            foreach ($form_data['select'] as $option) {
                                $md5 = md5($option);
                                $option = htmlspecialchars($option);
                                echo "<option value='$md5'>$option</option>";
                            }

                            if (isset($form_data['pathway'])) { 
                                // Si l'utilisateur avait choisi quelque chose, on l'insère dans le 
                                // select via JS ?>

                                <script>
                                    $(document).ready(function() {
                                        $('#pathway_select').val("<?= $form_data['pathway'] ?>");
                                    });
                                </script>
                            <?php } ?>
                        </select>
                        <label>Metabolic pathway</label>
                    </div>
                <?php } ?>

                <?php if (!isset($form_data['no_search_btn']) || !$form_data['no_search_btn']) { ?>
                    <button type='submit' id='submit_btn' class='btn-flat right blue-text'>Search</button>
                <?php } ?>
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
                    Search results
                </h3>
                <?php if (empty($res)) { ?>
                <h4 class='red-text header'>No results</h4>
                <?php } else { ?>
                <h6><?= count($res) ?> result<?= count($res) > 1 ? 's' : '' ?></h6>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Gene ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Pathway</th>
                            <th>Fullname</th>
                            <th>Family</th>
                            <th>Subfamily</th>
                            <th>Specie</th>
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
                <?php } ?>
            </div>
        </div>
    </div>

    <div class='popup-download'>
        <div class='card card-border'>
            <div class='card-content'>
                <a href='#!' class='btn-flat green-text left' onclick="checkAllPageBoxes(true)">Check all</a>
                <a href='#!' class='btn-flat blue-text left' onclick="checkAllPageBoxes(false)">Uncheck all</a>
                <div href='#!' data-count="0" id='total_count_popup' class='grey-text dl-count-popup darken-4 left'>
                    <span id='count_popup'>0</span> Selected<span id='count_popup_s'>s</span>
                </div>
                <a href='#!' class='btn-flat blue-text right' onclick="downloadCheckedSequences()"><i class='material-icons left'>file_download</i> Download </a>
                <div class='clearb'></div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            initCheckboxes();
        });
    </script>

    <?php
}

function generateArrayLine(Gene $line) : void { ?>
    <tr>
        <td>
            <label>
                <input type="checkbox" class="filled-in chk-srch" dataset-id="<?= $line->getID() ?>">
                <span class="checkbox-search"></span>
            </label>
        </td>
        <td><?= $line->getID() ?></td>
        <td><?= $line->getName() ?></td>
        <td><?= $line->getFunction() ?></td>
        <td><?= implode(', ', $line->getPathways()) ?></td>
        <td><?= $line->getFullName() ?></td>
        <td><?= $line->getFamily() ?></td>
        <td><?= $line->getSubFamily() ?></td>
        <td><?= $line->getSpecie() ?></td>
    </tr>

    <?php
}
