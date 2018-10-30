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

    if (isset($_GET['id']) && is_string($_GET['id']) && strlen($_GET['id']) > 0) {
        $r['form_data'] = [];
        $r['form_data']['id'] = htmlspecialchars($_GET['id'], ENT_QUOTES);

        global $sql;
        // Recherche de l'identifiant dans la base de données
        $id = mysqli_real_escape_string($sql, $_GET['id']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, a.linkable, a.alias,
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
             FROM Pathways p 
             WHERE g.id = p.id) as pathways,
            (CASE 
                WHEN a.sequence_adn IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_adn,
            (CASE 
                WHEN a.sequence_pro IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_pro
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE a.gene_id LIKE '$id%'
        GROUP BY a.gene_id, g.id ORDER BY g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        $r['results'] = [];

        if (mysqli_num_rows($q)) { // Il y a un identifiant trouvé, on le récupère
            while ($row = mysqli_fetch_assoc($q)) {
                // results empêche la génération du formulaire de recherche,
                // et affiche les résultats à la page

                // Filtre les gènes protégés
                if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isUserLogged()) {
                    // Si le génome est protégé, on l'insère pas dans le tableau
                    continue;
                }

                $r['results'][] = new GeneObject($row);
            }            
        }
    }

    return $r;
}

function showSearchById(array $data) : void {
    generateSearchForm('id', $data['form_data'] ?? []);

    if (isset($data['results'])) { // résultats : tableau de résultat à générer
        generateSearchResultsArray($data['results']);
    }
}

////// NAME //////
function searchByName() : array {
    $r = [];

    if (isset($_GET['name']) && is_string($_GET['name']) && $_GET['name']) {
        $r['form_data'] = [];
        $r['form_data']['name'] = htmlspecialchars($_GET['name'], ENT_QUOTES);

        global $sql;
        // Recherche du nom dans la base de données
        $name = mysqli_real_escape_string($sql, $_GET['name']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, a.linkable, a.alias,
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
             FROM Pathways p 
             WHERE g.id = p.id) as pathways,
            (CASE 
                WHEN a.sequence_adn IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_adn,
            (CASE 
                WHEN a.sequence_pro IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_pro
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE g.gene_name LIKE '$name%'
        GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        $r['results'] = [];

        if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
            while($row = mysqli_fetch_assoc($q)) { // Il peut y avoir plusieurs occurences, on met ça dans une boucle
                // Filtre les gènes protégés
                if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isUserLogged()) {
                    // Si le génome est protégé, on l'insère pas dans le tableau
                    continue;
                }

                $r['results'][] = new GeneObject($row);
            } 
            // results empêche la génération du formulaire de recherche,
            // et affiche les résultats à la page
        }
    }

    return $r;
}

function showSearchByName(array $data) : void {
    generateSearchForm('name', $data['form_data'] ?? []);

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

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, a.linkable, a.alias,
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
            FROM Pathways p 
            WHERE g.id = p.id) as pathways,
            (CASE 
                WHEN a.sequence_adn IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_adn,
            (CASE 
                WHEN a.sequence_pro IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_pro
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        JOIN Pathways pa ON pa.id=g.id
        WHERE MD5(pa.pathway)='$pathway'
        GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        $r['results'] = [];

        if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
            while($row = mysqli_fetch_assoc($q)) {
                // Filtre les gènes protégés
                if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isUserLogged()) {
                    // Si le génome est protégé, on l'insère pas dans le tableau
                    continue;
                }

                $r['results'][] = new GeneObject($row);
            } 
            // results empêche la génération du formulaire de recherche,
            // et affiche les résultats à la page
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
    $r = [];

    global $sql;
    // Récupération des pathways disponibles
    $path = mysqli_query($sql, "SELECT DISTINCT pathway FROM Pathways;");

    $r['form_data']['pathways'] = [];
    while ($row = mysqli_fetch_assoc($path)) {
        $r['form_data']['pathways'][] = $row['pathway'];
    }

    // Récuparation des espèces
    $r['form_data']['species'] = array_keys(SPECIE_TO_NAME);
    if (LIMIT_GENOMES && !isUserLogged()) { 
        // Si les espèces sont protégées et que l'utilisateur n'est pas connecté, on limite les espèces
        $r['form_data']['species'] = array_diff($r['form_data']['species'], getProtectedSpecies());
    }

    // ____ FORMULAIRE ____
    if (isset($_GET['global']) && is_string($_GET['global'])) {
        $r['form_data']['global'] = htmlspecialchars($_GET['global'], ENT_QUOTES);

        // ___ PATHWAYS ____
        $selected_pathways = [];
        $r['form_data']['selected_p'] = [];
        if (isset($_GET['pathways']) && is_array($_GET['pathways'])) {
            foreach ($_GET['pathways'] as $p) {
                if ($p !== 'all') // Enregistre uniquement si c'est un pathway spécial
                    $selected_pathways[] = mysqli_real_escape_string($sql, $p);
                
                $r['form_data']['selected_p'][$p] = true;
            }
        }

        // ___ SPECIES ____
        $selected_species = [];

        $r['form_data']['selected_s'] = [];
        if (isset($_GET['species']) && is_array($_GET['species'])) {
            foreach ($_GET['species'] as $p) {
                if ($p !== 'all') // Enregistre uniquement si c'est un pathway spécial
                    $selected_species[] = mysqli_real_escape_string($sql, $p);
                
                $r['form_data']['selected_s'][$p] = true;
            }
        }

        // ___ KEYWORDS ____
        // Recherche du nom dans la base de données
        $global = preg_split("/\s+/", $_GET['global']);

        $query='';
        foreach ($global as $word) {
            $word = trim($word);

            if ($word === "")
                continue;

            $query = makeAdvancedQuery($word, $query);
        }
        
        if ($query) { 
            // Si jamais on a écrit une requête, on l'entoure de parenthèses pour pouvoir y
            // ajouter des composantes
            $query = "($query)";
        }

        // ___ PATHWAYS TREATEMENT ____
        if (!empty($selected_pathways)) {
            $path_q = '';

            if ($query) {
                $query .= " AND ";
            }

            $query .= '(';

            foreach ($selected_pathways as $p) {
                if ($path_q !== '') {
                    $path_q .= " OR ";
                }
                
                $path_q .= "pa.pathway = '$p'";
            }

            $query .= $path_q . ')';
        }

        // ___ SPECIES TREATEMENT ____
        if (!empty($selected_species)) {
            $spec_q = '';

            if ($query) {
                $query .= " AND ";
            }

            $query .= '(';

            foreach ($selected_species as $p) {
                if ($spec_q !== '') {
                    $spec_q .= " OR ";
                }
                
                $spec_q .= "a.specie = '$p'";
            }

            $query .= $spec_q . ')';
        }

        // ____ FINAL QUERY ____
        if ($query) {
            $finalquery = "SELECT g.*, a.gene_id, a.specie, a.linkable, a.alias, 
                (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
                FROM Pathways p 
                WHERE g.id = p.id) as pathways,
            (CASE 
                WHEN a.sequence_adn IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_adn,
            (CASE 
                WHEN a.sequence_pro IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_pro
            FROM GeneAssociations a 
            JOIN Gene g ON a.id=g.id
            JOIN Pathways pa ON pa.id=g.id
            WHERE $query
            GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie";

            $q = mysqli_query($sql, $finalquery);

            if (!$q) {
                throw new UnexpectedValueException("SQL request failed");
            }

            if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
                while($row = mysqli_fetch_assoc($q)) { // Il peut y avoir plusieurs occurences, on met ça dans une boucle
                    if (LIMIT_GENOMES && !isUserLogged() && isProtectedSpecie($row['specie'])) {
                        continue;
                    }

                    $r['results'][] = new GeneObject($row);
                } 
                // results empêche la génération du formulaire de recherche,
                // et affiche les résultats à la page
            }
            else {
                $r['results'] = [];
            }
        }
        else {
            $r['form_data']['empty_search'] = true;
        }
    }
    else {
        $_GET['family'] = $_GET['names'] = $_GET['subfamily'] = $_GET['ids'] = $_GET['functions'] = true;
    }

    return $r;
}

function makeAdvancedQuery(string $word, string $query) : string {
    global $sql;
    $word = mysqli_real_escape_string($sql, $word);
    $word = addcslashes($word, '%_');

    if (isset($_GET['names'])) {
        if ($query != '') {
            $query=$query . " OR g.gene_name LIKE '$word%'";
        }
        else {
            $query=$query . "g.gene_name LIKE '$word%'";
        }
    }
    if (isset($_GET['ids'])) {
        if ($query != '') {
            $query=$query . " OR a.gene_id LIKE '$word%'";
        }
        else {
            $query=$query . "a.gene_id LIKE '$word%'";
        }
    }
    if (isset($_GET['family'])) {
        if ($query != '') {
            $query .= " OR ";
        }

        $query .= "g.family LIKE '$word%'";
    }
    if (isset($_GET['subfamily'])) {
        if ($query != '') {
            $query .= " OR ";
        }

        $query .= "g.subfamily LIKE '$word%'";
    }
    if (isset($_GET['functions'])) {
        if ($query != '') {
            $query=$query . " OR g.func LIKE '$word%'";
        }
        else {
            $query=$query . "g.func LIKE '$word%'";
        }
    }
    return $query;
}


function showGlobalSearch(array $data) : void {
    generateSearchForm('global', $data['form_data'] ?? []);

    if (isset($data['results'])) {
        generateSearchResultsArray($data['results']);
    }
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
                <?php }
                else if ($mode === 'global') {

                    if (isset($form_data['empty_search'])) {
                        echo '<h6 class="red-text">You haven\'t specified any parameter. You must filter with at least one parameter.</h6>';
                    } ?>

                    <div class="input-field col s12">
                        <select multiple data-mode="path" name="pathways[]" onchange="refreshSelect(this)">
                            <?php constructSelectAdv('path', $form_data['selected_p'] ?? [], $form_data) ?>
                        </select>
                        <label>Pathways</label>
                    </div>

                    <div class="input-field col s12">
                        <select multiple data-mode="spec" name="species[]" onchange="refreshSelect(this)">
                            <?php constructSelectAdv('spec', $form_data['selected_s'] ?? [], $form_data) ?>
                        </select>
                        <label>Species</label>
                    </div>

                    <div class="clearb"></div>
                    <div class="divider divider-margin"></div>

                    <div class='input-field col s12' style="margin-bottom: 20px;">
                        <i class="material-icons prefix">assignment</i>
                        <input type='text' autocomplete='off' name="global" id="global" 
                            value='<?= $form_data['global'] ?? '' ?>'>
                        <label for='global'>Keywords</label>
                    </div>
                    <div class="margin-adv-search-left" style="margin-bottom: 15px;">
                        Search in
                    </div>
                    <div>
                        <label class="margin-adv-search-left">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['names']) ? 'checked' : '') ?> name="names" />
                            <span>Names</span>
                        </label>
                        <label class="margin-adv-search-left">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['ids']) ? 'checked' : '') ?> name="ids" />
                            <span>IDs</span>
                        </label>
                        <label class="margin-adv-search-left">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['family']) ? 'checked' : '') ?> name="family" />
                            <span>Family</span>
                        </label>
                        <label class="margin-adv-search-left">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['subfamily']) ? 'checked' : '') ?> name="subfamily" />
                            <span>Subfamily</span>
                        </label>
                        <label class="margin-adv-search-left">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['functions']) ? 'checked' : '') ?> name="functions" />
                            <span>Role</span>
                        </label>
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

function constructSelectAdv(string $mode, array $options, array $form_data) { 
    $m = ($mode === 'spec' ? 'species' : 'pathways');

    echo "<option class='all_option' data-mode='$mode' value='all' ";

    if (empty($options) || isset($options['all'])) {
        echo "data-only-one='true' selected";
    }
    else {
        echo "data-only-one=''";
    }

    echo ">All $m</option>";

    $m = ($mode === 'spec' ? 'species' : 'pathways');
    foreach ($form_data[$m] as $p) {
        $spec = htmlspecialchars($p, ENT_QUOTES);
        echo "<option value='$spec' ". (isset($options[$spec]) ? 'selected' : '') .">$spec</option>";
    }
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

                <div class='download-results col s12'>
                    <div class='col s6'>
                        <a href='#!' class='btn-flat btn-perso purple-text right' 
                            onclick="downloadCheckedSequences('adn', true);">
                            <i class='material-icons left'>file_download</i>FASTA sequences (DNA)
                        </a>
                    </div>

                    <div class='col s6'>
                        <a href='#!' class='btn-flat btn-perso blue-text left' 
                            onclick="downloadCheckedSequences('pro', true);">
                            <i class='material-icons left'>file_download</i>FASTA sequences (Protein)
                        </a>
                    </div>
                    
                    <div class='clearb'></div>
                </div>
                <table id='search_table'>
                    <thead>
                        <tr>
                            <th></th>
                            <th class='pointer sortable'>Gene ID</th>
                            <th class='pointer sortable'>Name</th>
                            <th class='pointer sortable'>Role</th>
                            <th class='pointer sortable'>Pathway</th>
                            <th class='pointer sortable'>Fullname</th>
                            <th class='pointer sortable'>Family</th>
                            <th class='pointer sortable'>Subfamily</th>
                            <th class='pointer sortable'>Specie</th>
                            <th></th>
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
                <a href='#!' class='btn-flat btn-perso green-text left' onclick="checkAllPageBoxes(true)">Check all</a>
                <a href='#!' class='btn-flat btn-perso red-text left' onclick="checkAllPageBoxes(false)">Uncheck all</a>
                <div href='#!' data-count="0" id='total_count_popup' class='grey-text dl-count-popup darken-4 left'>
                    <span id='count_popup'>0</span> selected
                </div>
                <a href='#!' class='btn-flat btn-perso blue-text right' onclick="downloadCheckedSequences('pro')">
                    <i class='material-icons left'>file_download</i>Protein
                </a>
                <a href='#!' class='btn-flat btn-perso purple-text right' onclick="downloadCheckedSequences('adn')">
                    <i class='material-icons left'>file_download</i>DNA
                </a>
                <div class='clearb'></div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            initCheckboxes();

            sortTable('search_table');
        });
    </script>

    <?php
}

function generateArrayLine(GeneObject $line) : void { ?>
    <tr>
        <td>    
            <label>
                <input type="checkbox" class="filled-in 
                    <?= ($line->isSequenceADN() || $line->isSequenceProt() ? 'chk-srch"' : '" disabled') ?> data-id="<?= $line->getID() ?>">
                <span class="checkbox-search"></span>
            </label>
        </td>
        <td><a href='/gene/<?= $line->getID() ?>' target='_blank'><?= $line->getID() ?></a></td>
        <td><?= $line->getName() ?></td>
        <td><?= $line->getFunction() ?></td>
        <td><?= implode(', ', $line->getPathways()) ?></td>
        <td><?= $line->getFullName() ?></td>
        <td><?= $line->getFamily() ?></td>
        <td><?= $line->getSubFamily() ?></td>
        <td><?= $line->getSpecie() ?></td>
        <td>
            <?php 
            if (getLinkForId($line->getID(), $line->getSpecie(), $line->getAlias()) && $line->hasLink()) {
                echo '<a href="' . getLinkForId($line->getID(), $line->getSpecie(), $line->getAlias()) . 
                    '" target="_blank" title="View in external database">
                        <i class="material-icons link-external-search">launch</i>
                    </a>';
            }
            ?>
        </td>
    </tr>

    <?php
}
