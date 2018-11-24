<?php

////// PATHWAY //////
function searchPathway() : array {
    $r = [];

    // On récupère d'abord les différents pathways disponibles
    global $sql;
    $q = mysqli_query($sql, "SELECT DISTINCT pathway FROM Pathways;");

    if (!$q) {
        throw new RuntimeException("Inoperative SQL base");
    }

    $r['form_data'] = [];
    $r['form_data']['no_search_btn'] = true;

    while($row = mysqli_fetch_assoc($q)) {
        $r['form_data']['select'][] = $row['pathway'];
    }

    if (isset($_GET['pathway']) && is_string($_GET['pathway']) && $_GET['pathway']) {
        $r['form_data']['pathway'] = htmlspecialchars($_GET['pathway'], ENT_QUOTES);

        // Recherche du nom dans la base de données
        // Normalement, le pathway est UNIQUEMENT un md5, on cherche donc avec la fonction sql MD5
        $pathway = mysqli_real_escape_string($sql, $_GET['pathway']);

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
        JOIN Pathways pa ON pa.id=g.id
        WHERE MD5(pa.pathway)='$pathway'
        GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        $r['results'] = [];

        if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
            while($row = mysqli_fetch_assoc($q)) {
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

function showSearchByPathway(array $data) : void {
    generateSearchForm('pathway', $data['form_data'] ?? []);

    if (isset($data['results'])) {
        generateSearchResultsArray($data['results']);
    }
}
