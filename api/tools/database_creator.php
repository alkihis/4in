<?php

// Les espèces présentes dans le fichier, dans le bon ordre
// Ces espèces sont là à titre INFORMATIF et sont faites pour être définies
// manuellement via un paramètre POST
$assocs_species = ['Soryzae', 'Apisum', 'Agambiae', 'Amellifera', 
    'Bmori', 'Cfloridanus', 'Dmelanogaster', 'Gmorsitans',
    'Msexta', 'Nvitripennis', 'Phumanus', 'Pxylostella',
    'Tcastaneum', 'Sinvicta', 'Dponderosae', 'Aaegypti'
];

/**
 * insertPathway
 *
 * Insère le pathway souhaité pour le gène $id_gene dans la base de données
 * @param integer $id_gene
 * @param string $pathway
 * @param mysqli_stmt $stmt_request Request to send new pathway
 * @return void
 */
function insertPathway(int $id_gene, string $pathway, $stmt_request) : void {
    /* bind parameters for markers */
    $stmt_request->bind_param("is", $id_gene, $pathway);
    $stmt_request->execute();
}

/**
 * explodeFile
 * 
 * Parse un fichier séparé par des tabulations, et l'enregistre dans une base de données conçue pour
 * @param string $filename : Chemin du fichier .tsv à parser
 * @param bool $trim_first_line
 * @param bool $read_from_first Read species from first line
 * > trim_first_line must be on if read_first_line is on
 * @return void
 */
function explodeFile(string $filename, bool $trim_first_line = false, bool $read_from_first = false) : void { 
    global $sql; // importe la connexion SQL chargée avec l'appel à connectBD()
    global $assocs_species;

    // Prépare les requêtes pour améliorer le temps d'exécution
    $stmt_gene = mysqli_prepare($sql, "INSERT INTO Gene 
        (func, gene_name, fullname, family, subfamily)
        VALUES
        (?, ?, ?, ?, ?)");

    $stmt_assoc = mysqli_prepare($sql, "INSERT INTO GeneAssociations
        (id, gene_id, sequence_adn, sequence_pro, specie, addi)
        VALUES (?, ?, NULL, NULL, ?, ?)");

    $stmt_pathway = mysqli_prepare($sql, "INSERT INTO Pathways 
        (id, pathway)
        VALUES
        (?, ?);");

    // Sauvegarde le nombre d'espèces pour l'utiliser dans la future boucle
    $number_of_species = count($assocs_species);

    $handle = fopen($filename, 'r'); // ouvre le fichier $filename en lecture, et stocke le pointeur-sur-fichier dans $handle

    if (!$handle) {
        throw new RuntimeException('Unable to open file');
    }

    while (!feof($handle)) { // Si $handle est valide et tant que le fichier n'est pas fini (feof signifie file-end-of-file)
        $line = fgets($handle); // récupère une ligne du fichier

        if ($trim_first_line) { // Si la première ligne doit être passée
            $trim_first_line = false;

            if ($read_from_first) {
                // Lecture des espèces
                $line = rtrim($line, "\r\n");

                $arr = explode("\t", $line); // sépare la ligne en fonction d'une tabulation et la met dans un tableau
                // Les espèces commencent à la 6ème case

                $assocs_species = [];
                for ($i = 6; $i < count($arr); $i++) {
                    $cur_specie = trim($arr[$i]);
                    if (empty($cur_specie)) { // Si la case de texte est vide, on arrête là
                        break;
                    }

                    $assocs_species[] = $cur_specie;
                }

                $number_of_species = count($assocs_species);
            }
            continue;
        }

        if (trim($line) === "") { 
            // Si la ligne entièrement trimmée est vide, on skippe
            continue;
        }

        // On enlève le \r\n [CRLF] terminal à la droite de la ligne 
        // (il est présent dans le fichier .tsv), fgets le conserve lorsqu'il prend la ligne
        $line = rtrim($line, "\r\n");

        $arr = explode("\t", $line); // sépare la ligne en fonction d'une tabulation et la met dans un tableau

        // Importe fonction, pathway... dans des variables, les espaces terminaux trimmés
        // Pas besoin d'échapper ' car on utilise mysqli_prepare
        $func = trim($arr[1]);
        $pathway = trim($arr[2]);
        $name = trim($arr[0]);
        $fullname = trim($arr[3]);
        $family = trim($arr[4]);
        $sub = trim($arr[5]);

        // Insère ces données dans le Gene
        /* bind parameters for markers */
        $stmt_gene->bind_param("sssss", $func, $name, $fullname, $family, $sub);

        /* execute query */
        $stmt_gene->execute();

        // Récupération de l'ID numérique d'insertion (clé primaire de Gene)
        $id_insert = $stmt_gene->insert_id;

        // Si un pathway est défini, on le split en fonction de |, et on insère autant de pathways que défini dans Pathways,
        // ceux-ci étant reliés au Gene par son ID
        if (!empty($pathway)) {
            $pathways = explode('|', $pathway);

            foreach ($pathways as $p) {
                $p = trim($p);

                if ($p) {
                    insertPathway($id_insert, $p, $stmt_pathway);
                }
            }
        }

        // Pour les lignes 6 à 21 du tableau
        for ($i = 6; $i < ($number_of_species+6); $i++) {
            $m = [];
            // Enregistre les matches de l'expression régulière ci-dessous dans $m
            // preg_match_all("/\((.+?)\),?/m", $arr[$i], $m);
            /*
            UPDATE : l'expression régulière fonctionne, mais mal.
            Il y a des lignes étrangement formatées... avec des parenthèses à l'intérieur des parenthèses.
            L'expression régulière capture donc assez mal pour ces gènes là.
            Le choix fait est plutôt de la simulation d'une pile, où les parenthèses ouvrantes/fermantes empilent/dépilent
            et où on lit un ID uniquement après la première parenthèse ouverte et puis jusqu'à la première virgule rencontrée
            */

            $found_ids = [];
            $parenthese_pile = 0;
            $current_id = "";
            $additionnal = "";
            $should_read = false;
            $should_read_info = false;
            $len_arr_i = strlen($arr[$i]);
            for ($j = 0; $j < $len_arr_i; $j++) {
                $char = $arr[$i][$j];

                // Si c'est une parenthèse ouvrante, on incrémente le compteur de parenthèse
                if ($char === '(') {
                    $parenthese_pile++;

                    // Si on est à la première parenthèse ouverte, on peut lire
                    $should_read = ($parenthese_pile === 1);
                }
                // Si elle est fermante, on décrémente
                else if ($char === ')') {
                    if ($parenthese_pile === 1) {
                        // Si il n'y avait qu'une parenthèse ouverte, c'est le moment
                        // de stocker l'ID enregistré et les informations additionnelles si il y en a
                        $current_id = trim($current_id);

                        if (!empty($current_id)) {
                            $additionnal = trim($additionnal, ", \t\n\r\0\x0B");
                            if (empty($additionnal)) {
                                $additionnal = null;
                            }
                            // Stocke les données
                            $found_ids[] = [
                                'id' => $current_id, 
                                'info' => $additionnal
                            ];
                            $current_id = $additionnal = "";
                        }
                        $should_read_info = false;
                    }

                    $parenthese_pile--;
                    if ($parenthese_pile < 0) {
                        $parenthese_pile = 0;
                    }
                }
                // Si on rencontre une virgule, on interrompt la lecture si elle l'était
                else if ($char === ',') {
                    if ($should_read) {
                        $should_read = false;
                        $should_read_info = true;
                    }
                }
                // Si on doit lire, on ajoute le caractère dans la chaîne tampon
                else if ($should_read) {
                    $current_id .= $char;
                }
                // Si on doit lire les infos additionnelles, on stocke dans ce buffer
                if ($should_read_info && !$should_read) {
                    $additionnal .= $char;
                }
            }

            // Pour chaque ID trouvé, on l'insère dans la bdd
            foreach ($found_ids as $id_f) {
                // id_insert : ID SQL du gène, 
                // id_f['id'] : ID du gène réel, 
                // $assocs_species[$i - 6] : parce les espèces commencent à la colonne 6 et que i correspond au numéro de colonne,
                // $id_f['info'] : Informations additionnelles
                $stmt_assoc->bind_param("isss", $id_insert, $id_f['id'], $assocs_species[$i - 6], $id_f['info']);
                $stmt_assoc->execute();
            } 
        }
    }

    // Fermeture du fichier
    fclose($handle);
}

/**
 * emptyTables
 * Vide les tables SQL du site web
 * 
 * @return void
 */
function emptyTables() : void {
    global $sql;

    mysqli_query($sql, "DELETE FROM Gene;");
    mysqli_query($sql, "DELETE FROM GeneAssociations;");
    mysqli_query($sql, "DELETE FROM Pathways;");
    mysqli_query($sql, "ALTER TABLE Gene AUTO_INCREMENT=1;");
    mysqli_query($sql, "ALTER TABLE Pathways AUTO_INCREMENT=1;");

    // Supprime les fichiers du cache (sauf fichiers cachés)
    $files_cache = glob($_SERVER['DOCUMENT_ROOT'] . '/assets/cache/*');

    foreach ($files_cache as $f) {
        unlink($f);
    }
}

// Si l'utilisateur est connecté, on autorise
if (isAdminLogged()) {
    // On ferme la session parce qu'elle ne servira plus
    session_write_close();

    // On attend l'information du nom du fichier et toutes les espèces
    if (isset($_POST['file'], $_POST['species']) && is_string($_POST['file']) && is_string($_POST['species'])) {
        global $assocs_species;

        $assocs_species = explode(',', $_POST['species']);

        $trim_first = isset($_POST['trim_first']) && $_POST['trim_first'] === 'true';
        $read_first = isset($_POST['read_first']) && $_POST['read_first'] === 'true';

        $file = $_POST['file'];

        $empty = (isset($_POST['empty']) && $_POST['empty'] === 'true');

        // les fichiers sont dans %webserverroot%/assets/db/{fichier}
        $file = preg_replace("/\/\.\.\//", "", $file);
        $path = $_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $file;

        if (file_exists($path) && !is_dir($path)) {
            // Augmentation de la timelimit pour éviter le crash de PHP après 30 secondes
            set_time_limit(30 * 10);

            if ($empty)
                emptyTables();

            explodeFile($path, $trim_first, $read_first);

            // Après ça, renvoie un JSON contenant les espèces présentes dans la base et leur correspondance
            // avec un acronyme
            $species = [];
            global $sql;
            $q = mysqli_query($sql, "SELECT DISTINCT specie, COUNT(specie) c FROM GeneAssociations GROUP BY specie;");

            while ($row = mysqli_fetch_assoc($q)) {            
                $species[$row['specie']] = ['name' => SPECIE_TO_NAME[$row['specie']] ?? null, 'count' => $row['c']];
            }

            header('Content-Type: application/json');
            echo json_encode($species);
        }
        else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        }
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

