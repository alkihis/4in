<?php 

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
    try {
        $gene = new Gene($args[0]);
    } catch (RuntimeException $e) {
        throw new PageNotFoundException("Gene does not exists.");
    }

    // Le gene est récupéré : il faut vérifier si on a les droits de lecture (certaines
    // espèces ont des génomes non publiés) : Si l'utilisateur n'est pas connecté: affichage interdit
    if (LIMIT_GENOMES && isProtectedSpecie($gene->getSpecie()) && !isUserLogged()) {
        throw new ForbiddenPageException;
    }

    $link = getLinkForId($gene->getID(), $gene->getSpecie(), $gene->getAlias());

    if ($link && !isProtectedSpecie($gene->getSpecie())) {
        if (!$gene->isLinkDefined()) { // linkable vaut null, on checke si il est valide
            $is_ok = checkSaveLinkValidity($gene->getSpecie(), $gene->getAlias() ?? $gene->getID(), (bool)($gene->getAlias()));

            if (!$is_ok) {
                $link = null;
            }
        }
        else if (!$gene->hasLink()) { // linkable vaut 0
            $link = null;
        }
        // Sinon : linkable vaut 1, le lien vers le gène est valide
    }

    $gene_id = mysqli_real_escape_string($sql, $gene->getID());

    $q = mysqli_query($sql, "SELECT specie, gene_id
        FROM GeneAssociations
        WHERE id={$gene->getRealID()}
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

    return new Controller($arr, htmlspecialchars($gene->getID()));
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
    <div class="linear-nav-to-white top-float"></div>
    <div class="container">
        <h2 class="gene-id white-text"> 
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

        if (isContributorLogged()) {
            echo "<h6><a href='/modify/{$data['gene']->getID()}' class='sub green-text'>
                <i class='material-icons left'>mode_edit</i>Edit
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
                    $str[] = "<h4>Pathway</h4><div class='black-text'>" . implode('<br>', $data['gene']->getPathways()) . '</div><br>';
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

            <div class='black-text orthologues'>
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
                if (isContributorLogged()) {
                    echo "<h6><a href='/add_o/{$data['gene']->getID()}' class='sub blue-text'>
                        <i class='material-icons left'>add</i>Add homologous
                    </a></h6>";
                }
            echo "</div>";

            if ($data['gene']->getSeqADN() || $data['gene']->getSeqProt()) {
                echo '<div class="divider divider-margin divider-color" style="margin-bottom: 1.52rem;"></div>';

                echo "<div class='download-results row col s12'>";
                    if ($data['gene']->getSeqADN()) { ?>
                        <div class='col s12 l6'>
                            <a href='#!' class='btn-flat btn-perso purple-text right' 
                                onclick="downloadCheckedSequences('adn', true);">
                                <i class='material-icons left'>file_download</i>DNA Sequence
                            </a>
                        </div>
                    <?php }

                    if ($data['gene']->getSeqProt()) { ?>
                        <div class='col s12 l6'>
                            <a href='#!' class='btn-flat btn-perso blue-text left' 
                                onclick="downloadCheckedSequences('pro', true);">
                                <i class='material-icons left'>file_download</i>Protein sequence
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
