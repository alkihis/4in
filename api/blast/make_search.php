<?php

global $sql;

// Construit la requête BLAST
// et renvoie l'HTML sous forme de JSON

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
                return 'blastn -task blastn';
        }
    }
    else {
        return 'blastn -task blastn';
    }
}

function chooseBDD(string $program) : string {
    if (!LIMIT_GENOMES || isUserLogged()) {
        $suf = '_full';
    }
    else {
        $suf = '';
    }

    if ($program === 'blastp' || $program === 'blastx') {
        return '-db base/pro_base' . $suf;
    }

    return '-db base/adn_base' . $suf;
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

    if (0 !== strpos($program, "blastn")) { // Si jamais on est pas sur un blastn
        if (isset($_POST['matrix']) && is_string($_POST['matrix'])) {
            $mat = $_POST['matrix'];

            if (in_array($mat, ['PAM30', 'PAM70', 'PAM250', 'BLOSUM45', 'BLOSUM62', 'BLOSUM80', 'BLOSUM90'])) {
                $p .= " -matrix $mat ";
            }
        }

        if ($program !== 'tblastx' && isset($_POST['comp_based_stats']) && is_string($_POST['comp_based_stats'])) {
            $mat = (int)$_POST['comp_based_stats'];

            if ($mat >= 0 && $mat <= 3) {
                $p .= " -comp_based_stats $mat ";
            }
        }
    }

    if ($program === 'blastn -task blastn') {
        // Si on est sur un blastn
        if (isset($_POST['rewardvalues']) && is_string($_POST['rewardvalues'])) {
            $val = $_POST['rewardvalues'];

            $val = explode('/', $val);

            if (isset($val[1])) { // Si on a bien deux valeurs
                $mat = (int)$val[0];
                $mismat = (int)$val[1];

                $p .= " -reward $mat -penalty $mismat ";
            }
        }
    }

    if ($program !== 'blastn -task megablast' && $program !== 'tblastx') { 
        // Si on est pas sur megablast ou tblastx

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

    if (isset($_POST['filter_low_complexity']) && is_string($_POST['filter_low_complexity'])) {
        if (0 === strpos($program, "blastn")) {
            $p .= " -dust \"20 64 1\" ";
        }
        else {
            $p .= " -seg yes ";
        }
    }
    else if (strpos($program, "blastn") === 0) {
        $p .= " -dust no ";
    }
    else {
        $p .= " -seg no ";
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

function blastControl(array &$stats) : int {
    $re = [];

    chdir($_SERVER['DOCUMENT_ROOT']);
    // BASE DIRECTORY IS $_SERVER[’DOCUMENT_ROOT']
    $query_str = "";
    $query_file = null;

    // Sélection du programme
    $query_shell = $program = blastProgram();
    // Sélection de la base de données BLAST à utiliser
    $query_shell = './' . $query_shell . ' ' . chooseBDD($program);

    // Construction des paramètres en fonction du POST et du programme
    $query_shell .= constructParameters($program);

    // Fermeture de la session
    // pour ne pas lock la session
    // pour d'autres pages
    session_write_close();
        
    // Définit la taille maximum du fichier d'entrée (en Ko)
    $limit_of_fasta_file = ($program === 'blastp' || $program === 'tblastn' ? 100 : 300);

    if (isset($_FILES['fasta_file']) && $_FILES['fasta_file']['size'] && !$_FILES['fasta_file']['error']) {
        $query_file = $_FILES['fasta_file']['tmp_name'];

        if ($_FILES['fasta_file']['size'] > $limit_of_fasta_file * 1024) { 
            // Si le poids du fichier est supérieur à la limite définie (en Ko)
            return 6;
        }
    }
    else if (isset($_POST['query']) && is_string($_POST['query'])) {
        $_POST['query'] = trim($_POST['query']);

        if ($_POST['query']) {
            if (strlen($_POST['query']) > $limit_of_fasta_file * 1024) { 
                // Si la taille de la chaîne (string en PHP: byte, donc comparable à un poids en octets) 
                // est supérieur à la limite définie (en Ko)
                return 6;
            }

            $query_str = $_POST['query'];
        }
    }

    // Si on a au moins une sorce de query
    if ($query_file || $query_str) {
        // Le fichier est préféré au texte
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

        $query_shell .= " -query \"$temp_file\" -html -num_threads 4 2>&1";
    
        $st = microtime(true);
        // echo $query_shell;
        $html = `$query_shell`;
        $stats['time_request'] = microtime(true) - $st;
    
        chdir($_SERVER['DOCUMENT_ROOT']);
    
        if ($query_str) {
            `rm -f $temp_file`;
        }

        $stats['len'] = strlen($html);

        if (strlen($html) > 5000000) { // Résultat très très long (5 millions de caractères)
            if (strlen($html) > 9000000) {
                return 4;
            }

            // La mise en buffer est faite avec une référence sur $html,
            // pour éviter la recopie
            $stats['buffer'] = &$html;
            return 5;
        }
    
        $st = microtime(true);
        // Traitement de l'HTML généré
        $html = preg_replace("/a> *(.+)\nlength=/iu", "a> <a href='/gene/$1' target='_blank'>$1</a>\nlength=", $html);

        $stats['time_link'] = microtime(true) - $st;

        $st = microtime(true);
        // Création des liens vers queries
        $queries = [];
        
        $html = preg_replace_callback("/<b>Query=<\/b> (.+)\b/iu", function($matches) use (&$queries) {
            $match = htmlspecialchars($matches[1], ENT_QUOTES);
            $queries[] = $match;

            return "<a href='#top_blast'><i class='material-icons left'>arrow_drop_up</i>Jump to top</a>" . 
                "<div class='clearb'></div>\n<b>Query=</b> <span id='{$match}'>{$match}</span>";
        }, $html);
        $stats['time_queries'] = microtime(true) - $st;
    
        $st = microtime(true);
        $mat = [];
        preg_match("/(<pre>.+<\/pre>)/is", $html, $mat);
    
        // Construit le texte "Queries" en haut de la recherche pour accéder rapidement aux
        // différentes recherches
        if (!empty($queries)) {
            $query_text = "<h6 id='top_blast' class='light-text'>Queries :</h6>";
            $first = true;
            foreach ($queries as $quer) {
                if ($first) $first = false;
                else $query_text .= ", ";
                $query_text .= "<a href='#$quer'>$quer</a>";
            }
    
            echo $query_text;
        }
        $stats['time_generation'] = microtime(true) - $st;

        if (isset($mat[1])) {
            echo $mat[1];
        } 
        else {
            Logger::write("BLAST error : $html");

            if (!DEBUG_MODE)
                return 1; // BLAST NOT AVAILABLE
            else
                echo $html;
        }
    }
    else {
        return 2; // EMPTY QUERY
    }

    return 0;
}

if (isset($_SESSION['before_next_blast']) && $_SESSION['before_next_blast'] > time()) {
    $errors = 3; // RETRY LATER
    $html = '';
    $stats = ['retry_after' => $_SESSION['before_next_blast'] - time()];
}
else {
    // Commence le BLAST
    // Temps limite de 120 minutes (deux heures, 60*60*2)
    set_time_limit(120*60);

    ob_start();

    // Par défaut, empêche de relancer une requête pendant que l'ancienne tourne
    // pendant au moins 120 secondes
    // Sauf si l'utilisateur est connecté
    if (!isAdminLogged()) {
        $_SESSION['before_next_blast'] = time() + 120;
    }
    
    $stats = [];
    // blastControl() ferme la session pendant le BLAST ! (pour éviter de lock)
    $errors = blastControl($stats);

    // Réouvre la session (AVANT le débuffer, sinon header HTTP envoyés!)
    // et empêche de relancer une requête avant 20 secondes pour les utilisateurs anonymes
    // si jamais la requête a réussi
    session_start();
    if ($errors === 0 && !isAdminLogged()) {
        $_SESSION['before_next_blast'] = time() + 20;
    }
    else {
        $_SESSION['before_next_blast'] = 0;
    }

    $html = ob_get_clean();
}

if (isset($_POST['make_html']) && $_POST['make_html'] === "true") {
    if (isset($stats['buffer'])) {
        echo $stats['buffer'];
    }
    else {
        echo $html;
    }
}
else {
    header('Content-Type: application/json');

    echo json_encode(['html' => $html, 'error' => $errors, 'stats' => $stats]);
}
