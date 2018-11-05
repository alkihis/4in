<?php 
require 'inc/Gene.php';

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

    $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, a.sequence_adn, a.sequence_pro, a.linkable, a.alias, a.addi,
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

    // Le gene est récupéré : il faut vérifier si on a les droits de lecture (certaines
    // espèces ont des génomes non publiés) : Si l'utilisateur n'est pas connecté: affichage interdit
    if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isUserLogged()) {
        throw new ForbiddenPageException();
    }

    $link = getLinkForId($row['gene_id'], $row['specie'], $row['alias']);

    if ($link && !isProtectedSpecie($row['specie'])) {
        if ($row['linkable'] === null) {
            $is_ok = checkSaveLinkValidity($row['specie'], $row['alias'] ?? $row['gene_id'], (bool)($row['alias']));

            if (!$is_ok) {
                $link = null;
            }
        }
        else if ($row['linkable'] === "0") {
            $link = null;
        }
    }

    $gene = new Gene($row);
    $gene_id = mysqli_real_escape_string($sql, $row['gene_id']);

    $q = mysqli_query($sql, "SELECT specie, gene_id
        FROM GeneAssociations
        WHERE id='{$row['id']}'
        AND gene_id != '$gene_id'
    ");

    $array = [];
    while ($row = mysqli_fetch_assoc($q)){
        $specie_en_cours = $row['specie'];

        if (LIMIT_GENOMES && isProtectedSpecie($row['specie']) && !isUserLogged()) {
            // Si le génome est protégé, on l'insère pas dans le tableau
            continue;
        }

        $id_en_cours = $row['gene_id'];
        $array[$specie_en_cours][] = $id_en_cours;
    }

    $arr = ['gene' => $gene, 'orthologues' => $array, 'link' => $link];

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
    $data = $c->getData(); 

    $link = $data['link'];

    ?>
    <div class="container">
        <h2 class="gene-id"> 
        <?php 
            echo $data['gene']->getID(); 
            if ($data['gene']->getAlias()) {
                echo " <span class='tiny-text lighter-text'>{$data['gene']->getAlias()}</span>";
            }
        ?> 
        </h2>

        <h4 class="light-text" style="margin-top: -10px;"><?= $data['gene']->getSpecie() ?></h4>
        <?php 
        if ($link) {
            echo "<h6><a href='$link' class='sub' target='_blank'>
                <i class='material-icons left'>launch</i>View full informations in external database
            </a></h6>";
        }
        ?>
        <div class="section">
             <div class="light text-justify flow-text">
                <?php 
                $str = [];

                if ($data['gene']->getName()) {
                    $str[] = "<h4>Name</h4><div class='gene-info'>" . $data['gene']->getName() . '</div>';
                }

                if ($data['gene']->getFullname()) {
                    $str[] = "<h4>Fullname</h4><div class='gene-info'>" . $data['gene']->getFullname() . '</div>';  
                }

                if ($data['gene']->getFamily()) {
                    $str[] = "<h4>Family</h4><div class='gene-info'>" . $data['gene']->getFamily() . '</div>';
                }
                
                if ($data['gene']->getSubFamily()) {
                    $str[] = "<h4>Sub-family</h4><div class='gene-info'>" . $data['gene']->getSubFamily() . '</div>';
                }  

                if ($data['gene']->getFunction()) {
                    $str[] = "<h4>Function</h4><div class='gene-info'>" . $data['gene']->getFunction() . '</div>';
                }

                if (!empty($data['gene']->getPathways())) {
                    $str[] = "<h4>Pathway</h4>" . implode('<br>', $data['gene']->getPathways()) . '<br>';
                }

                if ($data['gene']->getAdditionalInfos()) {
                    $str[] = "<h4>Miscellaneous informations</h4><div class='gene-info'>" . 
                        ucfirst($data['gene']->getAdditionalInfos()) . '</div>';
                }

                foreach ($str as $key => $s) { 
                    // Affichage des informations par ligne de deux éléments
                    if ($key % 2 === 0) {
                        echo '<div class="row no-margin-bottom">';
                        if ($key === (count($str)-1)) { // Le dernier élément est un début de ligne : prend toute la ligne
                            echo "<div class='col s12 no-pad'>$s</div></div>";
                        }
                        else {
                            echo "<div class='col s12 l6 no-pad'><div style='width: 95%;'>$s</div></div>";
                        }
                    }
                    else {
                        echo "<div class='col s12 l6 no-pad'><div style='width: 95%;'>$s</div></div>";
                        echo '</div>';
                    }
                }
                
                ?>
            </div>

            <div class="divider divider-header-margin"></div>

            <?php
            if (count($data['orthologues'])) {
                echo "<h4>Homologous</h4>";

                $first = true;
                foreach (array_keys($data['orthologues']) as $specie) {
                    if ($first)
                        $first = false;
                    else
                        echo ", ";

                    $text_specie = ($specie === $data['gene']->getSpecie() ? "$specie (self)" : $specie);
                    echo "<span class='specie underline-hover blue-text text-darken-3 pointer' data-specie='$specie' 
                    data-genes='" . implode(',', $data['orthologues'][$specie]) . 
                    "' onclick='loadOrthologuesModal(this)'>$text_specie</span>";
                }
            }

            if ($data['gene']->getSeqADN() || $data['gene']->getSeqProt()) {
                echo '<div class="divider divider-margin divider-color" style="margin-bottom: 1.52rem;"></div>';

                echo "<div class='download-results row col s12'>";
                    if ($data['gene']->getSeqADN()) { ?>
                        <div class='col s6'>
                            <a href='#!' class='btn-flat btn-perso purple-text right' 
                                onclick="downloadCheckedSequences('adn', true);">
                                <i class='material-icons left'>file_download</i>FASTA sequence (DNA)
                            </a>
                        </div>
                    <?php }

                    if ($data['gene']->getSeqProt()) { ?>
                        <div class='col s6'>
                            <a href='#!' class='btn-flat btn-perso blue-text left' 
                                onclick="downloadCheckedSequences('pro', true);">
                                <i class='material-icons left'>file_download</i>FASTA sequence (Protein)
                            </a>
                        </div>
                    <?php } ?>
                    <div class='clearb'></div>
                    
                    <div class="hide">
                        <input type="checkbox" class="chk-srch" data-id="<?= $data['gene']->getID() ?>">
                    </div>
                </div> 
            <?php
            }

            if ($data['gene']->getSeqADN()) {
                echo '<div class="divider divider-header-margin divider-color"></div>';

                echo '<h4>DNA Sequence</h4>';
                echo '<pre class="break-word">' . $data['gene']->getSeqADN() . '</pre>';
            }

            if ($data['gene']->getSeqProt()) {
                echo '<div class="divider divider-header-margin divider-color"></div>';

                echo '<h4>Protein Sequence</h4>';
                echo '<pre class="break-word">' . $data['gene']->getSeqProt() . '</pre>';
            }
            ?>
        </div>

        <!-- Modal orthologues -->
        <div id="modal-orthologues" class="modal">
        </div>
    </div>  
    <?php
}
