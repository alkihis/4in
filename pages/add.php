<?php 

// Page d'ajout de gene
function addControl(array $args) : Controller {
    if (!isUserLogged()) {
        throw new ForbiddenPageException;
    }

    global $sql;
    // Recherche de l'identifiant dans la base de données
    $q = mysqli_query($sql, "SELECT DISTINCT pathway FROM Pathways;");
    $pathways = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $pathways[] = $row['pathway'];
    }
    
    $data = ['pathways' => $pathways, 'species' => getOrderedSpecies()];

    if (isset($_POST['role'], $_POST['name'], $_POST['fullname'], $_POST['family'], $_POST['subf']) &&
        isset($_POST['specie'], $_POST['pro_seq'], $_POST['adn_seq'], $_POST['id_new'], $_POST['addi'], $_POST['alias'])) {

        if (!isset($_POST['pathway'])) {
            $_POST['pathway'] = [];
        }

        if (is_array($_POST['pathway'])) {
            $pathway = array_unique($_POST['pathway']);

            $g_sql_id = constructNewGene(
                $_POST['name'], $_POST['fullname'], $_POST['family'], $_POST['subf'], $_POST['role'], $pathway
            );

            if ($g_sql_id) {
                $r = constructNewOrthologue(
                    $g_sql_id, 
                    $_POST['specie'], 
                    $_POST['id_new'], 
                    $_POST['addi'], 
                    $_POST['alias'], 
                    $_POST['pro_seq'], 
                    $_POST['adn_seq']
                );
        
                $data['creation'] = [$r, trim($_POST['id_new'])];
            } 
            else {
                $data['no_gene'] = true;
            }
        }
    }

    return new Controller($data, 'Add gene');
}

function addView(Controller $c) : void {
    $data = $c->getData(); 

    ?>
    <div class="linear-nav-to-white top-float"></div>
    <div class="container">
        <h2 class="gene-id white-text"> 
        Add new gene
        </h2>

        <?php if (isset($data['creation'])) {
            if ($data['creation'][0] === 0) {
                // Réussite
                ?>
                <h4 class="light-text" style="margin-top: 5px;">New gene has been added</h4>
                <div class="section">
                    <div class="light text-justify flow-text">
                        Check your new gene <a target="_blank" href="/gene/<?= $data['creation'][1] ?>">here</a>.<br>
                        You can go to this page to create homologous.
                    </div>
                </div>
                <?php
            }
            else if ($data['creation'][0] === 1) {
                ?>
                <h4 class="light-text" style="margin-top: 5px;">ID is empty</h4>
                <div class="section">
                    <div class="light text-justify flow-text red-text">
                        You can't create a gene with empty ID.
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
                        Specie can't be empty.
                    </div>
                </div>
                <?php
            }
            else {
                ?>
                <h4 class="light-text" style="margin-top: 5px;">Unable to add gene</h4>
                <div class="section">
                    <div class="light text-justify flow-text red-text">
                        An unknown error occurred.
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <div id="base_path" class="hide">
            <?php 
            foreach ($data['pathways'] as $s) {
                echo "<option value='$s'>$s</option>";
            }
            ?>
            <option value="">__Enter new pathway__</option>
        </div>

        <form class="row" action="#" method="post">
            <div class="section">
                <div class="light text-justify flow-text">
                    <h4 class="light-text" style="margin-top: 15px;">Common gene specifications</h4>
                    <h6 class="light-text" style="margin-bottom: 20px;">
                        All homologous of this gene will share this specifications.
                    </h6>

                    <div class="input-field col s12">
                        <input type="text" name="name" id="name" autocomplete='off' class="validate" 
                        value="<?= htmlspecialchars($_POST['name'] ?? "", ENT_QUOTES) ?>"
                        placeholder="Must contain only alpha-numerical characters or .-_" pattern="[a-zA-Z.\-_0-9]+">
                        <label for='name'>Gene name</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="fullname" id="fullname" autocomplete='off' class="validate" 
                        value="<?= htmlspecialchars($_POST['fullname'] ?? "", ENT_QUOTES) ?>"
                        placeholder="Must contain only alpha-numerical characters or .-_" pattern="[a-zA-Z.\-_0-9]+">
                        <label for='fullname'>Fullname</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="role" id="role" autocomplete='off' 
                        value="<?= htmlspecialchars($_POST['role'] ?? "", ENT_QUOTES) ?>">
                        <label for='role'>Role</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="family" id="family" autocomplete='off' 
                        value="<?= htmlspecialchars($_POST['family'] ?? "", ENT_QUOTES) ?>">
                        <label for='family'>Family</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="subf" id="subf" autocomplete='off' 
                        value="<?= htmlspecialchars($_POST['subf'] ?? "", ENT_QUOTES) ?>">
                        <label for='subf'>SubFamily</label>
                    </div>

                    <div id="s-container">
                    </div>

                    <div class="col s12">
                        <a href="#!" class="tiny-text" onclick="addPathway()">Add pathway</a>

                        <div class="divider divider-margin"></div>
                    </div>

                    <div class="divider divider-margin"></div>

                    <h4 class="light-text">Unique details</h4>
                    <h6 class="light-text" style="margin-bottom: 20px;">
                        Possible homologous of this gene will not share this details.
                    </h6>
                    
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

                    <button type="submit" class="btn-flat btn-perso blue-text right">Add gene and its details</button>

                    <div class="clearb"></div>
                </div>
            </div>
        </form>

        <div class="row no-margin-bottom">
            <div id="modal_modif" class="modal"></div>
        </div>
    </div>  

    <script src="/js/modify.js"></script>
    <?php
}
