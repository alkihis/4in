<?php

////// NAME //////
function searchByName() : array {
    $r = [];

    if (isset($_GET['name']) && is_string($_GET['name']) && $_GET['name']) {
        $r['form_data'] = [];
        $r['form_data']['name'] = htmlspecialchars($_GET['name'], ENT_QUOTES);

        global $sql;
        // Recherche du nom dans la base de données
        $name = mysqli_real_escape_string($sql, $_GET['name']);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, a.linkable, a.alias,
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
             FROM Pathways p 
             WHERE g.id = p.id) as pathways,
            (CASE 
                WHEN a.sequence_adn IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_adn,
            (CASE 
                WHEN a.sequence_pro IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_pro
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE g.gene_name LIKE '$name%'
        GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        $r['results'] = [];

        if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
            while($row = mysqli_fetch_assoc($q)) { // Il peut y avoir plusieurs occurences, on met ça dans une boucle
                // Filtre les gènes protégés
                if (LIMIT_GENOMES && !isUserLogged() && isProtectedSpecie($row['specie'])) {
                    // Si le génome est protégé, on l'insère pas dans le tableau
                    continue;
                }

                $r['results'][] = new Gene($row);
            } 
            // results empêche la génération du formulaire de recherche,
            // et affiche les résultats à la page

            $q->free();
        }
    }

    return $r;
}

function showSearchByName(array $data) : void {
    generateSearchForm('name', $data['form_data'] ?? []);

    if (isset($data['results'])) {
        generateSearchResultsArray($data['results']);
    }
}
