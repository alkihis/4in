<?php

// Fonctions nécessaires à la vue dans la recherche
function generateSearchForm(string $mode = 'id', array $form_data = []) : void { ?>
    <div class="linear-nav-to-white top-float"></div>
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
                            <option disabled selected value=''>Choose pathway</option>

                            <?php 
                            // Génération des options du select en fonction des pathways dans la base de données
                            foreach ($form_data['select'] as $option) {
                                $md5 = htmlspecialchars($option, ENT_QUOTES);
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
                        echo '<h6 class="red-text">
                            You haven\'t specified any parameter. 
                            You must filter with at least one parameter.
                        </h6>';
                    } ?>

                    <div class="input-field col s12 path">
                        <select multiple data-mode="path" name="pathways[]" onchange="refreshSelect(this)">
                            <?php constructSelectAdv('path', $form_data['selected_p'] ?? [], $form_data) ?>
                        </select>
                        <label>Pathways</label>
                    </div>

                    <div class="input-field col s12 spec">
                        <select multiple data-mode="spec" name="species[]" onchange="refreshSelect(this)">
                            <?php constructSelectAdv('spec', $form_data['selected_s'] ?? [], $form_data) ?>
                        </select>
                        <label>Species</label>
                    </div>

                    <div class="clearb"></div>
                    <div class="divider divider-margin"></div>

                    <div class="very-tiny-text">Keywords</div>

                    <div class="col s12 red-text tiny-text" id='loading_block_form' style="display: none;">
                        Loading keywords, please wait...
                    </div>

                    <div class='col s12 chips chips-autocomplete' id="chip_container" style="margin-bottom: 20px; margin-top: 10px;">
                        <input type='text' style="width: 100% !important" autocomplete='off' name="global_chip" id="global_chip">
                    </div>
                    <input type='hidden' name="global" id="global">

                    <div class="clearb"></div>

                    <script>
                        var dat = [
                            <?php if (isset($_GET['global'])) { // Si jamais on a déjà des keywords définis
                                $words = [];
                                preg_match_all('/"(.*?)"/um', $_GET['global'], $words);
                                // On les récupère

                                if (!empty($words) && !empty($words[1])) {
                                    foreach ($words[1] as $e) { // Pour chaque mot défini
                                        if ($e === "") continue;
    
                                        $t = addcslashes($e, "'");
                                        echo "{tag: '$t'},"; // On écrit les tags dispos dans le tableau d'initialisation
                                    }
                                }
                            } ?>
                        ];

                        $(function () { initGlobalSearchForm(dat); });
                    </script>

                    <div style="margin-left: 10px; margin-bottom: 15px;">
                        Search keyword in
                    </div>
                    <div class="row col s12">
                        <label class="col s12 l2">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['names']) ? 'checked' : '') ?> name="names">
                            <span>Names</span>
                        </label>
                        <label class="col s12 l2">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['fnames']) ? 'checked' : '') ?> name="fnames">
                            <span>Fullnames</span>
                        </label>
                        <label class="col s12 l2">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['ids']) ? 'checked' : '') ?> name="ids">
                            <span>Identifiers</span>
                        </label>
                        <label class="col s12 l2">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['family']) ? 'checked' : '') ?> name="family">
                            <span>Families</span>
                        </label>
                        <label class="col s12 l2">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['subfamily']) ? 'checked' : '') ?> name="subfamily">
                            <span>Subfamilies</span>
                        </label>
                        <label class="col s12 l2">
                            <input type="checkbox" class="filled-in" <?= (isset($_GET['functions']) ? 'checked' : '') ?> name="functions">
                            <span>Roles</span>
                        </label>
                    </div>
                <?php }
                
                if ($mode !== 'pathway') { ?>
                    <label class="col s12 l2 left tooltipped" data-tooltip="Entered query must exactly match field data">
                        <input type="checkbox" value='1' <?= (isset($_GET['exact_query']) ? 'checked' : '') ?> 
                            name="exact_query">
                        <span>Exact query</span>
                    </label>
                <?php } ?>

                <button type='submit' id='submit_btn' class='btn-flat btn-perso right blue-text'>Search</button>
                <div class='clearb'></div>
            </form>
        </div>
    </div>
    </div>
    </div>

    <script>
        $(function() {$('.tooltipped').tooltip();});
    </script>
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
    $max_res = null;

    // Si jamais on doit limiter, on initialise $max_res
    if (LIMIT_SEARCH_RESULTS && getLoggedUserLevel() < LIMIT_SEARCH_LEVEL) {
        if (count($res) > LIMIT_SEARCH_NUMBER) {
            $max_res = LIMIT_SEARCH_NUMBER;
        }
    }
    
    ?>
    <div class='container'>
        <div class='row'>
            <div class='col s12'>
                <?php 
                if ($max_res) {
                    echo "<h5 class='red-text'>Your search has too many hits.</h5>";
                    echo "<h6>Data may have been truncated. Please refine your search.</h6>";
                }
                
                if (empty($res)) { ?>
                    <h4 class='red-text h45 medium-light-text header'>No gene has matched your search</h4>
                    <h6 class='black-text medium-light-text header' style="margin-bottom: 50px;">Please check search terms.</h6>
                <?php } else { ?>
                    <h5 class="medium-light-text"><?= $max_res ?? count($res) ?> result<?= ($max_res ?? count($res)) > 1 ? 's' : '' ?></h5>

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
                        <tbody id="tbody_sort">
                            <?php  
                            if ($max_res) {
                                $max_res = LIMIT_SEARCH_NUMBER;
                                $count = count($res);
                                if ($max_res > $count) {
                                    $max_res = $count;
                                }

                                for ($i = 0; $i < $max_res; $i++) {
                                    generateArrayLine($res[$i], $i, 100);
                                }
                            }
                            else {
                                foreach ($res as $key => $gene) {
                                    generateArrayLine($gene, $key, 100);
                                }
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
                <div data-count="0" id='total_count_popup' class='grey-text dl-count-popup darken-4 left'>
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
            initScrollFireSegments();

            sortTable('search_table', 100);
        });
    </script>

    <script src="/js/jquery.scrollfire.min.js"></script>
    <script src="/js/jquery.sortElements.js"></script>

    <?php
}

function generateArrayLine(Gene $line, int $position, int $interval) : void { 
    if ($position % $interval === 0 && $position !== 0) {
        echo  '<tr class="segment" data-next-segment="' . floor($position / $interval) . '"></tr>';
    } 
    ?>
    <tr class="segment-container" data-segment="<?= floor($position / $interval) ?>" 
        <?= ($position >= $interval ? 'style="display: none;"' : '') ?>>
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
            $gene_link = getLinkForId($line->getID(), $line->getSpecie(), $line->getAlias());
            if ($gene_link && $line->hasLink()) { ?>
                <a href="<?= $gene_link ?>" target="_blank" title="View in external database">
                    <i class="material-icons link-external-search">launch</i>
                </a>
            <?php } ?>
        </td>
    </tr>

    <?php
}
