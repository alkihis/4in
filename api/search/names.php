<?php

// Récupère tous les noms
global $sql;

$q = mysqli_query($sql, "SELECT DISTINCT gene_name FROM Gene WHERE gene_name <> ''");

$res = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['gene_name']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
