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

function readFastaControl($args) : Controller {
    // Dans le contrôleur, on exploite le GET ou le POST
    loadFasta('fasta.fasta');

    // On donne les données au contrôleur
    return new Controller([], 'Read fasta');
}

function readFastaView(Controller $c) : void {

}
