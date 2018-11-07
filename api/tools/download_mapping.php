<?php

/**
 * downloadMapping
 * 
 * Renvoie une chaîne formatée en txt des mappings dispo
 * @return string
 */
function downloadMap() : string { 
    global $sql; // importe la connexion SQL chargée avec l'appel à connectBD()

    $str = "";

    $q = mysqli_query($sql, "SELECT gene_id, alias FROM GeneAssociations WHERE alias IS NOT NULL");

    while ($row = mysqli_fetch_assoc($q)) {
        $str .= "{$row['gene_id']}\t{$row['alias']}\n";
    }

    return $str;
}

if (isUserLogged()) {
    session_write_close();

    $name = "mapping_" . date('Y_m_d'); 

    header("Content-Type: text/plain");
    header("Content-disposition: attachment; filename=\"$name.txt\""); 
    echo downloadMap();
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
