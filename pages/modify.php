<?php 

function updateGeneById(Gene &$g, string $specie, string $dna, string $pro, string $addi, string $alias, bool $linkable) : void {
    $query = [];
    global $sql;

    $specie = trim($specie); $dna = trim($dna); $pro = trim($pro); $addi = trim($addi); $alias = trim($alias);

    if ($linkable !== $g->hasLink()) {
        $query[] = "linkable=" . ($linkable ? '1' : '0');
    }
    if ($specie !== $g->getSpecie()) {
        $query[] = "specie='" . mysqli_real_escape_string($sql, $specie) . "'";
    }
    if ($dna !== (string)$g->getSeqADN()) {
        if ($dna === "") {
            $query[] = "sequence_adn=NULL";
        }
        else {
            $query[] = "sequence_adn='" . mysqli_real_escape_string($sql, $dna) . "'";
        }
    }
    if ($pro !== (string)$g->getSeqProt()) {
        if ($pro === "") {
            $query[] = "sequence_pro=NULL";
        }
        else {
            $query[] = "sequence_pro='" . mysqli_real_escape_string($sql, $pro) . "'";
        }
    }
    if ($addi !== (string)$g->getAdditionalInfos()) {
        if ($addi === "") {
            $query[] = "addi=NULL";
        }
        else {
            $query[] = "addi='" . mysqli_real_escape_string($sql, $addi) . "'";
        }
    }
    if ($alias !== (string)$g->getAlias()) {
        if ($alias === "") {
            $query[] = "alias=NULL";
        }
        else {
            $query[] = "alias='" . mysqli_real_escape_string($sql, $alias) . "'";
        }
    }

    if (!empty($query)) {
        $id = mysqli_real_escape_string($sql, $g->getID());

        $query_str = "UPDATE GeneAssociations SET " . implode(", ", $query) . " WHERE gene_id='$id'";
        mysqli_query($sql, $query_str);

        $g = new Gene($g->getID());
    }
}

function updateGeneByOrtho(Gene &$g, string $pathway, string $name, string $fullname, 
    string $family, string $subfamily, string $role) : void {
    global $sql;

    $id_sql = $g->getRealID();

    $query = [];
    $change = false;

    $pathway = trim($pathway); $name = trim($name); $fullname = trim($fullname); 
    $family = trim($family); $subfamily = trim($subfamily); $role = trim($role);

    $pathways = preg_split("/(\s+)?\|(\s+)?/", $pathway, -1, PREG_SPLIT_NO_EMPTY);
    if (count($pathways) === 1 && $pathways[0] === "") {
        $pathways = [];
    }

    if ($pathways !== $g->getPathways()) {
        // Mise à jour des pathways
        // et suppression des anciens
        mysqli_query($sql, "DELETE FROM Pathways WHERE id=$id_sql;");
        
        foreach ($pathways as $p) {
            $p = mysqli_real_escape_string($sql, $p);
            mysqli_query($sql, "INSERT INTO Pathways (id, pathway) VALUES ($id_sql, '$p');");
        }
        $change = true;
    }

    if ($name !== (string)$g->getName()) {
        $query[] = "gene_name='" . mysqli_real_escape_string($sql, $name) . "'";
    }
    if ($fullname !== (string)$g->getFullName()) {
        $query[] = "fullname='" . mysqli_real_escape_string($sql, $fullname) . "'";
    }
    if ($family !== (string)$g->getFamily()) {
        $query[] = "family='" . mysqli_real_escape_string($sql, $family) . "'";
    }
    if ($subfamily !== (string)$g->getSubFamily()) {
        $query[] = "subfamily='" . mysqli_real_escape_string($sql, $subfamily) . "'";
    }
    if ($role !== (string)$g->getFunction()) {
        $query[] = "func='" . mysqli_real_escape_string($sql, $role) . "'";
    }

    if (!empty($query)) {
        $query_str = "UPDATE Gene SET " . implode(", ", $query) . " WHERE id=$id_sql";
        mysqli_query($sql, $query_str);

        $g = new Gene($g->getID());
    }
    else if ($change) {
        $g = new Gene($g->getID());
    }
}

// Page de modification de gène : informations et liens

function modifyControl(array $args) : Controller {
    if (!isset($args[0])){
        throw new PageNotFoundException("You must specify a gene to modify.");
    }

    if (!isAdminLogged()) {
        throw new ForbiddenPageException("Log in to modify genes.");
    }

    global $sql;

    $inf = [];

    // Recherche de l'identifiant dans la base de données
    try {
        $gene = new Gene($args[0]);
    } catch (RuntimeException $e) {
        throw new PageNotFoundException("You can't modify a gene that does not exists.");
    }

    $gene_id = mysqli_real_escape_string($sql, $gene->getID());
    $deleted = false;

    if (isset($_POST['delete']) && $_POST['delete'] === "true") {
        // Vérification si des homologues restent
        $ho = mysqli_query($sql, "SELECT * FROM GeneAssociations WHERE gene_id!='$gene_id' AND id={$gene->getRealID()};");

        $q = mysqli_query($sql, "DELETE FROM GeneAssociations WHERE gene_id='$gene_id';");

        // Si il en reste, on les supprime
        if (mysqli_num_rows($ho) === 0) {
            mysqli_query($sql, "DELETE FROM Pathways WHERE id={$gene->getRealID()};");
            mysqli_query($sql, "DELETE FROM Gene WHERE id={$gene->getRealID()};");
        }

        if ($q) {
            $deleted = true;
        }
    }
    else if (isset($_POST['id_form'], $_POST['specie'], $_POST['adn_seq'], $_POST['pro_seq'], $_POST['addi'], $_POST['alias'])) {
        $linkable = isset($_POST['linkable']);

        updateGeneById($gene, $_POST['specie'], $_POST['adn_seq'], $_POST['pro_seq'], $_POST['addi'], $_POST['alias'], $linkable);
        $inf['updated_id'] = true;
    }
    else if (isset($_POST['gene_form'], $_POST['name'], $_POST['fname'], $_POST['family'], $_POST['subfamily'], $_POST['role'])) {
        if (!isset($_POST['pathway'])) {
            $_POST['pathway'] = [];
        }
        
        if (is_array($_POST['pathway'])) {
            // Pathway est un tableau, qui contient tous les pathways possibles
            $pa = implode('|', array_unique($_POST['pathway']));

            updateGeneByOrtho($gene, $pa, $_POST['name'], $_POST['fname'], $_POST['family'], $_POST['subfamily'], $_POST['role']);
            $inf['updated_ortho'] = true;
        }
    }

    $q = mysqli_query($sql, "SELECT specie, gene_id
        FROM GeneAssociations
        WHERE id='{$gene->getRealID()}'
        AND gene_id != '$gene_id'
    ");

    $array = [];
    while ($row = mysqli_fetch_assoc($q)){
        $specie_en_cours = $row['specie'];

        $id_en_cours = $row['gene_id'];
        $array[$specie_en_cours][] = $id_en_cours;
    }

    $res = mysqli_query($sql, "SELECT DISTINCT pathway FROM Pathways");
    $p = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $p[] = $row['pathway'];
    }
    

    $arr = [
        'gene' => $gene, 
        'orthologues' => $array, 
        'species' => getOrderedSpecies(), 
        'link' => getLinkForId($gene->getID(), $gene->getSpecie(), $gene->getAlias()),
        'pathways' => $p, 
        'infos' => $inf,
        'deleted' => $deleted
    ];

    return new Controller($arr, 'Modify ' . $gene->getID());
}

function modifyView(Controller $c) : void {
    $data = $c->getData(); 

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

        <?php if ($data['deleted']) {
            echo "<h4 class'red-text'>Gene has been deleted.</h4>";
        }
        else { ?>
            <!-- Formulaire -->

            <div class="section row" style="margin-top: 30px;">
                <!-- Messages de validation -->
                <?php 
                if (isset($data['infos']['updated_id'])) {
                    echo '<h5 class="green-text">Informations has been updated for '. $data['gene']->getID(). '.</h5>';
                }
                if (isset($data['infos']['updated_ortho'])) {
                    echo '<h5 class="green-text">Informations has been updated for '. $data['gene']->getID(). ' and his homologous.</h5>';
                }
                ?>

                <h4 class="light-text">Informations of <?= $data['gene']->getID() ?></h4>
                <form method="post">
                    <input type="hidden" name="id_form" value="1">
                    <div class="input-field col s12">
                        <select name="specie">
                            <?php 
                            foreach ($data['species'] as $s) {
                                $selected = ($data['gene']->getSpecie() === $s ? "selected" : "");
                                echo "<option value='$s' $selected>$s</option>";
                            }
                            ?>
                        </select>
                        <label>Specie</label>
                    </div>

                    <div class="input-field col s12">
                        <textarea class="materialize-textarea" name="adn_seq" placeholder="Leave empty for no sequence"
                            id="adn_seq"><?= $data['gene']->getSeqADN() ?></textarea>
                        <label for='adn_seq'>DNA sequence</label>
                    </div>

                    <div class="input-field col s12">
                        <textarea class="materialize-textarea" name="pro_seq" placeholder="Leave empty for no sequence"
                            id="pro_seq"><?= $data['gene']->getSeqProt() ?></textarea>
                        <label for='pro_seq'>Proteic sequence</label>
                    </div>
                    
                    <div class="input-field col s12">
                        <input type="text" name="addi" id="addi" autocomplete='off'
                            value="<?= htmlspecialchars($data['gene']->getAdditionalInfos(), ENT_QUOTES) ?>">
                        <label for='addi'>Miscellaneous informations</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="alias" id="alias" autocomplete='off' placeholder="Leave empty for no alias"
                            value="<?= htmlspecialchars($data['gene']->getAlias(), ENT_QUOTES) ?>">
                        <label for='alias'>Alias</label>
                    </div>

                    <p class="col s12">
                        <label>
                            <input type="checkbox" name="linkable" <?= $data['gene']->hasLink() ? 'checked' : '' ?>>
                            <span>Link to ArthropodaCyc is valid (<a target="_blank" href="<?= $data['link'] ?>">link</a>)</span>
                        </label>
                    </p>

                    <div class="clearb"></div>

                    <button type="submit" class="btn-flat green-text right">Modify</button>
                </form>

                <div class="clearb"></div>

                <div class="divider divider-margin"></div>

                <h4 class="light-text">Gene details for <?= $data['gene']->getID() ?> and his homologous</h4>
                <h6 class="light-text" style="margin-bottom: 18px;">
                    Modifications will apply to this gene ID and
                    his <?= count($data['orthologues'], COUNT_RECURSIVE) - count($data['orthologues']) ?> homologous
                </h6>

                <div id="base_path" class="hide">
                    <?php foreach ($data['pathways'] as $s) {
                        echo "<option value='$s'>$s</option>";
                    } ?>
                    <option value="">__Enter new pathway__</option>
                </div>

                <form method="post">
                    <input type="hidden" name="gene_form" value="1">

                    <div id="s-container">
                        <?php foreach ($data['gene']->getPathways() as $p) { ?>
                            <div class="s-wrapper">
                                <div class="input-field col s11">
                                    <select class="s-pathway" name="pathway[]" onchange="detectChange(this)">
                                    <?php
                                    // Initialise les pathways à générer
                                    foreach ($data['pathways'] as $s) {
                                        echo "<option value='$s' ".($p === $s ? "selected" : "").">$s</option>";
                                    }
                                    ?>
                                    <option value="">__Enter new pathway__</option>
                                    
                                    ?>
                                    </select>
                                    <label>Pathway</label>
                                </div>
                                <a href="#!" class="col s1" onclick="removePathway(this.parentElement)" style="margin-top: 20px;">
                                    <i class="material-icons red-text right-align">delete_forever</i>
                                </a>
                                <div class="clearb"></div>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="col s12">
                        <a href="#!" class="tiny-text" onclick="addPathway()">Add pathway</a>

                        <div class="divider divider-margin"></div>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="name" id="name" autocomplete='nope'
                            value="<?= htmlspecialchars($data['gene']->getName(), ENT_QUOTES) ?>">
                        <label for='name'>Name</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="fname" id="fname" autocomplete='nope'
                            value="<?= htmlspecialchars($data['gene']->getFullname(), ENT_QUOTES) ?>">
                        <label for='fname'>Fullname</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="family" id="family" autocomplete='off'
                            value="<?= htmlspecialchars($data['gene']->getFamily(), ENT_QUOTES) ?>">
                        <label for='family'>Family</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="subfamily" id="subfamily" autocomplete='nope'
                            value="<?= htmlspecialchars($data['gene']->getSubFamily(), ENT_QUOTES) ?>">
                        <label for='subfamily'>SubFamily</label>
                    </div>

                    <div class="input-field col s12">
                        <input type="text" name="role" id="role" autocomplete='off'
                            value="<?= htmlspecialchars($data['gene']->getFunction(), ENT_QUOTES) ?>">
                        <label for='role'>Role</label>
                    </div>

                    <div class="clearb"></div>

                    <button type="submit" class="btn-flat blue-text right">Modify</button>
                </form>

                <div class="clearb"></div>
            </div>

            <div class="divider divider-margin"></div>

            <a class="btn-flat btn-perso red-text right center fit-content" onclick="deleteGene()">
                <i class="material-icons left">delete_forever</i>Delete gene
            </a>

            <a class="btn-flat btn-perso orange-text left center fit-content" href="/gene/<?= $data['gene']->getID() ?>">
                <i class="material-icons left">arrow_back</i>Return to gene page
            </a>

            <div class="clearb" style="margin-bottom: 30px;"></div>

            <div class="row no-margin-bottom">
                <div id="modal_modif" class="modal"></div>
            </div>

            <script src="/js/modify.js"></script>
        <?php } ?>
    </div>  
    <?php
}
