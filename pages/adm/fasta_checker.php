<?php

function getFileFastaSampleData(string $filename) : ?array {
    $h = fopen($filename, "r");
    global $sql;

    if (!$h) {
        return ['file' => basename($filename), 'id' => null];
    }

    $end = true;
    while ($end && !feof($h)) {
        $line = fgets($h);
        $line = trim($line);

        if (strpos($line, '>') !== false) {
            $s = substr($line, 1);

            $explo = preg_split("/\s/", $s);

            $id = trim($explo[0]);

            $id_e = mysqli_real_escape_string($sql, $id);
        
            $q = mysqli_query($sql, "SELECT * FROM GeneAssociations WHERE gene_id='$id_e';");
            $end = !((bool)mysqli_num_rows($q));
        }
    }

    fclose($h);

    if ($id) {
        return ['file' => basename($filename), 'id' => $id, 'exists' => !$end];
    }
    else {
        return ['file' => basename($filename), 'id' => null];
    }
}

function checkerController() : array {
    $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/adn/*');
    $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/pro/*');

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
                    In order to use FASTA file to update sequences informations, the gene ID in the fasta 
                    sequence comment MUST be the first element of the comment line (after the &gt;), and be
                    separated from other elements by a non-writable character (space, tabulation, form-feed...).<br>
                    This tool can check if files are correctly formatted, and give example of ID provided in FASTA file.<br>
                    Finally, the tool checks if a similar ID exists in the database.
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
                        <div class="clearb"></div>
                    </div>
                </div>

                <div class="col s6">
                    <button class="btn btn-personal green lighten-1 center-block" type="submit" name="go">
                        Check file in input
                    </button>
                </div>
            </form>

            <div class="col s6">
                <form method="get" action="#">
                    <button class="btn btn-personal blue lighten-2 center-block" type="submit" name="check">
                        Check all uploaded files
                    </button>
                </form>
            </div>

            <div class="clearb"></div>

            <div class="divider divider-margin"></div>

            <h5 class="underline" style='margin-bottom: -25px;'>Uploaded FASTA files</h5>
        </div>
    </div>

    <?php showFastaFiles($data['files'], false); ?>
    <?php
}
