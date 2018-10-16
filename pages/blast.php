<?php

function blastControl(array $args) : Controller {
    function isValidFasta(string $str, string $mode = 'adn') : bool {
        $lines = explode("\n", $str);
        $real_lines = 0;

        if ($mode === 'adn')
            $valid_nuc = ['A', 'T', 'G', 'C'];
        else
            $valid_nuc = ['G', 'A', 'L', 'M', 'F', 'W', 'K', 
                'Q', 'E', 'S', 'P', 'V', 'I', 'C', 
                'Y', 'H', 'R', 'N', 'D', 'T'
            ];

        foreach ($lines as $key => $line) {
            if (strpos($line, '>') === 0) {
                continue;
            }
            
            $i = 0;
            while ($i < strlen($line)) {
                if (! in_array(strtoupper($line[$i]), $valid_nuc)) {
                    return false;
                }
                $i++;
            }
            $real_lines++;
        }

        return $real_lines > 0;
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

        if (isValidFasta($_POST['query'], 'pro')) {
            $query_file = $_POST['query'];
        }
        else {
            $re['error']['query'] = true;
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
        <?= (isset($data['error']['query']) ? 
            "<h4 class='red-text'>Votre fichier est malformaté.</h4><h6>Utilisation du jeu de test.</h6>
            <div class='divider'></div>" : '') ?>
        <?= $data['html'] ?>
    </div>
    <?php
}
