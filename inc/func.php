<?php

$sql = null;

function connectBD() : void {
    global $sql;
    $sql = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_BASE);
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
    }
    else 
        mysqli_query($sql, 'SET NAMES UTF8mb4'); // requete pour avoir les noms en UTF8mb4
}

/**
 * Return user's selected URL and page arguments
 * return [string $user_url, array<string> $user_arguments]
 *
 * @return array
 */
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

/**
 * Get Controller object from given route
 *
 * @param string $page_name
 * @param array<string> $page_arguments
 * @return Controller
 */
function getRoute(string $page_name, array $page_arguments) : Controller {
    // Get Controller object for asked page, Controller for home page if page undefined otherwise

    // Charge le fichier demandé
    require_once PAGES_REF[$page_name]['file'];
    // Récupère le nom de la fonction servant à charger la vue
    $view = PAGES_REF[$page_name]['view'];

    $error = null;

    // Appelle la fonction servant à initialiser le Controller
    // et le stocke dans $ctrl 
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
        // Si il y a une erreur, on charge la page d'erreur adaptée
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

/**
 * Tente de connecter l'utilisateur si le cookie de token est fourni (et qu'il n'est pas connecté)
 *
 * @return void
 */
function tryLogIn() : void {
    global $sql;

    if (!isUserLogged() && isset($_COOKIE['token'])) {
        // échappe le token
        $token = mysqli_real_escape_string($sql, $_COOKIE['token']);

        // demande à la base si un utilisateur existe avec ce token
        $res = mysqli_query($sql, "SELECT * FROM Users WHERE token='$token';");

        if ($res && mysqli_num_rows($res)) {
            logUser(mysqli_fetch_assoc($res));
        }
    }
}

/**
 * Connecte un utilisateur via le résultat de 
 * $sql->query("SELECT * FROM Users WHERE xxx=xxx")->fetch_assoc()
 *
 * @param array $mysql_user_object
 * @param boolean $stay_logged
 * @return void
 */
function logUser(array $mysql_user_object, bool $stay_logged = true) : void {
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

        // Enregistrement du cookie de token si demandé
        setcookie('token', $token, time() + 1600*24*3600, '/', null, false, true);
    }
}

/**
 * Déconnecte l'utilisateur
 *
 * @return void
 */
function unlogUser() : void {
    unset($_SESSION['user']);

    // Supprime le cookie
    setcookie('token', "", time() - 3600, '/', null, false, true);
}

/**
 * Teste si l'utilisateur est connecté
 * > Il n'y a pas de gestion de niveau, un utilisateur connecté EST admin. <
 *
 * @return boolean
 */
function isUserLogged() : bool {
    return isset($_SESSION['user']['logged']) && $_SESSION['user']['logged'];
}

/**
 * Récupère le lien associé à l'ID ou l'alias lié.
 * Même si l'alias est passé, l'ID DOIT être passé (pas null).
 * 
 * Vérifie si l'utilisateur est autorisé à voir le lien de l'espèce en question.
 * Renvoie un lien au format URI si existe et autorisé,
 * renvoie une chaîne vide si lien inexistant (espèce non définie) ou non autorisée.
 *
 * @param string $id
 * @param string $specie
 * @param string|null $alias
 * @return string
 */
function getLinkForId(string $id, string $specie, ?string $alias = null) : string {
    // Si l'espèce n'existe pas, on renvoie une chaîne vide
    if (!specieExists($specie)) {
        return "";
    }

    // Si l'alias est définie, on l'utilise à la place de l'ID
    if ($alias) {
        $id = $alias;
    }

    // On obtient l'acronyme : nécessaire que l'espèce soit définie donc.
    $acronym = getAcronymForSpecie($specie);

    // Si c'est une espèce protégée, on renvoie rien si l'utilisateur n'a pas les droits
    if (isProtectedSpecie($specie)) {
        if ($specie === 'Msexta') { // Cette espèce doit être gérée spécialement
            $id = preg_replace("/Msex2\./", "Msex", $id);
        }

        if (!isUserLogged()) {
            return '';
        }

        return sprintf(LINK_PROTECTED_SPECIE, $acronym, $id);
    }

    return sprintf(LINK_GENERAL, $acronym, $id);
}

/**
 * Vrai si l'espèce $specie est protégée.
 *
 * @param string $specie
 * @return boolean
 */
function isProtectedSpecie(string $specie) : bool {
    return array_key_exists($specie, PROTECTED_SPECIES);
}

/**
 * Renvoie un tableau des espèces existantes
 * Ce tableau est THÉORIQUE, il n'est PAS le reflet de la base SQL configurée.
 *
 * @return array
 */
function getSpecies() : array {
    return array_keys(SPECIE_TO_NAME);
}

/**
 * Vrai si l'espèce $specie existe
 *
 * @param string $specie
 * @return boolean
 */
function specieExists(string $specie) : bool {
    return array_key_exists($specie, SPECIE_TO_NAME);
}

/**
 * Renvoie l'acronyme de l'espèce si il existe, null si l'espèce n'en a pas / n'existe pas
 *
 * @param string $specie
 * @return string|null
 */
function getAcronymForSpecie(string $specie) : ?string {
    return SPECIE_TO_NAME[$specie] ?? null;
}

/**
 * Renvoie le tableau d'espèces existantes dans l'ordre d'insertion voulue dans le fichier .tsv
 *
 * @return array
 */
function getOrderedSpecies() : array {
    return ORDERED_SPECIES;
}

/**
 * Renvoie un tableau contenant toutes les espèces protégées
 *
 * @return array
 */
function getProtectedSpecies() : array {
    return array_keys(PROTECTED_SPECIES);
}

/**
 * Modifie le status "maintenance" du site web
 *
 * @param boolean $is_on_maintenance
 * @return void
 */
function saveMaintenanceStatus(bool $is_on_maintenance) : void {
    $p = SITE_PARAMETERS_ARRAY;
    $p['accessible'] = !$is_on_maintenance;

    file_put_contents(PARAMETERS_FILE, json_encode($p));
}

/**
 * Sauvegarde les nouvelles espèces
 * $specie_to_name DOIT être un tableau clé=>valeur avec clé = nom de l'espèce, valeur = acronyme de l'espèce
 * $ordered représente un tableau indicé avec les espèces dans le bon ordre de lecture du fichier .tsv
 *
 * @param array $specie_to_name
 * @param array $ordered
 * @return void
 */
function saveSpecies(array $specie_to_name, array $ordered) : void {
    $p = SITE_PARAMETERS_ARRAY;
    $p['species'] = $specie_to_name;
    $p['species_ordered'] = $ordered;

    file_put_contents(PARAMETERS_FILE, json_encode($p));
}

/**
 * Sauvegarde les espèces protégées
 * Le tableau DOIT être un tableau indicé
 *
 * @param array $species
 * @return void
 */
function renewProtectedSpecies(array $species) : void {
    $p = SITE_PARAMETERS_ARRAY;
    $p['protected'] = $species;

    file_put_contents(PARAMETERS_FILE, json_encode($p));
}

/**
 * Vérifie si un lien est valide (non-mort)
 * $is_alias DOIT être true si jamais $gene_id contient l'alias et pas le gene_id.
 * Attention : Vous DEVEZ passer l'alias si l'alias existe 
 * (le paramètre $gene_id doit ressembler à *$alias ?? $gene_id*)
 *
 * Retourne vrai si le lien est valide.
 * 
 * @param string $specie
 * @param string $gene_id
 * @param boolean $is_alias
 * @return boolean
 */
function checkSaveLinkValidity(string $specie, string $gene_id, bool $is_alias = false) : bool {
    global $sql;

    if (array_key_exists($specie, SPECIE_TO_NAME) && !isProtectedSpecie($specie)) {
        $c = curl_init();
        $specie_code = SPECIE_TO_NAME[$specie];

        curl_setopt($c, CURLOPT_URL, sprintf(LINK_CHECKER, $specie_code, $gene_id));

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
            Logger::write("Unable to check link. Error {$res['http_code']}.");
        }
        else if ($res['http_code'] === 0) {
            // Code d'erreur timeout
            Logger::write("Unable to check link : Timeout error.");
            return true;
        }
        else {
            mysqli_query($sql, "UPDATE GeneAssociations SET linkable=1 WHERE $colomn='$gene_id';");
            return true;
        }
    }

    return false;
}

/**
 * Reconstruit la base de données SQL depuis 0.
 * Le mot de passe administrateur est demandé.
 * Cette fonction n'importe PAS de TSV !
 *
 * @param string $user_pw
 * @return void
 */
function buildDatabaseFromScratch(string $user_pw = 'admin') : void {
    global $sql;

    $user_pw = trim($user_pw);

    if ($user_pw === "") {
        throw new RuntimeException("Password cannot be empty");
    }

    $sql_file = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/db/base.sql');

    $sql_file .= "\nINSERT INTO Users (username, passw, rights) 
    VALUES ('admin', '" 
    . password_hash($user_pw, PASSWORD_BCRYPT) . 
    "', 2);";

    mysqli_multi_query($sql, $sql_file);
}

/**
 * Construit un nouvel orthologue/homologue.
 * 
 * Retourne 0 si tout s'est bien passé, un code d'erreur positif si échec.
 *
 * @param integer $original_sql_id
 * @param string $specie
 * @param string $id
 * @param string $addi sont les informations additionnelles du gène
 * @param string $alias
 * @param string $sp est la séquence protéique (peut être vide)
 * @param string $sn est la séquence nucléique (peut être vide)
 * @return integer ID d'insertion d'un gène dans la table Gene (l'orthologue y fera référence)
 */
function constructNewOrthologue(int $original_sql_id, string $specie, string $id, string $addi, string $alias, string $sp, string $sn) : int {
    global $sql;
    // Vérif que l'ID existe pas déjà
    $id = mysqli_real_escape_string($sql, trim($id));
    
    if (empty($id)) {
        return 1;
    }
    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE gene_id='$id';");
    if (mysqli_num_rows($q)) {
        return 2;
    }

    $query = "INSERT INTO GeneAssociations (id, gene_id, sequence_adn, sequence_pro, specie, alias, addi) VALUES (";

    // Ajout de l'ID SQL
    $query .= "{$original_sql_id}, ";

    // Ajout du gene_ID
    $query .= "'$id', ";

    // Ajout de la séquence ADN
    $adn = mysqli_real_escape_string($sql, trim($sn));
    if (!empty($adn)) {
        $query .= "'$adn', ";
    }
    else {
        $query .= "NULL, ";
    }

    // Ajout de la séquence Pro
    $pro = mysqli_real_escape_string($sql, trim($sp));
    if (!empty($pro)) {
        $query .= "'$pro', ";
    }
    else {
        $query .= "NULL, ";
    }

    // Ajout de l'espèce
    $specie = mysqli_real_escape_string($sql, trim($specie));
    if (empty($specie)) {
        return 3;
    }
    else {
        $query .= "'$specie', ";
    }

    // Ajout de l'alias
    $a = mysqli_real_escape_string($sql, trim($alias));
    if (!empty($a)) {
        $query .= "'$a', ";
    }
    else {
        $query .= "NULL, ";
    }

    // Ajout des infos en +
    $a = mysqli_real_escape_string($sql, trim($addi));
    if (!empty($a)) {
        $query .= "'$a')";
    }
    else {
        $query .= "NULL)";
    }

    $q = mysqli_query($sql, $query);

    if ($q) {
        return 0;
    }
    else {
        return 4;
    }
}

/**
 * Construit un nouveau gène inséré dans la table SQL Gene.
 * Le gène n'est PAS complet et doit être complété avec au moins un orthologue/homologue.
 *
 * @param string $name
 * @param string $full
 * @param string $fam
 * @param string $subf
 * @param string $role
 * @param array $pathways
 * @return integer ID SQL d'insertion du gène dans Gene
 */
function constructNewGene(string $name, string $full, string $fam, string $subf, string $role, array $pathways) : int {
    global $sql;

    $query = "INSERT INTO Gene (func, gene_name, fullname, family, subfamily) VALUES (";

    // Ajout de la fonction
    $tmp = mysqli_real_escape_string($sql, trim($role));
    $query .= "'$tmp', ";

    // Ajout du nom
    $tmp = mysqli_real_escape_string($sql, trim($name));
    $query .= "'$tmp', ";

    /// Ajout du fullnom
    $tmp = mysqli_real_escape_string($sql, trim($full));
    $query .= "'$tmp', ";

    // Ajout de la famille
    $tmp = mysqli_real_escape_string($sql, trim($fam));
    $query .= "'$tmp', ";

    // Ajout de la famille
    $tmp = mysqli_real_escape_string($sql, trim($subf));
    $query .= "'$tmp')";

    $q = mysqli_query($sql, $query);

    if ($q) {
        $id = mysqli_insert_id($sql);

        // Insertion des pathways
        if ($id) {
            foreach ($pathways as $p) {
                $tmp = mysqli_real_escape_string($sql, trim($p));

                if ($tmp) {
                    mysqli_query($sql, "INSERT INTO Pathways (id, pathway) VALUES ($id, '$tmp')");
                }
            }

            return $id;
        }
    }

    return 0;
}

connectBD();
