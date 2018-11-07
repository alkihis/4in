<?php

/**
 * downloadFasta
 * 
 * Renvoie une chaîne formatée en FASTA contenant toutes les séquences (DNA or Proteic) de la base
 * @param string $mode
 * @return string
 */
function downloadFasta(string $mode) : string { 
    global $sql; // importe la connexion SQL chargée avec l'appel à connectBD()

    $str = "";

    $mode = ($mode === 'dna' ? 'sequence_adn' : 'sequence_pro');

    $q = mysqli_query($sql, "SELECT gene_id, $mode FROM GeneAssociations WHERE $mode IS NOT NULL");

    while ($row = mysqli_fetch_assoc($q)) {
        $str .= ">{$row['gene_id']}\n{$row[$mode]}\n";
    }

    return $str;
}

if (isUserLogged()) {
    session_write_close();

    $mode = (isset($_GET['mode']) && $_GET['mode'] === 'pro' ? 'pro' : 'dna');

    $name = "fasta_${mode}_" . date('Y_m_d'); 

    header("Content-Type: text/plain");
    header("Content-disposition: attachment; filename=\"$name.fasta\""); 
    echo downloadFasta($mode);
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
