<?php

function blastControl(array $args) : Controller {
    /**
     * isValidFasta
     * Returns TRUE if FASTA is OK,
     * -1 if no valid line
     * 0+ (line position) if line 0+ failed
     *
     * @param string $str FASTA string
     * @param string $mode adn or pro
     * @return int|boolean
     */
    function isValidFasta(string $str, string $mode = 'adn') {
        $lines = explode("\n", $str);
        $real_lines = 0;

        if ($mode === 'adn')
            $mode = 0;
        else
            $mode = 1;

        foreach ($lines as $key => $line) {
            if (strpos($line, '>') === 0) {
                continue;
            }

            // REGEX
            if ($mode === 0)
                $is_ok = preg_match("/^[ATGC]+$/i", $line);
            else
                $is_ok = preg_match("/^[GALMFWKQESPVICYHRNDT]+$/i", $line);

            if (!$is_ok)
                return $key;

            $real_lines++;
        }

        if ($real_lines === 0) {
            return -1;
        }
        else {
            return true;
        }
    }

    $re = [];

    // BASE DIRECTORY IS $_SERVER[’DOCUMENT_ROOT']
    $query_file = ">test\nEDHKNSDWLIVVIMTHGDDDVLHAKDGQFNVDRLWENFIGDSCPSLLGKPKLFFIQACR";

    if (DEBUG_MODE) {
        if (isset($_REQUEST['query']))
            $_POST['query'] = $_REQUEST['query'];
    }

    if (isset($_POST['query']) && is_string($_POST['query'])) {
        $_POST['query'] = trim($_POST['query']);

        // $valid contiendra true si la chaîne est valide,
        // -1 si aucune ligne ne contient de séquence,
        // position_de_ligne de la ligne invalide sinon
        $valid = isValidFasta($_POST['query'], 'pro');

        if ($valid === true) {
            $query_file = $_POST['query'];
        }
        else {
            $re['error']['query'] = $valid;
        }
    }

    $temp_file = `mktemp`;
    $temp_file = trim($temp_file);

    `echo "{$query_file}" > $temp_file`;
    `chmod a+a $temp_file`;
    # echo `ls -l $temp_file`;

    chdir($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin');

    $html = `./blastp -query "$temp_file" -db testdb -html 2>&1`;

    chdir($_SERVER['DOCUMENT_ROOT']);

    `rm -f $temp_file`;

    // Traitement de l'HTML généré
    $html = preg_replace("/a> *(.+)\nlength=/iu", "a> <a href='/gene/$1' target='_blank'>$1</a>\nlength=", $html);
    $re['html'] = $html;

    return new Controller($re, 'BLAST');
}

function blastView(Controller $c) : void {
    $data = $c->getData();

    // global $sql;

    // $q = mysqli_query($sql, "SELECT DISTINCT gene_id FROM GeneAssociations;");

    // echo '<pre>';

    // while ($row = mysqli_fetch_assoc($q)) {
    //     echo $row['gene_id'] . "\n";
    // }

    // echo "</pre>";
    // return;

    ?>
    <div class='container'>
        <?php 
        if (isset($data['error']['query'])) {
            echo "<h4 class='red-text'>Votre fichier est malformaté.</h4><h6>Utilisation du jeu de test.</h6>
            <div class='divider divider-margin'></div>";
            echo '<h6 class="red-text">';

            if ($data['error']['query'] === -1) {
                echo "Aucune ligne ne contient de séquence.";
            }
            else {
                $data['error']['query']++; // Lines are 0+ formatted, switch to 1+ format
                echo "Ligne {$data['error']['query']} invalide.";
            }

            echo '</h6>';
        }
        ?>
        <?= $data['html'] ?>
    </div>
    <?php
}
