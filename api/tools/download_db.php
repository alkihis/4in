<?php

/**
 * downloadDatabase
 * 
 * Renvoie une chaîne formatée en TSV dans l'ordre donnée par $assocs_species
 * @param array $assocs_species : Ordre et nom des espèces
 * @return string
 */
function downloadDatabase(array $assocs_species) : string { 
    global $sql; // importe la connexion SQL chargée avec l'appel à connectBD()

    $genes = [];

    $q = mysqli_query($sql, "SELECT g.*, 
        (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR '|') FROM Pathways p WHERE g.id = p.id) as pathways 
        FROM Gene g");

    $str = "";

    while ($row = mysqli_fetch_assoc($q)) {
        $current_gene = mysqli_query($sql, "SELECT * FROM GeneAssociations WHERE id={$row['id']}");

        // Récupération de tous les gènes pour cet ID
        $matched = [];
        $ids_for_species = [];
        while ($row2 = mysqli_fetch_assoc($current_gene)) {
            $matched[] = $row2;
            $ids_for_species[$row2['specie']][] = $row2['gene_id'];
        }

        $str .= "{$row['gene_name']}\t{$row['func']}\t{$row['func']}\t{$row['pathways']}\t{$row['fullname']}\t";
        $str .= "{$row['family']}\t{$row['subfamily']}\t";

        // Écriture des espèces
        $first = true;
        foreach ($assocs_species as $spe) {
            if ($first) {
                $first = false;
            }
            else {
                $str .= "\t";
            }

            // Si l'espèce parcourue a des IDs pour notre gène en cours
            if (isset($ids_for_species[$spe])) {
                // Les différents IDs sont noté (ID), (ID), (ID)
                $first2 = true;
                foreach ($ids_for_species[$spe] as $id_spe) {
                    if ($first2) {
                        $first2 = false;
                    }
                    else {
                        $str .= ", ";
                    }

                    $str .= "($id_spe)";
                }
            }
        }
    }

    return $str;
}

if (isUserLogged()) {
    session_write_close();

    $name = "database_" . date('Y_m_d'); 

    header("Content-Type: text/tab-separated-values");
    header("Content-disposition: attachment; filename=\"$name.tsv\""); 
    echo downloadDatabase(ORDERED_SPECIES);
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}