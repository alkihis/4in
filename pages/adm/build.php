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

    // Traitement si l'utilisateur a demandé de construire la base de données
    /* else if (isset($_POST['construct']) && 
        file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $_POST['construct']) && 
        !is_dir($_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $_POST['construct'])) {

        $file_selected = $_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $_POST['construct'];

        emptyTables();
        explodeFile($file_selected, false);

        $data['construction'] = true;
    } */

    $data['files'] = [];

    foreach ($files as $f) {
        $data['files'][] = ['name' => basename($f), 'size' => round(filesize($f) / 1024, 2), 'date' => filemtime($f)];
    }

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

                    <button id="go_db" class="btn-flat btn-perso green-text darken-1 right">
                        Build database
                    </button>

                    <script>
                        $(document).ready(function() {
                            var modal = document.getElementById('modal-admin');
                            $(modal).modal();

                            $('#go_db').on('click', function() {
                                $(modal).modal('open');
                                launchDatabaseBuild(document.getElementById('construct').value);
                            });
                        });
                    </script>


                    <form method="post" action="#">
                        <input type="hidden" name="erase" value="true">
                        <button name="go" type="submit" 
                            onclick="return confirm('Are you sure you want to wipe the genome database ?')" 
                            class="btn-flat red-text btn-perso darken-1 left">
                            Clear genome database
                        </button>
                    </form>
                    <div class="clearb"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row no-margin-bottom">
        <div class="col s12">
            <h5>Currently uploaded database files</h5>
        </div>
    </div>

    <?php showMappingFiles($data['files'], false);
}

function buildBlastController() : array {
    global $sql;
    $data = ['active_page' => 'build_blast'];

    $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/adn/*');
    $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/pro/*');

    // Traitement si l'utilisateur a demandé de supprimer les bases BLAST
    if (isset($_POST['erase'])) {
        // mysqli_query($sql, "UPDATE GeneAssociations SET sequence_adn=NULL;");
        // mysqli_query($sql, "UPDATE GeneAssociations SET sequence_pro=NULL;");
        clearBlastDatabase();

        $data['erased'] = true;
    }

    // Traitement si l'utilisateur a demandé de construire la base de données
    /* else if (isset($_POST['construct'])) {
        readAllFastaFiles();

        clearBlastDatabase();
        makeAllBlastDB();

        $data['construction'] = true;
    } */

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
                    you can build BLAST database and register sequences to the website SQL Base by clicking
                    "build BLAST".<br>
                    It will <span class='underline'>NOT</span> delete uploaded sequences files.
                </p>
            </div>

            <?php if (isset($data['construction'])) {
                echo '<h5 class="green-text">Database is successfully updated with sequence informations 
                    and BLAST database has been built.</h5>';
            } 
            if (isset($data['erased'])) {
                echo '<h5 class="red-text">BLAST databases has been wiped.</h5>';
            }
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col s6">
            <form method="post" action="#">
                <input type="hidden" name="erase" value="true">
                <button name="go" type="submit" class="btn btn-personal red darken-1 center-block">
                    Clear BLAST databases
                </button>
            </form>
        </div>
        <div class="col s6">
            <button id="construct_btn" class="btn btn-personal green darken-1 center-block">
                Build BLAST and sequence databases
            </button>

            <script>
                $(document).ready(function () {
                    var modal = document.getElementById('modal-admin');
                    $(modal).modal();

                    $.get('/api/tools/get_all_fasta_files.php', {}, function(data) {

                        $('#construct_btn').on('click', function () {
                            $(modal).modal('open');

                            launchFastaBuild(JSON.parse(data));
                        });
                    });
                });
            </script>
        </div>
    </div>

    <?php showFastaFiles($data['files'], false); ?>

    <?php
}

function readAllFastaFiles($delete = false) : void {
    global $sql;
        
    if ($delete) {
        // Construction séquences dans la BDD SQL
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_adn=NULL;");
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_pro=NULL;");
    }
    
    $adn = glob('fasta/adn/*');
    $pro = glob('fasta/pro/*');

    set_time_limit(0);

    foreach($adn as $a) {
        loadFasta($a, 'adn');
    }
    foreach($pro as $a) {
        loadFasta($a, 'pro');
    }
}
