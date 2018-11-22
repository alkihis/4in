<?php

// Récupère tous les IDs
global $sql;

if (isset($_POST['ids']) && is_string($_POST['ids']) && $_POST['mode'] && is_string($_POST['mode'])) {
    $mode = 'sequence_adn';
    $seq = 'na';

    $line_breaks = (int)($_POST['chars_by_line'] ?? 0);
    if ($line_breaks < 0) {
        $line_breaks = 0;
    }

    switch($_POST['mode']) {
        case 'adn':
            break;
        case 'pro':
            $mode = 'sequence_pro';
            $seq = 'aa';
            break;
    }

    $ids = explode(',', $_POST['ids']);
    $final_fasta = '';
    $name = "search";
    if (count($ids) === 1) {
        // Si il n'y a qu'un seul ID, le fichier aura pour nom l'ID
        // échappe le nom de fichier
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $ids[0]) . ($_POST['mode'] === 'pro' ? '_pro' : '_dna');
    }

    // Pour chaque ID demandé
    foreach($ids as $id) {
        $id = mysqli_real_escape_string($sql, $id);

        // On le récupère et on inclut la séquence dans le fasta $final_fasta
        $q = mysqli_query($sql, "SELECT $mode, specie FROM GeneAssociations WHERE gene_id='$id' AND $mode IS NOT NULL");

        if (mysqli_num_rows($q)) {
            $row = mysqli_fetch_assoc($q);
            
            // Filtre les gènes protégés
            if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isUserLogged()) {
                // Si le génome est protégé, on l'insère pas dans le tableau
                continue;
            }

            if (!$line_breaks) { // Si on a demandé un certain nombre de caractères par ligne
                $final_fasta .= ">$id\n{$row[$mode]}\n";
            }
            else {
                $final_fasta .= ">$id\n" . chunk_split($row[$mode], $line_breaks, "\n");
            }   
        }
    }

    if ($final_fasta) {
        header("Content-Type: chemical/seq-$seq-fasta");
        header("Content-disposition: attachment; filename=\"$name.fasta\""); 
        echo $final_fasta;
    }
    else {
        // aucune séquence !
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
}
