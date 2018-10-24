<?php

// Récupère tous les IDs
global $sql;

if (isset($_FILES['input']) && $_FILES['input']['size']) {
    $h = fopen($_FILES['input']['tmp_name'], 'r');
    $name = $_FILES['input']['name'];

    $id_pos = (int)($_POST['id_pos'] ?? 0);

    $separator = "\s";

    $no_empty = (bool)($_POST['no_empty'] ?? false);
    $flags = ($no_empty ? PREG_SPLIT_NO_EMPTY : 0);

    if (isset($_POST['sep']) && is_string($_POST['sep'])) {
        if (isset($_POST['is_regex'])) {
            $separator = $_POST['sep'];
        }
        else {
            $separator = preg_quote($_POST['sep'], '/');
        }
    }

    if ($h) {
        $name = substr($name, 0, strrpos($name, "."));
        $formatted_name = $name . '_formatted'; 

        header("Content-Type: application/fasta");
        header("Content-disposition: attachment; filename=\"$formatted_name.fasta\""); 
        
        while (!feof($h)) {
            $line = fgets($h);
            $line = trim($line);
    
            if (strpos($line, '>') === 0) { // Commentaire, on doit reformer la ligne
                $trimmed = trim(substr($line, 1));
                $id = preg_split("/$separator/", $trimmed, -1, $flags);
    
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

