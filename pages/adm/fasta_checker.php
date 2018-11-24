<?php

function getFileFastaSampleData(string $filename) : ?array {
    // Ouvre le fichier à analyser
    $h = fopen($filename, "r");
    global $sql;

    // Si le fichier n'existe pas
    if (!$h) {
        return ['file' => basename($filename), 'id' => null];
    }

    // Tant qu'on a pas trouvé un ID valide dans la base, on avance
    $end = true;
    while ($end && !feof($h)) {
        $line = fgets($h);
        $line = trim($line);

        if (strpos($line, '>') !== false) {
            $s = substr($line, 1);

            $explo = preg_split("/\s/", $s);

            $id = trim($explo[0]);

            $id_e = mysqli_real_escape_string($sql, $id);
        
            $q = mysqli_query($sql, "SELECT * FROM GeneAssociations WHERE gene_id LIKE '$id_e%' OR alias='$id_e';");
            $end = !((bool)mysqli_num_rows($q));
        }
    }

    fclose($h);

    // On a trouvé un ID qui existe, on le marque comme OK
    if (isset($id) && $id) {
        return ['file' => basename($filename), 'id' => $id, 'exists' => !$end];
    }

    return ['file' => basename($filename), 'id' => null];
}

function checkerController() : array {
    $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_ADN_DIR . '*');
    $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_PRO_DIR . '*');

    $data = ['active_page' => 'checker'];

    $data['files'] = [];
    $data['files']['adn'] = [];
    $data['files']['pro'] = [];

    foreach ($files['adn'] as $f) {
        $data['files']['adn'][] = ['name' => basename($f), 'size' => round(filesize($f) / (1024*1024), 2), 'date' => filemtime($f)];
    }
    foreach ($files['pro'] as $f) {
        $data['files']['pro'][] = ['name' => basename($f), 'size' => round(filesize($f) / (1024*1024), 2), 'date' => filemtime($f)];
    }

    if (isset($_FILES['fasta']) && $_FILES['fasta']['size']) {
        $name = $_FILES['fasta']['tmp_name'];

        $infos = getFileFastaSampleData($name);
        $infos['file'] = $_FILES['fasta']['name'];

        $data['results']['adn'][] = $infos;
        $data['results']['pro'] = [];
    }
    else if (isset($_GET['check'])) {
        foreach ($files['adn'] as $a) {
            $data['results']['adn'][] = getFileFastaSampleData($a);
        }
        foreach ($files['pro'] as $p) {
            $data['results']['pro'][] = getFileFastaSampleData($p);
        }
    }

    return $data;
}

function checkerView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">

            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    In order to use FASTA file to update sequences informations, the gene ID or the alias in the fasta 
                    sequence comment MUST be the first element of the comment line (after the &gt;), and be
                    separated from other elements by a non-writable character (space, tabulation, form-feed...).
                    This tool can check if files are correctly formatted, and give example of ID provided in FASTA file.<br><br>

                    In the FASTA, if specified ID is the gene ID (not the alias!), comparaison will be lazy (if ID in the database
                    begin by given ID in FASTA, sequence will be imported).<br> 
                    If specified ID is the alias, comparaison will be
                    <span class="underline">strict</span>, specified ID must be, character by character, equals to database alias.
                    <br><br>
                    <span class="underline">Example:</span><br>
                    For:<br>
                    Database: gene_id BM00000101-RA, alias NULL<br>
                    FASTA: BM00000101<br>
                    Sequence will be imported.<br><br>

                    For:<br>
                    Database: gene_id Cflo_AR_10, alias CFLO130301-TA<br>
                    FASTA: CFLO130301<br>
                    Sequence will <span class="underline">NOT</span> be imported.
                </p>
            </div>

            <?php if (isset($data['results'])) { ?>
                <h4>Results</h4>
                <p>
                <?php foreach ($data['results']['adn'] as $r) { 
                    if ($r['id'] === null) {
                        echo "File {$r['file']} is unreadable or empty.";
                    }
                    else {
                        echo "{$r['file']} : {$r['id']} ";
                        if ($r['exists']) {
                            echo '(Some IDs exists in database)';
                        }
                        else {
                            echo '(<span class="red-text">None of the IDs of the file does exists in database</span>)';
                        }
                    }
                    echo '<br>';
                } 
                echo '</p><p>';

                foreach ($data['results']['pro'] as $r) { 
                    if ($r['id'] === null) {
                        echo "File {$r['file']} is unreadable or empty.";
                    }
                    else {
                        echo "{$r['file']} : {$r['id']} ";
                        if ($r['exists']) {
                            echo '(Some IDs exists in database)';
                        }
                        else {
                            echo '(<span class="red-text">None of the IDs of the file does exists in database</span>)';
                        }
                    }
                    echo '<br>';
                } 
                echo '</p>';
            } ?>

            <a class="btn extend btn-personal blue lighten-1 center-block" href="?check=true">
                Check all uploaded files
            </a>

            <form method="post" enctype="multipart/form-data" action="#">
                <div class='card card-border' style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="card-content">
                        <h6>File to check</h6>
                        <div class="row">
                            <div class="file-field input-field col s12">
                                <div class="btn light-blue darken-1">
                                    <span>FASTA file</span>
                                    <input name="fasta" accept="application/fasta" required type="file">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                        </div>
                        <button class="btn-flat btn-perso green-text right" type="submit" name="go">
                            Check file
                        </button>
                        <div class="clearb"></div>
                    </div>
                </div>
            </form>

            <div class="clearb"></div>

            <div class="divider divider-margin"></div>

            <h5 class="underline" style='margin-bottom: -25px;'>Uploaded FASTA files</h5>
        </div>
    </div>

    <?php 
    showFastaFiles($data['files'], false);
}
