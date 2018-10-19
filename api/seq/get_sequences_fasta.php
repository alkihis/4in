<?php

// Récupère tous les IDs
global $sql;

if (isset($_POST['ids']) && is_string($_POST['ids']) && $_POST['mode'] && is_string($_POST['mode'])) {
    $mode = 'sequence_adn';
    $seq = 'na';

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

    foreach($ids as $id) {
        $id = mysqli_real_escape_string($sql, $id);

        $q = mysqli_query($sql, "SELECT $mode FROM GeneAssociations WHERE gene_id='$id' AND $mode IS NOT NULL");

        if (mysqli_num_rows($q)) {
            $row = mysqli_fetch_assoc($q);

            $final_fasta .= ">$id\n{$row[$mode]}\n";
        }
    }

    if ($final_fasta) {
        header("Content-Type: chemical/seq-$seq-fasta");
        header("Content-disposition: attachment; filename=\"search.fasta\""); 
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
