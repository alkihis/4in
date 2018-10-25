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

    $mode = 'adn_base';
    $blast_type = 'blastn';
    $mode_short = 'adn';

    if (isset($args[0]) && $args[0] === 'prot') {
        $mode = 'pro_base';
        $blast_type = 'blastp';
        $mode_short = 'pro';
    }

    if (isUserLogged() || !LIMIT_GENOMES) { 
        // Si l'utilisateur a les droits, ou si les génomes ne sont pas limités
        // il utilise la BDD complète
        $mode .= '_full';
    }

    if (isset($_POST['query']) && is_string($_POST['query'])) {
        $_POST['query'] = trim($_POST['query']);

        // $valid contiendra true si la chaîne est valide,
        // -1 si aucune ligne ne contient de séquence,
        // position_de_ligne de la ligne invalide sinon
        $valid = isValidFasta($_POST['query'], $mode_short);

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

    chdir($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin');

    $html = `./$blast_type -query "$temp_file" -db base/$mode -html 2>&1`;

    chdir($_SERVER['DOCUMENT_ROOT']);

    `rm -f $temp_file`;

    // Traitement de l'HTML généré
    $html = preg_replace("/a> *(.+)\nlength=/iu", "a> <a href='/gene/$1' target='_blank'>$1</a>\nlength=", $html);

    $mat = [];
    preg_match("/(<pre>.+<\/pre>)/is", $html, $mat);

    if (isset($mat[1])) {
        $html = $mat[1];
    }
    else {
        // TODO : LOG ERROR
        throw new RuntimeException('BLAST is not available');
    }

    $re['html'] = $html;

    return new Controller($re, 'BLAST');
}

function blastView(Controller $c) : void {
    $data = $c->getData();

    ?>
    <div class='container'>
        <?php 
        if (isset($data['error']['query'])) {
            echo "<h4 class='red-text'>Your file is not formated correctly.</h4><h6>Using testing dataset.</h6>
            <div class='divider divider-margin'></div>";
            echo '<h6 class="red-text">';

            if ($data['error']['query'] === -1) {
                echo "Empty sequence or file.";
            }
            else {
                $data['error']['query']++; // Lines are 0+ formatted, switch to 1+ format
                echo "Line {$data['error']['query']} is invalid.";
            }

            echo '</h6>';
        }
        ?>
        <?= $data['html'] ?>
    </div>
    <?php
}
