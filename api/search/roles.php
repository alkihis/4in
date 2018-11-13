<?php

// Récupère tous les familles
global $sql;

$q = mysqli_query($sql, "SELECT DISTINCT func FROM Gene WHERE func <> ''");

$res = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['func']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
