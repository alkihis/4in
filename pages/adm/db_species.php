<?php

function databaseSpeciesController() : array {
    $data = ['active_page' => 'db_species'];

    global $sql;

    $data['species'] = SPECIE_TO_NAME;
    $data['ord_species'] = getOrderedSpecies();

    if (isset($_POST['new_specie'], $_POST['specie_alias']) && is_string($_POST['new_specie']) && is_string($_POST['specie_alias'])) {
        $new_specie = trim($_POST['new_specie']);
        $alias = trim($_POST['specie_alias']);

        if ($new_specie) {
            // Vérifier si l'espèce est déjà définie dans species
            // Vérifier si l'espèce est déjà présente dans le tableau ordonné
            $mod = false;

            if (!isset($data['species'][$new_specie])) { // Elle est pas présente dans les espèces
                if ($alias) {
                    $data['species'][$new_specie] = $alias;
                }
                else {
                    $data['species'][$new_specie] = null;
                }
                $mod = true;
            }

            if (!in_array($new_specie, $data['ord_species'], true)) { // Pas dans le tableau ordonné
                $data['ord_species'][] = $new_specie;
                $mod = true;
            }

            if ($mod) {
                saveSpecies($data['species'], $data['ord_species']);
                $data['modified'] = true;
            }
            else {
                $data['equals'] = true;
            }
        }
    }
    else if (isset($_POST['order']) && is_string($_POST['order'])) {
        $data['ord_species'] = explode(',', $_POST['order']);
        
        if (count($data) > 0) {
            saveSpecies($data['species'], $data['ord_species']);
            $data['order_mod'] = true;
        }
        else {
            $data['empty'] = true;
        }
    }
    else if (isset($_POST['delete']) && is_string($_POST['delete'])) {
        $spec_to_delete = trim($_POST['delete']);

        if (isset($data['species'][$spec_to_delete])) {
            unset($data['species'][$spec_to_delete]);
            $mod = true;
        }

        $i = -1;
        foreach ($data['ord_species'] as $key => $s) {
            if ($s === $spec_to_delete) {
                $i = $key; break;
            }
        }

        if ($i !== -1) {
            unset($data['ord_species'][$i]);
            // Réindexation du tableau après suppression
            $data['ord_species'] = array_values($data['ord_species']);
            $mod = true;
        }

        if (isset($mod)) {
            saveSpecies($data['species'], $data['ord_species']);
            $data['deleted'] = true;
        }
        else {
            $data['not_found'] = true;
        }
    }

    return $data;
}

function databaseSpeciesView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Species supported by website database are listed here.<br>
                    You can add new supported species (plus their alias used for generating the ArthropodaCyc link) 
                    and sort the default read order of the species, when
                    the database is created for TSV file.
                </p>
            </div>

            <?php 
            if (isset($data['modified'])) {
                echo '<h5 class="green-text">The requested specie has been added in supported species.</h5>';
            }
            else if (isset($data['equals'])) {
                echo '<h5 class="red-text">Specie already exists.</h5>';
            }

            if (isset($data['deleted'])) {
                echo '<h5 class="green-text">The requested specie has been deleted from supported species.</h5>';
            }
            else if (isset($data['not_found'])) {
                echo '<h5 class="red-text">Specie not found.</h5>';
            }

            if (isset($data['order_mod'])) {
                echo '<h5 class="green-text">The default order has been saved.</h5>';
            }
            else if (isset($data['empty'])) {
                echo '<h5 class="red-text">You can\'t order if any specie is registered.</h5>';
            }
            ?>

            <div class="card card-border">
                <div class="card-content">
                    <h6 class="collapsible-ready" data-collapse="registered" data-arrow="a_registered">
                        <span class="light-blue-text pointer">Registered species</span>
                        <i class="material-icons valign-middle" id="a_registered">keyboard_arrow_down</i>
                    </h6>

                    <div class="clearb" style="margin-top: 20px;"></div>

                    <ul id="registered" class="collection">
                        <?php foreach ($data['species'] as $key => $val) { ?>
                            <li class="collection-item">
                                <div>
                                    <?= htmlspecialchars($key) . " (" . ($val ?? "no short alias") . ')' ?>
                                    <a href="#!" class="secondary-content" data-specie="<?= htmlspecialchars($key) ?>"
                                        onclick="deleteSpecie(this.dataset.specie)">
                                        <i class="material-icons red-text">delete_forever</i>
                                    </a>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>

                    <div class="clearb"></div>
                </div>
            </div>
            
            <div class="clearb"></div>

            <form method="post" action="#" id="form_spec">
                <div class="card card-border">
                    <div class="card-content">
                        <h6>Add new specie</h6>
                        <p>
                            New species will automatically be inserted at the end of the ordered species table,
                            and association between species and its alias will be registered.<br>
                            Species name and alias are both case-sensitive, please be careful !
                        </p>

                        <div class="clearb" style="margin-top: 10px;"></div>

                        <div class='input-field col s7'>
                            <input type="text" name="new_specie" id='new_specie' autocomplete="off" required>
                            <label for="new_specie">New species</label>
                        </div>
                        <div class="input-field col s5">
                            <input type='text' id='specie_alias' name='specie_alias' autocomplete="off">
                            <label for="specie_alias">Alias</label>
                        </div>

                        <div class="col s12">
                            <button type="submit" class="btn-flat right blue-text">
                                <i class="material-icons left sub">add</i> Add</button>
                        </div>

                        <div class="clearb"></div>
                    </div>
                </div>
                
                <div class="clearb"></div>
            </form>

            <div class="card card-border">
                <div class="card-content">
                    <form method="post" action="#" id="order_form">
                        <h6 class="collapsible-ready" data-collapse="ordered_s" data-arrow="a_ordered">
                            <span class="light-blue-text pointer">Ordered species</span>
                            <i class="material-icons valign-middle" id="a_ordered">keyboard_arrow_down</i>
                        </h6>

                        <div id="ordered_s">
                            <p>
                                Drag and drop to reorder species. Order reads from top to bottom.
                            </p>

                            <div class="clearb" style="margin-top: 20px;"></div>

                            <ul class="collection" id="specie_order">
                                <?php foreach ($data['ord_species'] as $val) { ?>
                                    <li class="collection-item collection-specie" data-specie="<?= htmlspecialchars($val) ?>">
                                        <div>
                                            <?= htmlspecialchars($val) ?>
                                            <a href="#!" class="secondary-content no-user-click">
                                                <i class="material-icons blue-text">swap_vert</i>
                                            </a>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>

                            <div class="clearb"></div>

                            <div class="divider divider-margin"></div>

                            <button type="button" onclick="sendSpecieOrder()" class="btn-flat btn-perso green-text right">
                                Save order
                            </button>

                            <input type="hidden" name="order" id="hidden_order">

                            <div class="clearb"></div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="clearb"></div>
            <div class="hide" id="prec_order"><?= implode(', ', $data['ord_species']) ?></div>
        </div>
    </div>

    <script>
        $(function () {
            var el = document.getElementById('specie_order');
            Sortable.create(el);

            $('.collapsible-ready').on('click', function() {
                var d = document.getElementById(this.dataset.collapse);

                if (d) {
                    var a = this.dataset.arrow ? this.dataset.arrow : null;
                    if ($(d).is(':visible')) {
                        $(d).slideUp(300);

                        if (a) {
                            document.getElementById(a).classList.add('rotate');
                        }
                    }
                    else {
                        $(d).slideDown(300);

                        if (a) {
                            document.getElementById(a).classList.remove('rotate');
                        }
                    }
                }
            });
        });
    </script>
    <?php
}
