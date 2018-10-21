<?php

session_start();
// Récupère tous les IDs
global $sql;

$add = '';

if (LIMIT_GENOMES && !isUserLogged()) {
    $first = true;

    foreach (getProtectedSpecies() as $specie) {
        $specie = mysqli_real_escape_string($sql, $specie);

        if ($first) {
            $first = false;

            $add = " WHERE ";
        }
        else {
            $add .= ' AND ';
        }

        $add .= " specie != '$specie' ";
    }
}

$q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations $add");

$res = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['gene_id']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
