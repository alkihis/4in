<?php

/**
 * Réinitilise les alias
 *
 * @return void
 */
function resetIndex() : void {
    global $sql;

    mysqli_query($sql, "UPDATE GeneAssociations SET alias=NULL;");
}

/**
 * Construit les alias depuis un fichier $filename tabulé
 * gene_id \t alias
 *
 * @param string $filename
 * @return void
 */
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
            // Ligne invalide, on ne lance aucune exception mais on devrait...
            continue;
        }

        $id_classic = mysqli_real_escape_string($sql, trim($line[0]));
        $id_alias = mysqli_real_escape_string($sql, trim($line[1]));

        mysqli_query($sql, "UPDATE GeneAssociations SET alias='$id_alias' WHERE gene_id LIKE '$id_classic%';");
    }
}

if (isAdminLogged()) {
    session_write_close();

    if (isset($_POST['file']) && is_string($_POST['file'])) {
        $file = $_POST['file'];

        // Supprime les /../ du chemin
        $file = preg_replace("/\/\.\.\//", "", $file);

        $path = $_SERVER['DOCUMENT_ROOT'] . MAPPING_DIR . $file;

        // Si le fichier existe, on lance la création
        if (file_exists($path) && !is_dir($path)) {
            set_time_limit(30 * 10);
            readBuildIndex($path);
        }
        else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        }
    }
    else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

