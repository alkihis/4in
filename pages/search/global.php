<?php

////// AVANCEE //////
function searchAdvanced() : array {
    $r = [];

    global $sql;
    // Récupération des pathways disponibles
    $path = mysqli_query($sql, "SELECT DISTINCT pathway FROM Pathways;");

    $r['form_data']['pathways'] = [];
    while ($row = mysqli_fetch_assoc($path)) {
        $r['form_data']['pathways'][] = $row['pathway'];
    }

    // Récuparation des espèces
    $r['form_data']['species'] = array_keys(SPECIE_TO_NAME);
    if (LIMIT_GENOMES && !isAdminLogged()) { 
        // Si les espèces sont protégées et que l'utilisateur n'est pas connecté, on limite les espèces
        $r['form_data']['species'] = array_diff($r['form_data']['species'], getProtectedSpecies());
    }

    // ____ FORMULAIRE ____
    if (isset($_GET['global']) && is_string($_GET['global'])) {
        $r['form_data']['global'] = htmlspecialchars($_GET['global'], ENT_QUOTES);

        // ___ PATHWAYS ____
        $selected_pathways = [];
        $r['form_data']['selected_p'] = [];
        if (isset($_GET['pathways']) && is_array($_GET['pathways'])) {
            foreach ($_GET['pathways'] as $p) {
                if ($p !== 'all') // Enregistre uniquement si c'est un pathway spécial
                    $selected_pathways[] = mysqli_real_escape_string($sql, $p);
                
                $r['form_data']['selected_p'][$p] = true;
            }
        }
        if (count($selected_pathways) === count($r['form_data']['pathways'])) {
            // Si on a sélectionné tous les pathways, c'est comme si on ne filtrait pas
            $selected_pathways = [];
        }

        // ___ SPECIES ____
        $selected_species = [];

        $r['form_data']['selected_s'] = [];
        if (isset($_GET['species']) && is_array($_GET['species'])) {
            foreach ($_GET['species'] as $p) {
                if ($p !== 'all') // Enregistre uniquement si c'est un pathway spécial
                    $selected_species[] = mysqli_real_escape_string($sql, $p);
                
                $r['form_data']['selected_s'][$p] = true;
            }
        }
        if (count($selected_species) === count($r['form_data']['species'])) {
            // Si on a sélectionné toutes les espèces
            $selected_species = [];
        }

        // ___ KEYWORDS ____
        $query = '';

        // Recherche du nom dans la base de données
        // On éclate en fonction des ""
        $global = [];
        preg_match_all('/"(.*?)"/ium', $_GET['global'], $global);

        if (!empty($global) && isset($global[1])) {
            $global = $global[1];
        }
        else {
            $global = [];
        }

        // Remet la chaîne global à zéro pour supprimer les mots vides/invalides
        // lors du traitement
        $_GET['global'] = "";

        // Traitement des mots clés
        foreach ($global as $word) {
            $word = trim($word);

            if ($word === "" || strlen($word) < 2)
                continue;

            $query = makeAdvancedQuery($word, $query);
            $_GET['global'] .= "\"$word\" ";
        }
        
        if ($query) { 
            // Si jamais on a écrit une requête, on l'entoure de parenthèses pour pouvoir y
            // ajouter des composantes
            $query = "($query)";
        }

        // ___ PATHWAYS TREATEMENT ____
        if (!empty($selected_pathways)) {
            $path_q = '';

            if ($query) {
                $query .= " AND ";
            }

            $query .= '(';

            foreach ($selected_pathways as $p) {
                if ($path_q !== '') {
                    $path_q .= " OR ";
                }
                
                $path_q .= "pa.pathway = '$p'";
            }

            $query .= $path_q . ')';
        }

        // ___ SPECIES TREATEMENT ____
        if (!empty($selected_species)) {
            $spec_q = '';

            if ($query) {
                $query .= " AND ";
            }

            $query .= '(';

            foreach ($selected_species as $p) {
                if ($spec_q !== '') {
                    $spec_q .= " OR ";
                }
                
                $spec_q .= "a.specie = '$p'";
            }

            $query .= $spec_q . ')';
        }

        // ____ FINAL QUERY ____
        if ($query) {
            $finalquery = "SELECT g.*, a.gene_id, a.specie, a.linkable, a.alias, 
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
            LEFT JOIN Pathways pa ON pa.id=g.id
            WHERE $query
            GROUP BY a.gene_id, g.id ORDER BY g.gene_name, g.id, a.specie";

            $q = mysqli_query($sql, $finalquery);

            if (!$q) {
                throw new UnexpectedValueException("SQL request failed");
            }

            if (mysqli_num_rows($q)) { // Il y a un nom trouvé, on le récupère
                while($row = mysqli_fetch_assoc($q)) { // Il peut y avoir plusieurs occurences, on met ça dans une boucle
                    if (LIMIT_GENOMES && !isAdminLogged() && isProtectedSpecie($row['specie'])) {
                        continue;
                    }

                    $r['results'][] = new Gene($row);
                } 
                // results empêche la génération du formulaire de recherche,
                // et affiche les résultats à la page

                $q->free();
            }
            else {
                $r['results'] = [];
            }
        }
        else {
            $r['form_data']['empty_search'] = true;
        }
    }
    else {
        $_GET['family'] = $_GET['names'] = $_GET['fnames'] = $_GET['subfamily'] = $_GET['ids'] = $_GET['functions'] = true;
    }

    return $r;
}

function makeAdvancedQuery(string $word, string $query) : string {
    global $sql;
    $word = mysqli_real_escape_string($sql, $word);
    $word = addcslashes($word, '%_');

    if (isset($_GET['names'])) {
        if ($query != '') {
            $query=$query . " OR g.gene_name LIKE '%$word%'";
        }
        else {
            $query=$query . "g.gene_name LIKE '%$word%'";
        }
    }
    if (isset($_GET['fnames'])) {
        if ($query != '') {
            $query=$query . " OR g.fullname LIKE '%$word%'";
        }
        else {
            $query=$query . "g.fullname LIKE '%$word%'";
        }
    }
    if (isset($_GET['ids'])) {
        if ($query != '') {
            $query=$query . " OR a.gene_id LIKE '$word%'";
        }
        else {
            $query=$query . "a.gene_id LIKE '$word%'";
        }
    }
    if (isset($_GET['family'])) {
        if ($query != '') {
            $query .= " OR ";
        }

        $query .= "g.family LIKE '$word%'";
    }
    if (isset($_GET['subfamily'])) {
        if ($query != '') {
            $query .= " OR ";
        }

        $query .= "g.subfamily LIKE '$word%'";
    }
    if (isset($_GET['functions'])) {
        if ($query != '') {
            $query=$query . " OR g.func LIKE '$word%'";
        }
        else {
            $query=$query . "g.func LIKE '$word%'";
        }
    }
    return $query;
}

function showGlobalSearch(array $data) : void {
    generateSearchForm('global', $data['form_data'] ?? []);

    if (isset($data['results'])) {
        generateSearchResultsArray($data['results']);
    }
}
