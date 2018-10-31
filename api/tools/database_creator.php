<?php

// Les espèces présentes dans le fichier, dans le bon ordre
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
 * @return void
 */
function explodeFile(string $filename, bool $trim_first_line = false) : void { 
    global $sql; // importe la connexion SQL chargée avec l'appel à connectBD()
    global $assocs_species;

    $stmt_gene = mysqli_prepare($sql, "INSERT INTO Gene 
        (func, gene_name, fullname, family, subfamily)
        VALUES
        (?, ?, ?, ?, ?)");

    $stmt_assoc = mysqli_prepare($sql, "INSERT INTO GeneAssociations
        (id, gene_id, sequence_adn, sequence_pro, specie)
        VALUES (?, ?, NULL, NULL, ?)");

    $stmt_pathway = mysqli_prepare($sql, "INSERT INTO Pathways 
        (id, pathway)
        VALUES
        (?, ?);");

    $number_of_species = count($assocs_species);

    $h = fopen($filename, 'r'); // ouvre le fichier $filename en lecture, et stocke le pointeur-sur-fichier dans $h

    if (!$h) {
        throw new RuntimeException('Unable to open file');
    }

    while (!feof($h)) { // Si $h est valide et tant que le fichier n'est pas fini (feof signifie file-end-of-file)
        $line = fgets($h); // récupère une ligne du fichier

        if ($trim_first_line) { // Si la première ligne doit être passée
            $trim_first_line = false;
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

        // Importe fonction, pathway... dans des variables, avec les ' échappés, et les espaces terminaux trimmés
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

        // Si un pathway est défini, on le split en fonction de /, et on insère autant de pathways que défini dans Pathways,
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
            $should_read = false;
            for ($j = 0; $j < strlen($arr[$i]); $j++) {
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
                        // de stocker l'ID enregistré
                        if (trim($current_id)) {
                            $found_ids[] = trim($current_id);
                            $current_id = "";
                        }
                    }

                    $parenthese_pile--;
                    if ($parenthese_pile < 0) {
                        $parenthese_pile = 0;
                    }
                }
                // Si on rencontre une virgule, on interrompt la lecture si elle l'était
                else if ($char === ',') {
                    $should_read = false;
                }
                // Si on doit lire, on ajoute le caractère dans la chaîne tampon
                else if ($should_read) {
                    $current_id .= $char;
                }
            }

            foreach ($found_ids as $id_f) {
                $stmt_assoc->bind_param("iss", $id_insert, $id_f, $assocs_species[$i - 6]);

                /* execute query */
                $stmt_assoc->execute();
            } 

            // Si il y a eu des matches ($m[1] représente tous les matches fait pour la PREMIÈRE parenthèse capturante,
            // $m[0] représente le match entier)
            // if (isset($m[1])) {
            //     // Pour chaque match dans la parenthèse
            //     foreach($m[1] as $key => $match) { 
            //         // On extrait la "ligne" entière qui est dans $m[0] (full-match) [$key] (et on l'escape)
            //         $full_line = mysqli_real_escape_string($sql, $m[0][$key]);
            //         // Pour récupérer l'ID, c'est la première valeur de la "parenthèse" séparée par des ,
            //         // On récupère donc le premier élément du split
            //         $id = explode(',', $match)[0];

            //         // On insère le gène dans les associations
            //         /* bind parameters for markers */
            //         $stmt_assoc->bind_param("isss", $id_insert, $id, $assocs_species[$i - 6], $full_line);

            //         /* execute query */
            //         $stmt_assoc->execute();
            //     }
            // }
        }
    }

    fclose($h);
}

function emptyTables() : void {
    global $sql;

    mysqli_query($sql, "DELETE FROM Gene;");
    mysqli_query($sql, "DELETE FROM GeneAssociations;");
    mysqli_query($sql, "DELETE FROM Pathways;");
    mysqli_query($sql, "ALTER TABLE Gene AUTO_INCREMENT=1;");
    mysqli_query($sql, "ALTER TABLE Pathways AUTO_INCREMENT=1;");
}

if (isUserLogged()) {
    session_write_close();

    if (isset($_POST['file'], $_POST['species']) && is_string($_POST['file']) && is_string($_POST['species'])) {
        global $assocs_species;

        $assocs_species = explode(',', $_POST['species']);

        $file = $_POST['file'];

        $empty = (isset($_POST['empty']) && $_POST['empty'] === 'true');

        $path = $_SERVER['DOCUMENT_ROOT'] . '/assets/db/' . $file;

        if (file_exists($path) && !is_dir($path)) {
            set_time_limit(30 * 10);

            if ($empty)
                emptyTables();

            explodeFile($path, false);
        }

    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

