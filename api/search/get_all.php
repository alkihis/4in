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

$q = mysqli_query($sql, "SELECT DISTINCT subfamily FROM Gene WHERE subfamily <> ''");

if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['subfamily']] = null;
    }
}

$q = mysqli_query($sql, "SELECT DISTINCT gene_name FROM Gene WHERE gene_name <> ''");

if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['gene_name']] = null;
    }
}

$q = mysqli_query($sql, "SELECT DISTINCT func FROM Gene WHERE func <> ''");

if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $res[$row['func']] = null;
    }
}

header('Content-Type: application/json');
echo json_encode($res);
