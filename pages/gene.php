<?php 
require 'inc/GeneObject.php';

// Page de gène : informations et liens

/**
 * geneControl
 * Contrôleur de la page de gène
 * 
 * Doit, en fonction de l'ID de gène passé dans $args[0],
 * récupérer ses informations: famille, nom, espèce, gènes orthologues chez les autres espèces,
 * et son lien vers ArthroCyc-truc si possible. Doit également récupérer sa séquence.
 *
 * @param array $args
 * @return Controller
 */
function geneControl(array $args) : Controller {
    if (!isset($args[0])){
        throw new PageNotFoundException;
    }

    global $sql;
    // Recherche de l'identifiant dans la base de données
    $id = mysqli_real_escape_string($sql, $args[0]);

    $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, 
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
    WHERE a.gene_id = '$id'
    GROUP BY a.gene_id, g.id");

    if (mysqli_num_rows($q) === 0){
        throw new PageNotFoundException;
    }

    $row = mysqli_fetch_assoc($q);
    $gene = new GeneObject($row);
    $gene_id = mysqli_real_escape_string($sql, $row['gene_id']);

    $q = mysqli_query($sql, "SELECT specie, gene_id
        FROM GeneAssociations
        WHERE id='{$row['id']}'
        AND gene_id != '$gene_id'
    ");

    $array = [];
    while ($row = mysqli_fetch_assoc($q)){
        $specie_en_cours = $row['specie'];
        $id_en_cours = $row['gene_id'];
        $array[$specie_en_cours][] = $id_en_cours;
    }

    $arr = ['gene' => $gene, 'orthologues' => $array];

    return new Controller($arr, $id);
}

/**
 * geneView
 * Vue de la page de gène
 * 
 * Doit afficher correctement, en HTML, à l'écran, les informations contenues
 * dans le contrôleur, récupérées dans geneControl
 *
 * @param Controller $c
 * @return void
 */
function geneView(Controller $c) : void {
    // TODO
    $data = $c->getData(); 
    ?>
    <div class="container">
        <h2> <?= $data['gene']->getID(); ?> </h2>
        <div class="section">
             <div class="light text-justify flow-text">
                <?php 
                if ($data['gene']->getName())
                    echo "<h4>Name</h4>" . $data['gene']->getName();

                if ($data['gene']->getFullname())
                    echo "<h4>Fullname</h4>" . $data['gene']->getFullname() . "<br>";  
                
                echo '<h4>Specie</h4> ' . $data['gene']->getSpecie();

                if ($data['gene']->getFamily()) {
                    echo "<h4> Family <br></h4>" . $data['gene']->getFamily();
                }
                
                if ($data['gene']->getSubFamily())
                    echo "<h4> Sub-family<br></h4>" . $data['gene']->getSubFamily();

                if ($data['gene']->getFunction())
                    echo "<h4>Function</h4>" . $data['gene']->getFunction();

                if (empty($data['gene']->getPathways())) {
                    echo "<h4>Pathways</h4>";
                    foreach ($data['gene']->getPathways() as $element) {
                        echo $element . "<br>";
                    }
                }
                ?>
                <div class="divider divider-margin divider-color"></div>
                <?php
                if (count($data['orthologues'])) {
                    echo "<h4>Homologous</h4>";

                    $first = true;
                    foreach (array_keys($data['orthologues']) as $specie) {
                        if ($first)
                            $first = false;
                        else
                            echo ", ";
                        echo "<span class='specie underline-hover blue-text text-darken-3 pointer' data-genes='" . 
                            implode(',', $data['orthologues'][$specie]) . 
                        "'>$specie</span>";
                    }
                }
                    
                // autre séparateur 
                // liste des espèces avec des homologues et liens vers les pages concernées

                ?>
            </div>
        </div>
    </div>  
    
    
    
    <?php
}
