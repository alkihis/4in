<?php

function emptyTables() : void {
    global $sql;

    mysqli_query($sql, "DELETE FROM Gene;");
    mysqli_query($sql, "DELETE FROM GeneAssociations;");
    mysqli_query($sql, "DELETE FROM Pathways;");
    mysqli_query($sql, "ALTER TABLE Gene AUTO_INCREMENT=1;");
    mysqli_query($sql, "ALTER TABLE Pathways AUTO_INCREMENT=1;");
}

/**
 * insertPathway
 *
 * Insère le pathway souhaité pour le gène $id_gene dans la base de données
 * @param integer $id_gene
 * @param string $pathway
 * @return void
 */
function insertPathway(int $id_gene, string $pathway) : void {
    global $sql;
    mysqli_query($sql, "INSERT INTO Pathways 
        (id, pathway)
        VALUES
        ($id_gene, '$pathway');
    ");
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
        $func = trim(mysqli_real_escape_string($sql, $arr[1]));
        $pathway = trim(mysqli_real_escape_string($sql, $arr[2]));
        $name = trim(mysqli_real_escape_string($sql, $arr[0]));
        $fullname = trim(mysqli_real_escape_string($sql, $arr[3]));
        $family = trim(mysqli_real_escape_string($sql, $arr[4]));
        $sub = trim(mysqli_real_escape_string($sql, $arr[5]));

        // Insère ces données dans le Gene
        $q = mysqli_query($sql, "INSERT INTO Gene 
            (func, gene_name, fullname, family, subfamily)
            VALUES
            ('$func', '$name', '$fullname', '$family', '$sub');
        ");

        // Si l'insertion a réussi
        if ($q) {
            // Récupération de l'ID numérique d'insertion (clé primaire de Gene)
            $id_insert = mysqli_insert_id($sql);

            // Si un pathway est défini, on le split en fonction de /, et on insère autant de pathways que défini dans Pathways,
            // ceux-ci étant reliés au Gene par son ID
            if (!empty($pathway)) {
                insertPathway($id_insert, $pathway);

                // $paths = explode('/', $pathway);
                
                // foreach ($paths as $p) {
                //     if (!empty(trim($p)))
                        
                // }
            }

            // Les espèces présentes, dans le bon ordre
            $assocs_species = ['Soryzae', 'Apisum', 'Agambiae', 'Amellifera', 
                'Bmori', 'Cfloridanus', 'Dmelanogaster', 'Gmorsitans',
                'Msexta', 'Nvitripennis', 'Phumanus', 'Pxylostela',
                'Tcastaneum', 'Sinvicta', 'Dponderosae', 'Aaegypti'
            ];

            // Pour les lignes 6 à 21 du tableau
            for ($i = 6; $i < 22; $i++) {
                $m = [];
                // Enregistre les matches de l'expression régulière ci-dessous dans $m
                preg_match_all("/\((.+?)\),?/m", $arr[$i], $m);

                // Si il y a eu des matches ($m[1] représente tous les matches fait pour la PREMIÈRE parenthèse capturante,
                // $m[0] représente le match entier)
                if (isset($m[1])) {
                    // Pour chaque match dans la parenthèse
                    foreach($m[1] as $key => $match) { 
                        // On extrait la "ligne" entière qui est dans $m[0] (full-match) [$key] (et on l'escape)
                        $full_line = mysqli_real_escape_string($sql, $m[0][$key]);
                        // Pour récupérer l'ID, c'est la première valeur de la "parenthèse" séparée par des ,
                        // On récupère donc le premier élément du split
                        $id = explode(',', $match)[0];

                        // On insère le gène dans les associations
                        mysqli_query($sql, "INSERT INTO GeneAssociations
                        (id, gene_id, sequence_adn, sequence_pro, specie, addi)
                        VALUES ($id_insert, '$id', NULL, NULL, '{$assocs_species[$i - 6]}', '$full_line')"); 
                    }
                }
            }
        }
    }

    fclose($h);
}

function getGenesWithPathways(bool $compact) : array {
    // Dans une fonction de Controller, il ne doit subsister AUCUN echo !
    // Rien n'est affiché à l'écran, tout est simplement récupéré / calculé puis renvoyé
    // L'avantage majeur est que cela permet de pouvoir réorganiser l'affichage sans modifier tout
    // le code de récupération de ces données

    global $sql;

    // Initialise le tableau à renvoyer au contrôleur plus tard
    $return_values = [];

    if ($compact) { // Groupe les colonnes espèce et ID de gène pour un résultat réduit
        $q = mysqli_query($sql, "SELECT GROUP_CONCAT(DISTINCT a.specie SEPARATOR ', ') as specie, 
        g.*, 
        GROUP_CONCAT(DISTINCT a.gene_id SEPARATOR ', ') as gene_id
        FROM GeneAssociations a 
        JOIN Gene g 
        ON a.id=g.id 
        GROUP BY g.id");
    } 
    else {
        $q = mysqli_query($sql, "SELECT a.gene_id, a.specie, g.* FROM GeneAssociations a JOIN Gene g ON a.id=g.id;");
    }

    if (!$q) {
        throw new Exception("La base de données est hors ligne ou la requête a échoué");
    }
    
    if (mysqli_num_rows($q) > 0) {
        while ($row = mysqli_fetch_assoc($q)) {
            $pathway = mysqli_query($sql, "SELECT pathway FROM Pathways WHERE id={$row['id']}");
            $pathways = [];

            if (mysqli_num_rows($pathway)) {
                while ($row_pathway = mysqli_fetch_assoc($pathway)) {
                    $pathways[] = $row_pathway['pathway'];
                }
            }

            // Ajoute les pathways à la ligne
            $row['pathways'] = implode(', ', $pathways);
            $return_values[] = $row; // ajoute la ligne entière de la base de données au tableau
        }
    }

    return $return_values;
}

/**
 * showGenesWithPathways
 * 
 * Affiche sur la page toutes les caractéristiques des gènes et leur voie
 * @return void
 */
function showGenesWithPathways(array $data) : void {
    // On va constater que RIEN n'est calculé ici, aucun appel à la BDD autorisé
    // On doit uniquement exploiter les données fournies
    ?>
    <table>
        <thead>
            <tr>
                <th>Gene ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Pathway</th>
                <th>Fullname</th>
                <th>Family</th>
                <th>Subfamily</th>
                <th>Specie</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data as $row) { 
            // On extrait la ligne des données telle qu'elle a été construite dans la formation du contrôleur
            echo '<tr>';

            // Écriture de la table
            ?>
                <td><?= $row['gene_id'] ?></td>
                <td><?= $row['gene_name'] ?></td>
                <td><?= $row['func'] ?></td>
                <td><?= $row['pathways'] ?></td>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['family'] ?></td>
                <td><?= $row['subfamily'] ?></td>
                <td><?= $row['specie'] ?></td>
            <?php

            echo '</tr>';
        }

        ?>
        </tbody>
    </table>
    <?php
}

function readFileControl($args) : Controller {
    // Dans le contrôleur, on exploite le GET ou le POST
    if (isset($_GET['refresh']) && $_GET['refresh'] === 't') {
        emptyTables();
        explodeFile('new_tab.tsv', true);
    }

    $compact = true;
    if (isset($args[0]) && $args[0] === 'full') {
        $compact = false;
    }

    $data = getGenesWithPathways($compact);

    // On donne les données au contrôleur
    return new Controller($data, 'View database');
}

function readFileView(Controller $c) : void {
    showGenesWithPathways($c->getData());
}
