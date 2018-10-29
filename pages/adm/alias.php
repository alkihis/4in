<?php

function importAliasController() : array {
    global $sql;
    $data = ['active_page' => 'alias_import'];

    $files = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/*');

    // If delete request
    if (isset($_POST['delete']) && is_string($_POST['delete'])) {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/' . $_POST['delete']) 
            && !is_dir($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/' . $_POST['delete'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/' . $_POST['delete']);
            $data['file_deleted'] = true;
        }
        else {
            $data['file_not_found'] = true;
        }

        $files = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/*');
    }

    // If import request
    if (isset($_FILES['mapping']) && $_FILES['mapping']['name'] && $_FILES['mapping']['size']) {
        $filename = $_FILES['mapping']['name'];
        $filesize = $_FILES['mapping']['size'];
        $location = $_FILES['mapping']['tmp_name'];
        $status = $_FILES['mapping']['error'];

        if ($status === UPLOAD_ERR_OK) {
            $name = findSafeName($files, $location, $filename);

            if (!$name) {
                $data['already_exists'] = true;
            }
            else {
                if (!move_uploaded_file($location, $_SERVER['DOCUMENT_ROOT'] . '/fasta/map/' . $name)) {
                    $data['upload_error'] = true;
                }
    
                $files = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/*');
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

function buildAliasController() : array {
    global $sql;
    $data = ['active_page' => 'alias_build'];

    $files = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/*');

    $data['files'] = [];
    foreach ($files as $f) {
        $data['files'][] = ['name' => basename($f), 'size' => round(filesize($f) / 1024, 2), 'date' => filemtime($f)];
    }

    // Traitement si l'utilisateur a demandé de supprimer le mapping existant
    if (isset($_POST['erase'])) {
        resetIndex();

        $data['erased'] = true;
    }

    // Traitement si l'utilisateur a demandé de construire le mapping
    /* else if (isset($_POST['construct'])) {
        foreach ($files as $f) {
            readBuildIndex($f);
        }

        $data['construction'] = true;
    } */

    return $data;
}

function aliasImportView(array $data) : void { ?>
    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Gene alias are used in case of gene ID in this database is different in another database.<br>
                    The generated link to ArthropodaCyc will use defined alias instead.<br>
                    Alias file <span class="underline">MUST</span> have 2 colomns, separated by a tabulation (first colomn
                    is at the beginning of the line), the <span class="underline">first</span> colomn must present 
                    <span class="underline">gene ID registered in this database</span>, second one must be
                    <span class="underline">gene ID of the external database</span>.
                </p>
            </div>

            <div class="card card-border">
                <div class="card-content">
                    <?php 
                    if (isset($data['already_exists'])) {
                        echo '<h6 class="red-text">File is already present in mapping files</h6>';
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
                                <span>New mapping file</span>
                                <input name="mapping" accept="text/plain" required type="file">
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

            <?php if (count($data['files']) !== 0) { ?>
                <h5>Currently loaded mapping files</h5>
            <?php } ?>

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

    <?php showMappingFiles($data['files'], true); ?>

    <?php
}

function aliasBuildView(array $data) : void { ?>
    <div class="row no-margin-bottom">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Import first mapping files using "Import mapping files" utility. After this operation,
                    you can synchronize mapping txt files and SQL database using this page. It will register
                    alias in gene ID entries.<br>
                    Remember that if you reset or re-import the database, you must do this operation again.<br><br>
                    You can also delete all existing alias to be sure to start from scratch 
                    (it will <span class='underline'>NOT</span> delete uploaded files).<br>
                    After any operation, it is recommanded to reset link status (see in side-navigation menu).
                </p>
            </div>

            <?php if (isset($data['construction'])) {
                echo '<h5 class="green-text">Database is successfully updated with mapping informations.</h5>';
            } 
            if (isset($data['erased'])) {
                echo '<h5 class="red-text">Mapping informations has been wiped from the database.</h5>';
            } ?>

            <?php if (count($data['files']) !== 0) { ?>
                <h5>Currently loaded mapping files</h5>
            <?php } ?>

            <?php 
            if (isset($data['file_not_found'])) {
                echo '<h6 class="red-text">File not found</h6>';
            }
            else if (isset($data['already_exists'])) {
                echo '<h6 class="red-text">File is already present in mapping files</h6>';
            }
            else if (isset($data['upload_error'])) {
                echo '<h6 class="red-text">Unexpected error during upload</h6>';
            }
            else if (isset($data['file_deleted'])) {
                echo '<h6 class="green-text">File successfully deleted</h6>';
            }
            else if (isset($data['upload_ok'])) {
                echo '<h6 class="green-text">File successfully uploaded</h6>';
            }
            ?>
        </div>
    </div>

    <?php showMappingFiles($data['files'], false); ?>

    <div class="row">
        <div class="col s6">
            <a href="#modal_wipe" class="btn btn-personal red darken-1 center-block modal-trigger">
                Erase all gene mapping
            </a>
        </div>

        <?php if (count($data['files']) !== 0) { ?>
            <div class="col s6">
                <a href="#modal_build" id="go_db" class="modal-trigger btn btn-personal green darken-1 center-block">
                    Build alias mapping
                </a>

                <!-- set modal build parameter -->
                <script>
                    document.getElementById('wipe_header').innerText = 'Wipe gene aliases from database ?';
                    document.getElementById('wipe_text').innerText = 'After wipe, you need to map aliases again using \
                        this tool to register gene aliases. All current defined aliases will be lost.';

                    document.getElementById('build_header').innerText = 'Build mapping in database from uploaded files ?';
                    document.getElementById('build_text').innerText = 'Building will read all mapping\
                    files and register aliases in website database.';

                    $(document).ready(function () {
                        $.get('/api/tools/get_all_mapping_files.php', {}, function(data) {
                            document.getElementById('setter_builder').onclick = function () {
                                launchMapBuild(JSON.parse(data));
                            };
                        });
                    });
                </script>
            </div>
        <?php } ?>

    </div>

    <?php
}

function resetIndex() : void {
    global $sql;

    mysqli_query($sql, "UPDATE GeneAssociations SET alias=NULL;");
}

function readBuildIndex(string $filename) : void {
    global $sql;

    $h = fopen($filename, 'r'); 
    // ouvre le fichier $filename en lecture, et stocke le pointeur-sur-fichier dans $h

    if (!$h) {
        throw new RuntimeException('Unable to open file');
    }

    while (!feof($h)) { // Si $h est valide et tant que le fichier n'est pas fini (feof signifie file-end-of-file)
        $line = fgets($h); 

        $line = explode("\t", $line);

        if (count($line) < 2) {
            // Ligne invalide, on ne lance rien mais on devrait...
            continue;
        }

        $id_classic = mysqli_real_escape_string($sql, trim($line[0]));
        $id_alias = mysqli_real_escape_string($sql, trim($line[1]));

        mysqli_query($sql, "UPDATE GeneAssociations SET alias='$id_alias' WHERE gene_id LIKE '$id_classic%';");
    }
}
