<?php
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
