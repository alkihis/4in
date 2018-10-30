<?php

function speciesController() : array {
    $data = ['active_page' => 'species'];

    global $sql;

    // Récupération des espèces existantes

    // TROP CONSOMMATEUR : désactivé
    /* 
    $q = mysqli_query($sql, "SELECT DISTINCT specie FROM GeneAssociations");

    while ($row = mysqli_fetch_assoc($q)) {
        $data['all_species'][] = $row['specie'];
    }
    */

    // Toutes les espèces sont normalement dans SPECIE_TO_NAME (en clé)
    $data['all_species'] = array_keys(SPECIE_TO_NAME);

    $data['species'] = getProtectedSpecies();

    if (isset($_POST['species']) && is_array($_POST['species'])) {
        // Définit l'unicité (normalement déjà faite, mais on sait jamais)
        $species = array_unique($_POST['species']);
        // Vérification que l'espèce existe

        $max = count($species);

        for ($i = 0; $i < $max; $i++) {
            if (!in_array($species[$i], $data['all_species'])) {
                $data['ignored'][] = $species[$i];
                unset($species[$i]);
            }
        }

        // Réindexe le tableau si des éléments ont été supprimés
        $species = array_values($species);

        // Sauvegarde les modifications
        renewProtectedSpecies($species);

        $data['changed_species'] = true;

        // actualise
        $data['species'] = $species;
    }

    return $data;
}

function speciesView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Protected species are hidden in search, sequence download and even BLAST queries (modulo database rebuild)
                    to unidentified users.<br>
                    To show genes who belongs to protected species, you must login to the website.<br>
                    You can define here species that are protected.
                </p>
            </div>

            <?php 
            if (isset($data['reset'])) {
                echo '<h5 class="green-text">Link status has been successfully reset</h5>';
            }
            else if (isset($data['error'])) {
                echo '<h5 class="red-text">An unknown error occurred</h5>';
            }
            ?>

            <form method="post" action="#" id="form_spec">
                <div class="card card-border">
                    <div class="card-content">
                        <?php 
                        if (isset($data['changed_species'])) {
                            echo "<h5 class='green-text'>Protected species has been saved</h5>";

                            if (isset($data['ignored'])) {
                                echo '<p class="red-text darken-1">';
                                foreach($data['ignored'] as $i) {
                                    echo htmlspecialchars($i) . " has been ignored (specie does not exists in database)<br>";
                                }
                                echo '</p>';
                            }
                        }
                        
                        if (count($data['species'])) { ?>

                        <h6>Protected species</h6>
                        <p>
                            Uncheck specie(s) then confirm to delete protection.<br><br>
                        </p>

                        <?php } ?>

                        <div id="multiple_species">
                            <?php foreach($data['species'] as $s) { ?>
                                <p>
                                    <label>
                                        <input type="checkbox" class="chk-spe" name="species[]" 
                                            checked value="<?= htmlspecialchars($s) ?>">
                                        <span><?= htmlspecialchars($s) ?></span>
                                    </label>
                                    <br>
                                </p>
                            <?php } ?>
                        </div>

                        <div class="clearb" style="margin-top: 10px;"></div>

                        <div class='input-field col s7'>
                            <input type="text" id="new_specie" autocomplete="off">
                            <label for="new_specie">New specie</label>
                            
                        </div>
                        <div class="col s5">
                            <button type="button" style="margin-top: 25px;" class="btn-flat center-block blue-text" 
                                onclick="addSpecie($('#new_specie').val())">
                                <i class="material-icons left sub">add</i> Add specie</button>
                        </div>

                        <div class="clearb"></div>

                        <div class="divider divider-margin"></div>

                        <button type="submit" id="act_btn" class="btn-flat btn-perso green-text right">
                            Confirm and save
                        </button>

                        <div class="clearb"></div>

                        <script>
                            document.getElementById('form_spec').onkeypress = function (e) {
                                var key = e.charCode || e.keyCode || 0;     
                                if (key === 13) {
                                    e.preventDefault();
                                    addSpecie($('#new_specie').val());
                                }
                            };

                            // Autocomplétion des espèces
                            $(document).ready(function() {
                                $('#new_specie').autocomplete({
                                    data: {
                                        <?php foreach ($data['all_species'] as $s) {
                                            echo "'$s': null,";
                                        } ?>
                                    },
                                    minLength: 1,
                                    limit: 3
                                });
                            });
                        </script>
                    </div>
                </div>
                
                <div class="clearb"></div>
            </form>
        </div>
    </div>

    <?php
}
