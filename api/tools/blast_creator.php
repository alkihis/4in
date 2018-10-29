<?php

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

function makeAllBlastDB() : void {
    // Construction des 4 bases :
    // ADN sans autorisation et complète (génomes protégés), de même protéine
    makeBlastDB('adn', true);
    makeBlastDB('adn', false);
    makeBlastDB('pro', true);
    makeBlastDB('pro', false);
}

session_start();

if (isUserLogged()) {
    session_write_close();

    if (isset($_POST['make'])) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // Pas possible sur Windows
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        } 
        else {
            set_time_limit(30 * 10);
            makeAllBlastDB();
        }
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

