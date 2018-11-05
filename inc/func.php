<?php

$sql = null;

function connectBD() : void {
    global $sql;
    $sql = mysqli_connect('localhost', MYSQL_USER, MYSQL_PASSWORD, MYSQL_BASE);
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
    }
    else 
        mysqli_query($sql, 'SET NAMES UTF8mb4'); // requete pour avoir les noms en UTF8mb4
}

function getSelectedUrl() : array {
    $page_name = 'home';
    $page_arguments = [];
    $ctrl = null;

    if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] !== '/') { 
        // Si la requête est définie et que on ne vise pas la racine (page d'accueil)

        // REQUEST_URI contient la query string GET, on l'enlève
        $request_without_query_string = explode('?', $_SERVER['REQUEST_URI'])[0];

        // Redirection par Apache, stockée dans cette variable
        // Possible par le .htaccess
        $page_arguments = explode('/', $request_without_query_string);
        $page_name = $page_arguments[1];

        // Récupère les arguments après la page
        // Équivaut à $page_arguments[2:] en Python
        $page_arguments = array_slice($page_arguments, 2);

        if (!array_key_exists($page_name, PAGES_REF) || preg_match("/^[0-9]+$/", $page_name)) {
            // Si la page demandée n'existe pas
            // ou si la page demandée est une page d'erreur
            $page_name = '404';
        }
    }

    return [$page_name, $page_arguments];
}

function getRoute(string $page_name, array $page_arguments) : Controller {
    // Get Controller object for asked page, Controller for home page if page undefined otherwise

    // Charge le fichier demandé
    require_once PAGES_REF[$page_name]['file'];
    // Récupère le nom de la fonction servant à charger la vue
    $view = PAGES_REF[$page_name]['view'];

    $error = null;

    // Appelle la fonction servant à initialiser le Controller
    // et le stocke dans $ctrl 
    // (on peut appeler des variables qui sont une chaîne de caractères nommant une fonction en PHP, cherchez pas)
    try {
        // Tente d'inclure l'original. Si il lance une exception, elle est attrapée en dessous et appelle
        // les pages adéquates
        $ctrl = (PAGES_REF[$page_name]['controller'])($page_arguments);
    } 
    catch (ForbiddenPageException $f) {
        $error = ['403', $f];
    } 
    catch (PageNotFoundException $n) {
        $error = ['404', $n];
    } 
    catch (NotImplementedException $n) {
        $error = ['501', $n];
    } 
    catch (Throwable $e) { // Toute autre exception
        $error = ['500', $e];
    }

    if ($error) {
        $code = $error[0]; $ex = $error[1];
        require_once PAGES_REF[$code]['file'];
        $ctrl = (PAGES_REF[$code]['controller'])($ex);
        $view = PAGES_REF[$code]['view']; 
    }

    // Enregistre la fonction de vue dans le contrôleur
    $ctrl->setViewFunction($view);

    // Retourne le contrôleur
    return $ctrl;
}

function tryLogIn() : void {
    global $sql;

    if (!isUserLogged() && isset($_COOKIE['token'])) {
        $token = mysqli_real_escape_string($sql, $_COOKIE['token']);

        $res = mysqli_query($sql, "SELECT * FROM Users WHERE token='$token';");

        if ($res && mysqli_num_rows($res)) {
            logUser(mysqli_fetch_assoc($res));
        }
    }
}

function logUser($mysql_user_object, bool $stay_logged = true) : void {
    global $sql;

    $_SESSION['user']['logged'] = true;
    $_SESSION['user']['id'] = (int)$mysql_user_object['id_user'];
    $_SESSION['user']['login'] = $mysql_user_object['username'];
    $_SESSION['user']['rights'] = (int)$mysql_user_object['rights'];

    if ($stay_logged) {
        // Enregistrement du cookie
        if ($mysql_user_object['token']) {
            $token = $mysql_user_object['token'];
        }
        else { 
            // Tirage d'un token
            do {
                $pass = true;
                $token = bin2hex(random_bytes(32));
                // Vérif si il existe dans la BDD
                $res = mysqli_query($sql, "SELECT * FROM Users WHERE token='$token';");
                if(mysqli_num_rows($res) > 0){
                    $pass = false;
                }
            } while (!$pass);

            mysqli_query($sql, "UPDATE Users SET token='$token' WHERE id_user={$_SESSION['user']['id']}");
        }

        setcookie('token', $token, time() + 1600*24*3600, '/', null, false, true);
    }
}

function unlogUser() : void {
    unset($_SESSION['user']);

    // Supprime le cookie
    setcookie('token', "", time() - 3600, '/', null, false, true);
}

function isUserLogged() : bool {
    return isset($_SESSION['user']['logged']) && $_SESSION['user']['logged'];
}

function getLinkForId(string $id, string $specie, ?string $alias = null) : string {
    if (!array_key_exists($specie, SPECIE_TO_NAME)) {
        return "";
    }

    if ($alias) {
        $id = $alias;
    }

    if ($specie === 'Soryzae') {
        if (!isUserLogged()) {
            return '';
        }

        return "http://bf2i200.insa-lyon.fr:3555/SITOR/NEW-IMAGE?type=GENE&object=" . $id;
    }
    
    if ($specie === 'Msexta') {
        if (!isUserLogged()) {
            return '';
        }
        
        $id = preg_replace("/Msex2\./", "Msex", $id);

        return "http://bf2i200.insa-lyon.fr:3555/MANSE/NEW-IMAGE?type=GENE&object=" . $id;
    }

    return "http://arthropodacyc.cycadsys.org/". SPECIE_TO_NAME[$specie] ."/NEW-IMAGE?type=GENE&object=" . $id;
}

function isProtectedSpecie(string $specie) : bool {
    return array_key_exists($specie, PROTECTED_SPECIES);
}

function getSpecies() : array {
    return array_keys(SPECIE_TO_NAME);
}

function getOrderedSpecies() : array {
    return ORDERED_SPECIES;
}

function getProtectedSpecies() : array {
    return array_keys(PROTECTED_SPECIES);
}

function saveMaintenanceStatus(bool $is_maintenance) : void {
    $p = SITE_PARAMETERS_ARRAY;
    $p['accessible'] = !$is_maintenance;

    file_put_contents(PARAMETERS_FILE, json_encode($p));
}

function saveSpecies(array $specie_to_name, array $ordered) : void {
    $p = SITE_PARAMETERS_ARRAY;
    $p['species'] = $specie_to_name;
    $p['species_ordered'] = $ordered;

    file_put_contents(PARAMETERS_FILE, json_encode($p));
}

function renewProtectedSpecies(array $species) : void {
    $p = SITE_PARAMETERS_ARRAY;
    $p['protected'] = $species;

    file_put_contents(PARAMETERS_FILE, json_encode($p));
}

function checkSaveLinkValidity(string $specie, string $gene_id, bool $is_alias = false) : bool {
    global $sql;

    if (array_key_exists($specie, SPECIE_TO_NAME) && !isProtectedSpecie($specie)) {
        $c = curl_init();
        $specie_code = SPECIE_TO_NAME[$specie];

        curl_setopt($c, CURLOPT_URL, "http://bf2i200.insa-lyon.fr/$specie_code/NEW-IMAGE?type=GENE&object={$gene_id}");

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_TIMEOUT, 6); // Attend 6 secondes maximum

        $return = curl_exec($c);

        $res = curl_getinfo($c);
        $gene_id = mysqli_real_escape_string($sql, $gene_id);

        $colomn = ($is_alias ? 'alias' : 'gene_id');

        if ($res['http_code'] === 404) {
            mysqli_query($sql, "UPDATE GeneAssociations SET linkable=0 WHERE $colomn='$gene_id';");
        }
        else if ($res['http_code'] >= 400 && $res['http_code'] <= 505 && $res['http_code'] !== 403) {
            // Code d'erreur client inconnu (erreur serveur)
            // On enregistre rien

            // 403: le lien existe mais est interdit (espèce protégé ?)
            // On l'enregistre comme valide (plus bas)
            $GLOBALS['logger']->write("Unable to check link. Error {$res['http_code']}.");
        }
        else if ($res['http_code'] === 0) {
            // Code d'erreur timeout
            $GLOBALS['logger']->write("Unable to check link : Timeout error.");
        }
        else {
            mysqli_query($sql, "UPDATE GeneAssociations SET linkable=1 WHERE $colomn='$gene_id';");
            return true;
        }
    }

    return false;
}

function buildDatabaseFromScratch() : void {
    global $sql;

    $sql_file = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/db/base.sql');

    $sql_file .= "\nINSERT INTO Users (username, passw, rights) 
    VALUES ('admin', '" 
    . password_hash('admin', PASSWORD_BCRYPT) . 
    "', 2);";

    mysqli_multi_query($sql, $sql_file);
}

connectBD();
