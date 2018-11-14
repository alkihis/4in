<?php

$cache = $_SERVER['DOCUMENT_ROOT'] . '/assets/cache/get_all_cache.json';

// Checke le cache
if (file_exists($cache) && (time() - filemtime($cache)) < (60*60)) {
    // Si il existe et que le fichier est récent (moins d'une heure),
    // on affiche le contenu de ce fichier plutôt que tout calculer depuis la base de données
    header('Content-Type: application/json');
    echo file_get_contents($cache);

    return;
}

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
$json = json_encode($res);

// Enregistre le cache
file_put_contents($cache, $json);

echo $json;
