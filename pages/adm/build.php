<?php

function buildGenomeController() : array {
    throw new NotImplementedException();
}

function buildBlastController() : array {
    global $sql;
    $data = ['active_page' => 'build_blast'];

    $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/adn/*');
    $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/pro/*');

    // Traitement si l'utilisateur a demandé de supprimer les bases BLAST
    if (isset($_POST['erase'])) {
        clearBlastDatabase();

        $data['erased'] = true;
    }

    // Traitement si l'utilisateur a demandé de construire la base de données
    else if (isset($_POST['construct'])) {
        readAllFastaFiles();
        
        clearBlastDatabase();
        makeAllBlastDB();

        $data['construction'] = true;
    }

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
                    Import first sequences files using "Import sequences files" utility. After this operation,
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
            <form method="post" action="#">
                <input type="hidden" name="construct" value="true">
                <button name="go" type="submit" class="btn btn-personal green darken-1 center-block">
                    Build BLAST and sequence databases
                </button>
            </form>
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

function clearBlastDatabase() : void {
    // Toutes les séquences ont été chargées, on construit la base BLAST
    // Effacement des anciennes
    $base = glob('ncbi/bin/base/adn_base.*');
    foreach ($base as $file) {
        unlink($file);
    }

    $base = glob($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin/base/pro_base.*');
    foreach ($base as $file) {
        unlink($file);
    }
}

function makeAllBlastDB() : void {
    // Construction des 4 bases :
    // ADN sans autorisation et complète (génomes protégés), de même protéine
    makeBlastDB('adn', true);
    makeBlastDB('adn', false);
    makeBlastDB('pro', true);
    makeBlastDB('pro', false);
}

function addLine($mode, $sequence, $current_id) : void {
    global $sql;

    // On traite la séquence en cours
    $q = mysqli_query($sql, "UPDATE GeneAssociations 
        SET $mode='$sequence' 
        WHERE gene_id LIKE '$current_id%'
        OR alias='$current_id';");
}

/**
 * loadFasta
 * 
 * Parse un fichier fasta, et l'enregistre dans la base de données
 * @param string $filename : Chemin du fichier .fasta à parser
 * @return void
 */
function loadFasta(string $filename, $mode = 'adn') : void { 
    global $sql; // importe la connexion SQL chargée avec l'appel à connectBD()

    $mode = ($mode === 'adn' ? 'sequence_adn' : 'sequence_pro');

    $h = fopen($filename, 'r'); // ouvre le fichier $filename en lecture, et stocke le pointeur-sur-fichier dans $h

    if (!$h) {
        throw new RuntimeException('Unable to open file');
    }

    $sequence = "";
    $current_id = "";

    while (!feof($h)) { // Si $h est valide et tant que le fichier n'est pas fini (feof signifie file-end-of-file)
        $line = fgets($h); // récupère une ligne du fichier

        if ($line[0] === '>') { // Commentaire, on récupère l'ID concerné
            if ($sequence !== '' && $current_id !== '') {
                addLine($mode, $sequence, $current_id);
            }

            $e = substr($line, 1);
            $current_id = mysqli_real_escape_string($sql, trim(preg_split("/\s/", trim($e))[0]));

            // ----------
            // TO DISABLE
            // ----------

            if (strpos($current_id, "|") !== false) { // Si il contient des pipes, on récupère différemment
                $id_avec_tiret_de_merde = explode("|", trim($current_id))[2];
                $id_sans_tiret_de_merde = explode("-", trim($id_avec_tiret_de_merde))[0];

                $current_id = mysqli_real_escape_string($sql, trim($id_sans_tiret_de_merde));
            }
            else if (strpos($current_id, 'BGIBMG') !== false) {
                $id_sans_tiret_de_merde = explode("-", trim($current_id))[0];

                $current_id = mysqli_real_escape_string($sql, trim($id_sans_tiret_de_merde));
            }

            // ----------
            // TO DISABLE
            // ----------

            $sequence = '';
        }
        else {
            $sequence .= trim($line);
        }
    }

    if ($sequence !== '' && $current_id !== '') {
        addLine($mode, $sequence, $current_id);
    }

    fclose($h);
}

function getAllFastaSequences(string $mode = 'adn', bool $full) : string {
    global $sql;

    $m = ($mode === 'adn' ? 'sequence_adn' : 'sequence_pro');

    $q = mysqli_query($sql, "SELECT $m, gene_id, specie FROM GeneAssociations WHERE $m IS NOT NULL;");

    $s = '';
    while ($row = mysqli_fetch_assoc($q)) {
        if (!$full && isProtectedSpecie($row['specie'])) {
            continue;
        }

        $s .= ">{$row['gene_id']}\n{$row[$m]}\n";
    }

    return $s;
}

function makeBlastDB(string $mode = 'adn', bool $full = true) {
    chdir($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin');

    $suffix = ($full ? '_full' : '');

    $temp_file = `mktemp`;
    $temp_file = trim($temp_file);

    `chmod a+a $temp_file`;

    if ($mode === 'adn') {
        $str_seq = getAllFastaSequences('adn', $full);

        // Écrit le contenu dans le fichier
        file_put_contents($temp_file, $str_seq);

        `./makeblastdb -dbtype nucl -in "$temp_file" -out base/adn_base$suffix 2>&1`;
    }
    else if ($mode === 'pro') {
        $str_seq = getAllFastaSequences('pro', $full);

        // Écrit le contenu dans le fichier
        file_put_contents($temp_file, $str_seq);

        `./makeblastdb -dbtype prot -in "$temp_file" -out base/pro_base$suffix 2>&1`;
    }
    else {
        chdir($_SERVER['DOCUMENT_ROOT']);
        throw new UnexpectedValueException('Unrecognized mode');
    }

    chdir($_SERVER['DOCUMENT_ROOT']);
    `rm -f $temp_file`;
}
