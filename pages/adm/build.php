<?php

function emptyTables() : void {
    global $sql;

    mysqli_query($sql, "DELETE FROM Gene;");
    mysqli_query($sql, "DELETE FROM GeneAssociations;");
    mysqli_query($sql, "DELETE FROM Pathways;");
    mysqli_query($sql, "ALTER TABLE Gene AUTO_INCREMENT=1;");
    mysqli_query($sql, "ALTER TABLE Pathways AUTO_INCREMENT=1;");
}

function buildGenomeController() : array {
    global $sql;
    $data = ['active_page' => 'build_genome'];

    $files = glob($_SERVER['DOCUMENT_ROOT'] . '/assets/db/*.tsv');

    // Traitement si l'utilisateur a demandé de supprimer les bases BLAST
    if (isset($_POST['erase'])) {
        emptyTables();

        $data['erased'] = true;
    }

    $data['files'] = [];

    foreach ($files as $f) {
        $data['files'][] = ['name' => basename($f), 'size' => round(filesize($f) / 1024, 2), 'date' => filemtime($f)];
    }

    $data['species'] = getOrderedSpecies();

    return $data;
}

function buildGenomeView(array $data) : void { ?>
    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Import first database file using "Import genome file" utility. After this operation,
                    you can build genome database by choosing file to use to construct and clicking
                    "build database".<br>
                    It will <span class='underline'>NOT</span> delete uploaded database files.
                </p>
            </div>

            <?php if (isset($data['construction'])) {
                echo '<h5 class="green-text">Database is successfully created with selected file.</h5>
                <h6 class="red-text">It is recommanded to do mapping then
                build BLAST database after this operation.</h6>';
            } 
            if (isset($data['erased'])) {
                echo '<h5 class="red-text">Database has been wiped.</h5>';
            }
            ?>
        </div>
    </div>

    <div class='row'>
        <div class="col s12">
            <div class="card card-border">
                <div class="card-content">
                    <?php if (count($data['files']) !== 0) { ?>
                        <div class="input-field col s12">
                            <select id="construct">
                                <?php 
                                foreach ($data['files'] as $f) {
                                    $name = htmlspecialchars($f['name'], ENT_QUOTES);

                                    echo "<option value='$name'>$name</option>";
                                }
                                ?>
                            </select>
                            <label>File to use</label>
                        </div>

                        <div class="row col s12">
                            <p>
                                <label>
                                    <input type="checkbox" id="first_line_is_text" 
                                        onchange="(this.checked ? true : $('#read_from_first').prop('checked', false)); $('#read_from_first').prop('disabled', !this.checked);" />
                                    <span>First line contains colomn name</span>
                                </label>
                            </p>

                            <p>
                                <label>
                                    <input type="checkbox" id="read_from_first" disabled />
                                    <span>Read specie name from first line</span>
                                </label>
                            </p>
                        </div>
                        
                        <a href="#!" id="go_db" class="btn-flat btn-perso green-text darken-1 right">
                            Build database
                        </a>

                        <!-- set modal build parameter -->
                        <script>
                            var btn = document.getElementById('go_db');
                            btn.onclick = function () {
                                buildGenomeDbModal(
                                    document.getElementById('construct').value,
                                    document.getElementById('first_line_is_text').checked,
                                    document.getElementById('read_from_first').checked
                                );
                            };
                        </script>
                    <?php } ?>

                    <a href="#modal_wipe" class="btn-flat red-text btn-perso darken-1 left modal-trigger">
                        Clear genome database
                    </a>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SPECIES USED FOR GENERATION: WILL BE _HIDDEN_ -->
    <div class="hide" id="collection-build">
        <ul class="collection">
            <?php foreach ($data['species'] as $s) { ?>
                <li class="collection-item collection-specie" data-specie="<?= htmlspecialchars($s) ?>">
                    <div>
                        <?= htmlspecialchars($s) ?>
                        <a href="#!" class="secondary-content" style="margin-left: 15px;" 
                            onclick="$(this.parentElement.parentElement).remove()">
                            <i class="material-icons red-text">delete_forever</i>
                        </a>
                        <a href="#!" class="secondary-content no-user-click">
                            <i class="material-icons blue-text">swap_vert</i>
                        </a>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>

    <?php if (count($data['files']) !== 0) { ?>
        <div class="row no-margin-bottom">
            <div class="col s12">
                <h5>Currently uploaded database files</h5>
            </div>
        </div>

        <?php showMappingFiles($data['files'], false);
    }
}

function buildBlastController() : array {
    global $sql;
    $data = ['active_page' => 'build_blast'];

    $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_ADN_DIR . '*');
    $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_PRO_DIR . '*');

    // Traitement si l'utilisateur a demandé de supprimer les bases BLAST
    if (isset($_POST['clear_blast']) && $_POST['clear_blast'] === 'true') {
        clearBlastDatabase();

        $data['erased'] = true;
    }

    if (isset($_POST['wipe_seq']) && $_POST['wipe_seq'] === 'true') {
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_adn=NULL, sequence_pro=NULL;");

        $data['cleared'] = true;
    }

    // Traitement si l'utilisateur a demandé de construire la base de données

    $data['files'] = [];
    $data['files']['adn'] = [];
    $data['files']['pro'] = [];

    foreach ($files['adn'] as $f) {
        $data['files']['adn'][] = ['name' => basename($f), 'size' => round(filesize($f) / (1024*1024), 2), 'date' => filemtime($f)];
    }
    foreach ($files['pro'] as $f) {
        $data['files']['pro'][] = ['name' => basename($f), 'size' => round(filesize($f) / (1024*1024), 2), 'date' => filemtime($f)];
    }

    return $data;
}

function buildBlastView(array $data) : void { ?>
    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Import first sequences files using "Import sequence files" utility. After this operation,
                    you can first register sequences to the website SQL Base by clicking
                    "Import sequences in database".<br>
                    Finally, click to "Build BLAST" to construct BLAST database based on imported sequences.<br>
                    It will <span class='underline'>NOT</span> delete uploaded sequences files.
                </p>
            </div>

            <?php
            if (isset($data['erased'])) {
                echo '<h5 class="red-text">BLAST databases has been wiped.</h5>';
            }
            if (isset($data['cleared'])) {
                echo '<h5 class="red-text">Sequences of genes has been deleted.</h5>';
            }
            ?>
        </div>
    </div>

    <div class="row no-margin-bottom">
        <div class="col s6">
            <a href="#modal_build" onclick="initAdminModalForSequenceBuild()" 
                class="btn btn-personal extend blue darken-1 center-block modal-trigger">
                Insert sequences in database
            </a>
        </div>

        <div class="col s6">
            <a onclick="initAdminModalForSequenceDelete()" href="#modal_wipe" 
                class="btn btn-personal extend orange darken-1 center-block modal-trigger">
                Delete sequences from database
            </a>
        </div>
    </div>

    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="divider divider-margin"></div>
        </div>
    </div>

    <div class="row">
        <?php if (count($data['files']['adn']) !== 0 || count($data['files']['pro']) !== 0) { ?>
            <div class="col s6">
                <a href="#modal_build" onclick="initAdminModalForBlastBuild()"
                    class="modal-trigger btn btn-personal extend green darken-1 center-block">
                    Build BLAST databases
                </a>
            </div>
        <?php } ?>

        <div class="col s6">
            <a onclick="initAdminModalForBlastDelete()" href="#modal_wipe" 
                class="btn btn-personal extend red darken-1 center-block modal-trigger">
                Clear BLAST databases
            </a>
        </div>
    </div>

    <?php showFastaFiles($data['files'], false); ?>

    <?php
}

function clearBlastDatabase() : void {
    // Toutes les séquences ont été chargées, on construit la base BLAST
    // Effacement des anciennes
    $base = glob($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin/base/adn_base*');
    foreach ($base as $file) {
        unlink($file);
    }

    $base = glob($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin/base/pro_base*');
    foreach ($base as $file) {
        unlink($file);
    }
}
