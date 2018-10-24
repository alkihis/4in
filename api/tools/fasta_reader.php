<?php

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

session_start();

if (isUserLogged()) {
    session_write_close();

    if (isset($_POST['file']) && is_string($_POST['file'])) {
        $file = $_POST['file'];
        $mode = (isset($_POST['mode']) && $_POST['mode'] === 'pro' ? 'pro' : 'adn');

        $path = $_SERVER['DOCUMENT_ROOT'] . '/fasta/' . $mode . '/' . $file;

        if (file_exists($path) && !is_dir($path)) {
            set_time_limit(30 * 10);
            loadFasta($path, $mode);
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
