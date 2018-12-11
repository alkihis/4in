<?php

// Récupère tous les familles
global $sql;

$q = mysqli_query($sql, "SELECT DISTINCT family FROM Gene WHERE family <> ''");

$res = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['family']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
