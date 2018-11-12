<?php

// Récupère tous les IDs
global $sql;

// Si le fichier d'entrée est fourni, on commence à l'analyser
if (isset($_FILES['input']) && $_FILES['input']['size']) {
    $h = fopen($_FILES['input']['tmp_name'], 'r');
    $name = $_FILES['input']['name'];

    // ID de position
    // Précise où l'ID doit être lu après explode() / preg_split()
    $id_pos = (int)($_POST['id_pos'] ?? 0);

    // Séparateur à utiliser
    $separator = "\s";

    $no_empty = (bool)($_POST['no_empty'] ?? false);
    $flags = ($no_empty ? PREG_SPLIT_NO_EMPTY : 0);

    // Si le séparateur est personnalisé, on l'utilise
    if (isset($_POST['sep']) && is_string($_POST['sep'])) {
        if (isset($_POST['is_regex'])) {
            $separator = $_POST['sep'];
        }
        else {
            $separator = preg_quote($_POST['sep'], '/');
        }
    }

    if ($h) {
        // Prend le nom du fichier sans l'extension pour ajouter le suffixe _formatted
        $name = substr($name, 0, strrpos($name, "."));
        $formatted_name = $name . '_formatted'; 

        header("Content-Type: application/fasta");
        header("Content-disposition: attachment; filename=\"$formatted_name.fasta\""); 
        
        // Tant que le fichier est pas fini
        while (!feof($h)) {
            // On récupère la ligne en cours
            $line = fgets($h);
            $line = trim($line);
    
            if (strpos($line, '>') === 0) { // Commentaire, on doit reformer la ligne
                $trimmed = trim(substr($line, 1));
                // On explose la ligne
                $id = preg_split("/$separator/", $trimmed, -1, $flags);
    
                // Si jamais c'est défini, alors on stocke l'id via $real_id
                if (isset($id[$id_pos])) {
                    $real_id = $id[$id_pos];
                }
                else {
                    echo ">---------------- PARSE ERROR: LINE DOES NOT MATCH POSITION REQUIREMENTS ----------------\n";
                    return;
                }
    
                echo ">$real_id\n";
            }
            else {
                echo "$line\n";
            }
        }

        fclose($h);
    }
    else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
}

