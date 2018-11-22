<?php

////// ID //////
function searchById() : array {
    $r = [];

    if (isset($_GET['id']) && is_string($_GET['id']) && strlen($_GET['id']) > 0) {
        $r['form_data'] = [];
        $r['form_data']['id'] = htmlspecialchars($_GET['id'], ENT_QUOTES);

        global $sql;
        // Recherche de l'identifiant dans la base de données
        $id = mysqli_real_escape_string($sql, $_GET['id']);

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
        WHERE a.gene_id LIKE '$id%'
        GROUP BY a.gene_id, g.id ORDER BY g.id, a.specie");

        if (!$q) {
            throw new UnexpectedValueException("SQL request failed");
        }

        $r['results'] = [];

        if (mysqli_num_rows($q)) { // Il y a un identifiant trouvé, on le récupère
            while ($row = mysqli_fetch_assoc($q)) {
                // results empêche la génération du formulaire de recherche,
                // et affiche les résultats à la page

                // Filtre les gènes protégés
                if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isAdminLogged()) {
                    // Si le génome est protégé, on l'insère pas dans le tableau
                    continue;
                }

                $r['results'][] = new Gene($row);
            } 

            $q->free();

            if (count($r['results']) === 1) {
                // Si le nombre de résultat est de 1 (un seul ID),
                // on redirige immédiatement vers la page de gène
                header('Location: /gene/' . $r['results'][0]->getID());
            }
        }
    }

    return $r;
}

function showSearchById(array $data) : void {
    generateSearchForm('id', $data['form_data'] ?? []);

    if (isset($data['results'])) { // résultats : tableau de résultat à générer
        generateSearchResultsArray($data['results']);
    }
}
