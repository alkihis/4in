<?php

// Récupère tous les subfamily
global $sql;

$q = mysqli_query($sql, "SELECT DISTINCT subfamily FROM Gene WHERE subfamily <> ''");

$res = [];
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['subfamily']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
