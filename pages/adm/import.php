<?php

function importGenomeController() : array {
    global $sql;
    $data = ['active_page' => 'import_genome'];

    $files = glob($_SERVER['DOCUMENT_ROOT'] . '/assets/db/*.tsv');

    $data['ord_species'] = getOrderedSpecies();

    // If delete request
    if (isset($_POST['delete']) && is_string($_POST['delete'])) {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $_POST['delete']) 
            && !is_dir($_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $_POST['delete'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $_POST['delete']);
            $data['file_deleted'] = true;
        }
        else {
            $data['file_not_found'] = true;
        }

        $files = glob($_SERVER['DOCUMENT_ROOT'] . '/assets/db/*.tsv');
    }

    // If import request
    if (isset($_FILES['database']) && $_FILES['database']['name'] && $_FILES['database']['size']) {
        $filename = $_FILES['database']['name'];
        $filesize = $_FILES['database']['size'];
        $location = $_FILES['database']['tmp_name'];
        $status = $_FILES['database']['error'];

        if ($status === UPLOAD_ERR_OK) {
            $name = findSafeName($files, $location, $filename);

            if (!$name) {
                $data['already_exists'] = true;
            }
            else {
                if (!move_uploaded_file($location, $_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $name)) {
                    $data['upload_error'] = true;
                }
    
                $files = glob($_SERVER['DOCUMENT_ROOT'] . '/assets/db/*.tsv');
                $data['upload_ok'] = true;
            }
        }
        else {
            $data['upload_error'] = true;
        }
    }

    $data['files'] = [];
    foreach ($files as $f) {
        $data['files'][] = ['name' => basename($f), 'size' => round(filesize($f) / 1024, 2), 'date' => filemtime($f)];
    }

    return $data;
}

function importBlastController() : array {
    global $sql;
    $data = ['active_page' => 'import_blast'];

    $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_ADN_DIR . '*');
    $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_PRO_DIR . '*');

    // If delete request
    if (isset($_POST['delete'], $_POST['mode']) && is_string($_POST['delete']) && is_string($_POST['mode'])) {
        $mode = $_POST['mode'] === 'adn' ? 'adn' : 'pro';

        $direct = ($mode === 'pro' ? FASTA_PRO_DIR : FASTA_ADN_DIR);

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $direct . $_POST['delete']) 
            && !is_dir($_SERVER['DOCUMENT_ROOT'] . $direct . $_POST['delete'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . $direct . $_POST['delete']);
            $data['file_deleted'] = true;
        }
        else {
            $data['file_not_found'] = true;
        }

        $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_ADN_DIR . '*');
        $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_PRO_DIR . '*');
    }

    // If import request
    if (isset($_FILES['fasta'], $_POST['upload_type']) && is_string($_POST['upload_type']) && $_FILES['fasta']['size']) {
        $filename = $_FILES['fasta']['name'];
        $filesize = $_FILES['fasta']['size'];
        $location = $_FILES['fasta']['tmp_name'];
        $status = $_FILES['fasta']['error'];

        $mode = $_POST['upload_type'] === 'adn' ? 'adn' : 'pro';

        if ($status === UPLOAD_ERR_OK) {
            $name = findSafeName($files[$mode], $location, $filename);

            if (!$name) {
                $data['already_exists'] = true;
            }
            else {
                if (!move_uploaded_file($location, $_SERVER['DOCUMENT_ROOT'] . ($mode === 'pro' ? FASTA_PRO_DIR : FASTA_ADN_DIR) . $name)) {
                    $data['upload_error'] = true;
                }
    
                $files['adn'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_ADN_DIR . '*');
                $files['pro'] = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_PRO_DIR . '*');
                $data['upload_ok'] = true;
            }
        }
        else {
            $data['upload_error'] = true;
        }
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

function importGenomeView(array $data) : void { ?>
    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Database file is a tabulated version of an Excel document.<br>
                    Name of the colomns can be present in the file, 
                    but you must specify it before launch build process.<br>
                    If a gene has multiple pathways, they must be separated by a pipe (|).<br><br>
                    <span class="underline">Colomn order should be ordered as [one slash: one tabulation]</span> :<br>
                    Name / Role / Pathway / Fullname / Family / SubFamily / <?= implode(' / ', $data['ord_species']) ?> <br>
                    <span class="very-tiny-text no-line-height">
                        Species can be added and their order changed in "Database species" module.
                        Order can be dynamically modified during build.
                    </span>
                    <br><br>

                    <span class="underline">Gene ID information by specie MUST be formatted as</span> :<br>
                    ({GENE_ID}, ...), ({OTHER_GENE_ID}, ...)
                </p>
            </div>

            <div class="card card-border">
                <div class="card-content">
                    <?php 
                    if (isset($data['already_exists'])) {
                        echo '<h6 class="red-text">File is already present</h6>';
                    }
                    else if (isset($data['upload_error'])) {
                        echo '<h6 class="red-text">Unexpected error during upload</h6>';
                    }
                    else if (isset($data['upload_ok'])) {
                        echo '<h6 class="green-text">File uploaded successfully</h6>';
                    }
                    ?>

                    <form method="post" enctype="multipart/form-data" action="#">
                        <div class="file-field input-field">
                            <div class="btn light-blue darken-1">
                                <span>New database file</span>
                                <input name="database" accept="text/tab-separated-values" required type="file">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text">
                            </div>
                        </div>

                        <button type="submit" class="btn-flat blue-text right">Submit</button>
                        <div class="clearb"></div>
                    </form>
                </div>
            </div>

            <div class="divider divider-margin"></div>

            <a href="/api/tools/download_db.php?with_title=1" target="_blank" 
                class="btn-flat green-text btn-perso center-block center">
                <i class="material-icons left">file_download</i>Download current website database in TSV format
            </a>

            <div class="divider divider-margin"></div>
            
            <?php if (count($data['files']) !== 0) { ?>
                <h5>Currently uploaded database files</h5>
            <?php }

            if (isset($data['file_not_found'])) {
                echo '<h6 class="red-text">File not found</h6>';
            }
            else if (isset($data['file_deleted'])) {
                echo '<h6 class="green-text">File deleted successfully</h6>';
            }
            ?>
        </div>
    </div>

    <?php showMappingFiles($data['files'], true); ?>

    <?php
}

function importBlastView(array $data) : void { ?>
    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Import FASTA files for store sequence information in the database's genes.<br>
                    FASTA file must present, right after the &gt; in the beginning of a comment line, a gene ID
                    registered in the database.<br> 
                    
                    Additionnal informations can be present after the gene ID (one space is needed after the ID), 
                    but it will not be saved.<br><br>
                    
                    See more infos for the required format of the FASTA file in
                    <a class="white-text underline" href="/admin/checker">FASTA checker</a> page.
                </p>
            </div>

            <div class="card card-border">
                <div class="card-content">
                    <?php 
                    if (isset($data['already_exists'])) {
                        echo '<h6 class="red-text">File is already present</h6>';
                    }
                    else if (isset($data['upload_error'])) {
                        echo '<h6 class="red-text">Unexpected error during upload</h6>';
                    }
                    else if (isset($data['upload_ok'])) {
                        echo '<h6 class="green-text">File uploaded successfully</h6>';
                    }
                    ?>

                    <form method="post" enctype="multipart/form-data" action="#">
                        <div class="row">
                            <div class="col s3">
                                <p style="margin-top: 13px;">
                                    <label>
                                        <input name="upload_type" value="adn" type="radio" checked>
                                        <span>DNA</span>
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input name="upload_type" value="pro" type="radio">
                                        <span>Protein</span>
                                    </label>
                                </p>
                            </div>

                            <div class="file-field input-field col s9">
                                <div class="btn light-blue darken-1">
                                    <span>New FASTA file</span>
                                    <input name="fasta" accept="application/fasta" required type="file">
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-flat blue-text right">Submit</button>
                        <div class="clearb"></div>
                    </form>
                </div>
            </div>

            <div class="divider divider-margin"></div>

            <a href="/api/tools/download_fasta.php?mode=dna" target="_blank" 
                class="col s6 btn-flat green-text btn-perso center-block center">
                <i class="material-icons left">file_download</i>Download built DNA sequences
            </a>
            <a href="/api/tools/download_fasta.php?mode=pro" target="_blank" 
                class="col s6 btn-flat purple-text btn-perso center-block center">
                <i class="material-icons left">file_download</i>Download built proteic sequences
            </a>
            <div class="clearb"></div>

            <div class="divider divider-margin"></div>

            <?php
            if (isset($data['file_not_found'])) {
                echo '<h6 class="red-text">File not found</h6>';
            }
            else if (isset($data['file_deleted'])) {
                echo '<h6 class="green-text">File deleted successfully</h6>';
            }
            ?>
        </div>
    </div>

    <?php showFastaFiles($data['files'], true); ?>

    <?php
}
