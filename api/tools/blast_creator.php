<?php

/**
 * Récupère toutes les séquences correspondant à un mode (adn ou pro)
 * au format FASTA
 *
 * @param string $mode "adn"|"pro"
 * @param boolean $full
 * @return string
 */
function getAllFastaSequences(string $mode = 'adn', bool $full = false) : string {
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

/**
 * Construit la base de données BLAST en fonction du mode (adn ou pro)
 * et mode complet ou non (séquences des espèces protégées cachées ou non)
 * 
 * DEMANDE UN UNIX
 *
 * @param string $mode "adn"|"pro"
 * @param boolean $full
 * @return void
 */
function makeBlastDB(string $mode = 'adn', bool $full = true) {
    chdir($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin');

    $suffix = ($full ? '_full' : '');

    $temp_file = `mktemp`;
    $temp_file = trim($temp_file);

    $date = date('Y-m-d');

    `chmod a+a $temp_file`;

    if ($mode === 'adn') {
        $str_seq = getAllFastaSequences('adn', $full);

        // Écrit le contenu dans le fichier
        file_put_contents($temp_file, $str_seq);

        `./makeblastdb -dbtype nucl -in "$temp_file" -title "Innate_Immunity_Insect_nucleic_$date" -out base/adn_base$suffix 2>&1`;
    }
    else if ($mode === 'pro') {
        $str_seq = getAllFastaSequences('pro', $full);

        // Écrit le contenu dans le fichier
        file_put_contents($temp_file, $str_seq);

        `./makeblastdb -dbtype prot -in "$temp_file" -title "Innate_Immunity_Insect_proteic_$date" -out base/pro_base$suffix 2>&1`;
    }
    else {
        chdir($_SERVER['DOCUMENT_ROOT']);
        throw new UnexpectedValueException('Unrecognized mode');
    }

    chdir($_SERVER['DOCUMENT_ROOT']);
    `rm -f $temp_file`;
}

/**
 * Construit toutes les bases BLAST
 *
 * @return void
 */
function makeAllBlastDB() : void {
    // Construction des 4 bases :
    // ADN sans autorisation et complète (génomes protégés), de même protéine
    makeBlastDB('adn', true);
    makeBlastDB('adn', false);
    makeBlastDB('pro', true);
    makeBlastDB('pro', false);
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

if (isAdminLogged()) {
    session_write_close();

    if (isset($_POST['make'])) {
        // Si jamais on est sur windows, on bloque
        if (stripos(PHP_OS, 'WIN') === 0) { // Pas possible sur Windows
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        } 
        else {
            clearBlastDatabase();
            
            set_time_limit(30 * 10);
            makeAllBlastDB();
        }
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

