<?php
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
            $is_ok = preg_match("/^[ATGC\*]+$/i", $line);
        else
            $is_ok = preg_match("/^[GALMFWKQESPVICYHRNDT\*]+$/i", $line);

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

function blastProgram() : string {
    if (isset($_POST['program']) && is_string($_POST['program'])) {
        switch ($_POST['program']) {
            case 'p':
                return 'blastp';
            case 'meg':
                return 'blastn -task megablast';
            case 'x':
                return 'blastx';
            case 'tx':
                return 'tblastx';
            case 'tn':
                return 'tblastn';
            default:
                return 'blastn';
        }
    }
    else {
        return 'blastn';
    }
}

function chooseBDD(string $program) : string {
    if (isUserLogged() || !LIMIT_GENOMES) {
        $suf = '_full';
    }
    else {
        $suf = '';
    }

    if ($program === 'blastp' || $program === 'blastx') {
        return '-db base/pro_base' . $suf;
    }
    else {
        return '-db base/adn_base' . $suf;
    }
}

function constructParameters(string $program) : string {
    $p = "";

    if (isset($_POST['num_descriptions']) && is_string($_POST['num_descriptions'])) {
        $max = (int)$_POST['num_descriptions'];

        if ($max >= 50 && $max <= 5000) {
            $p .= " -num_descriptions $max ";
        }
    }
    if (isset($_POST['num_alignments']) && is_string($_POST['num_alignments'])) {
        $max = (int)$_POST['num_alignments'];

        if ($max >= 50 && $max <= 2500) {
            $p .= " -num_alignments $max ";
        }
    }

    if (isset($_POST['evalue']) && is_string($_POST['evalue'])) {
        $max = (int)$_POST['evalue'];

        if ($max >= 0.0001 && $max <= 100) {
            $p .= " -evalue $max ";
        }
    }

    if (isset($_POST['word_size']) && is_string($_POST['word_size'])) {
        $max = (int)$_POST['word_size'];

        if ($max >= 2 && $max <= 48) {
            $p .= " -word_size $max ";
        }
    }

    if (!preg_match("/^blastn/", $program)) { // Si jamais on est pas sur un blastn
        if (isset($_POST['matrix']) && is_string($_POST['matrix'])) {
            $mat = $_POST['matrix'];

            if (in_array($mat, ['PAM30', 'PAM70', 'PAM250', 'BLOSUM45', 'BLOSUM62', 'BLOSUM80', 'BLOSUM90'])) {
                $p .= " -matrix $mat ";
            }
        }

        if ($program !== 'tblastx') {
            if (isset($_POST['comp_based_stats']) && is_string($_POST['comp_based_stats'])) {
                $mat = (int)$_POST['comp_based_stats'];
    
                if ($mat >= 0 && $mat <= 3) {
                    $p .= " -comp_based_stats $mat ";
                }
            }

            if (isset($_POST['gapvalues']) && is_string($_POST['gapvalues'])) {
                $val = $_POST['gapvalues'];

                $val = explode('/', $val);

                if (isset($val[1])) { // Si on a bien deux valeurs
                    $open = (int)$val[0];
                    $ext = (int)$val[1];

                    $p .= " -gapopen $open -gapextend $ext ";
                }
            }
        }
    }

    if (isset($_POST['filter_low_complexity']) && is_string($_POST['filter_low_complexity'])) {
        if (preg_match("/^blastn/", $program)) {
            $p .= " -dust \"20 64 1\" ";
        }
        else {
            $p .= " -seg yes ";
        }
    }
    else {
        if (preg_match("/^blastn/", $program)) {
            $p .= " -dust no ";
        }
        else {
            $p .= " -seg no ";
        }
    }

    if (isset($_POST['soft_masking']) && is_string($_POST['soft_masking'])) {
        $p .= " -soft_masking true ";
    }
    else {
        $p .= " -soft_masking false ";
    }

    if (isset($_POST['lcase_masking']) && is_string($_POST['lcase_masking'])) {
        $p .= " -lcase_masking ";
    }

    if ($program === 'blastx' || $program === 'tblastx') {
        if (isset($_POST['query_genetic_code']) && is_string($_POST['query_genetic_code'])) {
            $mat = (int)$_POST['query_genetic_code'];

            if (($mat > 0 && $mat <= 6) || ($mat > 8 && $mat <= 15)) {
                $p .= " -query_gencode $mat ";
            }
        }
    }

    if ($program === 'tblastx' || $program === 'tblastn') {
        if (isset($_POST['db_gen_code']) && is_string($_POST['db_gen_code'])) {
            $mat = (int)$_POST['db_gen_code'];

            if (($mat > 0 && $mat <= 6) || ($mat > 8 && $mat <= 15)) {
                $p .= " -db_gencode $mat ";
            }
        }
    }

    if (isset($_POST['outfmt']) && is_string($_POST['outfmt'])) {
        $mat = (int)$_POST['outfmt'];

        if ($mat > 0 && $mat <= 4) {
            $p .= " -outfmt $mat ";
        }
    }

    return $p;
}

function blastControl(array $args) : Controller {
    $re = [];

    // BASE DIRECTORY IS $_SERVER[’DOCUMENT_ROOT']
    $query_str = "";
    $query_file = null;

    $query_shell = $program = blastProgram();
    $query_shell = './' . $query_shell . ' ' . chooseBDD($program);

    $query_shell .= constructParameters($program);

    if (isset($_FILES['fasta_file']) && $_FILES['fasta_file']['size'] && !$_FILES['fasta_file']['error']) {
        $query_file = $_FILES['fasta_file']['tmp_name'];
    }
    else if (isset($_POST['query']) && is_string($_POST['query'])) {
        $_POST['query'] = trim($_POST['query']);

        if ($_POST['query']) {
            $query_str = $_POST['query'];
        }
    }

    if ($query_file || $query_str) {
        if ($query_file) {
            $temp_file = $query_file;
        }
        else {
            $temp_file = `mktemp`;
            $temp_file = trim($temp_file);
        
            file_put_contents($temp_file, $query_str);
            `chmod a+a $temp_file`;
        }
    
        chdir($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin');

        $query_shell .= " -query \"$temp_file\" -html 2>&1";
    
        // echo $query_shell;
        $html = `$query_shell`;
    
        chdir($_SERVER['DOCUMENT_ROOT']);
    
        if ($query_str) {
            `rm -f $temp_file`;
        }
    
        // Traitement de l'HTML généré
        $html = preg_replace("/a> *(.+)\nlength=/iu", "a> <a href='/gene/$1' target='_blank'>$1</a>\nlength=", $html);
    
        $mat = [];
        preg_match("/(<pre>.+<\/pre>)/is", $html, $mat);
    
        if (!DEBUG_MODE) {
            if (isset($mat[1])) {
                $html = $mat[1];
            }
            else {
                throw new RuntimeException('BLAST is not available');
            }
        }
    
        $re['html'] = $html;
    
    }
    else {
        $re['html'] = '';
        $re['error']['empty'] = true;
    }

    return new Controller($re, 'BLAST');
}

function blastView(Controller $c) : void {
    $data = $c->getData();

    ?>
    <div class='container'>
        <?php 
        if (isset($data['error']['query'])) {
            echo "<h4 class='red-text'>Your file is not formated correctly.</h4>
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
        else if (isset($data['error']['empty'])) {
            echo "<h4 class='red-text'>Query is empty.</h4>";
        }
        ?>
        <?= $data['html'] ?>
    </div>
    <?php
}
