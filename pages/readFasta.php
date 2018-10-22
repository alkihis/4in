<?php

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
                
                // On traite la séquence en cours
                $q = mysqli_query($sql, "UPDATE GeneAssociations SET $mode='$sequence' WHERE gene_id LIKE '$current_id%';");
            }

            $e = substr($line, 1);
            $current_id = mysqli_real_escape_string($sql, trim(explode(' ', trim($e))[0]));
            $sequence = '';
        }
        else {
            $sequence .= trim($line);
        }
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

function readFastaControl($args) : Controller {
    // Dans le contrôleur, on exploite le GET ou le POST

    if (isset($_GET['refresh'])) {
        global $sql;
        
        // Construction séquences dans la BDD SQL
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_adn=NULL;");
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_pro=NULL;");

        $adn = glob('fasta/adn/*.fasta');
        $pro = glob('fasta/pro/*.fasta');
    
        foreach($adn as $a) {
            loadFasta($a, 'adn');
        }
        foreach($pro as $a) {
            loadFasta($a, 'pro');
        }

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

        // Construction des 4 bases :
        // ADN sans autorisation et complète (génomes protégés), de même protéine
        makeBlastDB('adn', true);
        makeBlastDB('adn', false);
        makeBlastDB('pro', true);
        makeBlastDB('pro', false);
    }
    

    // On donne les données au contrôleur
    return new Controller([], 'Read fasta');
}

function readFastaView(Controller $c) : void {

}
