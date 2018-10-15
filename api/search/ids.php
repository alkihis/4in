<?php

// Récupère tous les IDs
global $sql;

$q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations");

$res = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['gene_id']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
