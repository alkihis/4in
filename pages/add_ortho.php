<?php 

// Page d'ajout d'orthologue / homologue
function addOControl(array $args) : Controller {
    if (!isset($args[0])){
        throw new PageNotFoundException;
    }

    if (!isContributorLogged()) {
        throw new ForbiddenPageException;
    }

    global $sql;
    // Recherche de l'identifiant dans la base de données
    try {
        $gene = new Gene($args[0]);
    } catch (RuntimeException $e) {
        throw new PageNotFoundException;
    }
    
    $data = ['gene' => $gene, 'species' => getOrderedSpecies()];

    if (isset($_POST['specie'], $_POST['pro_seq'], $_POST['adn_seq'], $_POST['id_new'], $_POST['addi'], $_POST['alias'])) {
        $r = constructNewOrthologue(
            $gene->getRealID(), 
            $_POST['specie'], 
            $_POST['id_new'], 
            $_POST['addi'], 
            $_POST['alias'], 
            $_POST['pro_seq'], 
            $_POST['adn_seq']
        );

        $data['creation'] = [$r, trim($_POST['id_new'])];
    }

    return new Controller($data, 'Add homologous of ' . $gene->getID());
}

function addOView(Controller $c) : void {
    $data = $c->getData(); 

    ?>
    <div class="linear-nav-to-white top-float"></div>
    <div class="container">
        <h2 class="gene-id white-text"> 
        Add homologous of 
        <?php 
            echo $data['gene']->getID(); 
        ?> 
        </h2>

        <?php if (isset($data['creation'])) {
            if ($data['creation'][0] === 0) {
                // Réussite
                ?>
                <h4 class="light-text" style="margin-top: 5px;">New homologous has been added</h4>
                <div class="section">
                    <div class="light text-justify flow-text">
                        Check your new gene <a target="_blank" href="/gene/<?= $data['creation'][1] ?>">here</a>.<br>
                        You are free to re-use this form again to add another homologous gene.
                    </div>
                </div>
                <?php
            }
            else if ($data['creation'][0] === 1) {
                ?>
                <h4 class="light-text" style="margin-top: 5px;">ID is empty</h4>
                <div class="section">
                    <div class="light text-justify flow-text red-text">
                        You can't create a gene without an ID.
                    </div>
                </div>
                <?php
            }
            else if ($data['creation'][0] === 2) {
                ?>
                <h4 class="light-text" style="margin-top: 5px;">Gene already exists</h4>
                <div class="section">
                    <div class="light text-justify flow-text red-text">
                        Specified ID is already linked to another gene. Please check your informations.
                    </div>
                </div>
                <?php
            }
            else if ($data['creation'][0] === 3) {
                ?>
                <h4 class="light-text" style="margin-top: 5px;">Specie is empty</h4>
                <div class="section">
                    <div class="light text-justify flow-text red-text">
                        Species field can't be empty.
                    </div>
                </div>
                <?php
            }
            else {
                ?>
                <h4 class="light-text" style="margin-top: 5px;">Unable to add gene</h4>
                <div class="section">
                    <div class="light text-justify flow-text red-text">
                        An unknown error has occurred.
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <h4 class="light-text" style="margin-top: 5px;">Homologous gene specifications</h4>
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
                <div class="divider divider-margin"></div>

                <h4 class="light-text">New homologous gene</h4>
                
                <form class="row" action="#" method="post">
                    <div class="input-field col s12">
                        <select name="specie">
                            <?php 
                            $selc = $_POST['specie'] ?? null;

                            foreach ($data['species'] as $s) {
                                $selected = ($selc === $s ? "selected" : "");
                                echo "<option value='$s' $selected>$s</option>";
                            }
                            ?>
                        </select>
                        <label>Specie</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="id_new" id="id_new" autocomplete='off' class="validate" 
                        value="<?= htmlspecialchars($_POST['id_new'] ?? "", ENT_QUOTES) ?>"
                        required placeholder="Must contain only alpha-numerical characters or .-_" pattern="[a-zA-Z.\-_0-9]+">
                        <label for='id_new'>Identifier</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="addi" id="addi" autocomplete='off' 
                        value="<?= htmlspecialchars($_POST['addi'] ?? "", ENT_QUOTES) ?>">
                        <label for='addi'>Miscellaneous informations</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="alias" id="alias" autocomplete='off' 
                        value="<?= htmlspecialchars($_POST['alias'] ?? "", ENT_QUOTES) ?>">
                        <label for='alias'>Alias</label>
                    </div>

                    <div class="input-field col s12">
                        <textarea class="materialize-textarea" name="adn_seq" placeholder="Leave empty for no sequence"
                            id="adn_seq"><?= htmlspecialchars($_POST['adn_seq'] ?? "") ?></textarea>
                        <label for='adn_seq'>DNA sequence</label>
                    </div>

                    <div class="input-field col s12">
                        <textarea class="materialize-textarea" name="pro_seq" placeholder="Leave empty for no sequence"
                            id="pro_seq"><?= htmlspecialchars($_POST['pro_seq'] ?? "") ?></textarea>
                        <label for='pro_seq'>Proteic sequence</label>
                    </div>

                    <div class="clearb"></div>
                    <div class="divider divider-margin"></div>

                    <button type="submit" class="btn-flat btn-perso blue-text right">Add</button>

                    <div class="clearb"></div>
                </form>
            </div>
        </div>
    </div>  
    <?php
}
