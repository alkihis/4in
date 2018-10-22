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

function getRoute() : Controller {
    // Get Controller object for asked page, Controller for home page if page undefined otherwise
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

        if (!array_key_exists($page_name, PAGES_REF)) {
            // Si la page demandée n'existe pas
            $page_name = '404';
        }
    }

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

function initTableUser() : void {
    global $sql;

    mysqli_multi_query($sql, "
    CREATE TABLE Users (
        id_user INT NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        passw VARCHAR(255) NOT NULL,
        rights INT NOT NULL,
        PRIMARY KEY (id_user)
    );
    INSERT INTO Users (username, passw, rights) 
    VALUES ('admin', '". password_hash('admin', PASSWORD_BCRYPT) . "', 2);");
}

function logUser($mysql_user_object) : void {
    global $sql;

    $_SESSION['user']['logged'] = true;
    $_SESSION['user']['id'] = (int)$mysql_user_object['id_user'];
    $_SESSION['user']['login'] = $mysql_user_object['username'];
    $_SESSION['user']['rights'] = (int)$mysql_user_object['rights'];
}

function unlogUser() : void {
    unset($_SESSION['user']);
}

function isUserLogged() : bool {
    return isset($_SESSION['user']['logged']) && $_SESSION['user']['logged'];
}

function getLinkForId(string $id, string $specie) : string {
    if (!array_key_exists($specie, SPECIE_TO_NAME)) {
        return "";
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

function getProtectedSpecies() : array {
    return array_keys(PROTECTED_SPECIES);
}

function checkSaveLinkValidity(string $specie, string $gene_id) : bool {
    global $sql;

    if (array_key_exists($specie, SPECIE_TO_NAME) && !isProtectedSpecie($specie)) {
        $c = curl_init();
        $specie_code = SPECIE_TO_NAME[$specie];

        curl_setopt($c, CURLOPT_URL, "http://bf2i200.insa-lyon.fr/$specie_code/NEW-IMAGE?type=GENE&object={$gene_id}");

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($c);

        $res = curl_getinfo($c);
        $gene_id = mysqli_real_escape_string($sql, $gene_id);

        if ($res['http_code'] === 404) {
            mysqli_query($sql, "UPDATE GeneAssociations SET linkable=0 WHERE gene_id='$gene_id';");
        }
        else {
            mysqli_query($sql, "UPDATE GeneAssociations SET linkable=1 WHERE gene_id='$gene_id';");
            return true;
        }
    }

    return false;
}

connectBD();
