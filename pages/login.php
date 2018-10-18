<?php

function loginControl($args) : Controller {
    $returns = [];

    global $sql;

    if (isUserLogged()) {
        unlogUser();
        $returns['unlogged'] = true;
    }
    else if (isset($_POST['login'], $_POST['password'])) { // Si il y a un login et mot de passe défini
        $login = mysqli_real_escape_string($sql, $_POST['login']);
        $psw = $_POST['password'];

        // Récupération du password associé au login
        $q = mysqli_query($sql, "SELECT passw FROM Users WHERE username='$login';");

        if (!$q) {
            throw new Exception("Base de données utilisateur non fonctionnelle.");
        }
        if (mysqli_num_rows($q) > 0) { // Un utilisateur est trouvé
            $row = mysqli_fetch_assoc($q);

            if (password_verify($psw, $row['passw'])) {
                // Connexion réussie, connexion à faire
                $q = mysqli_query($sql, "SELECT * FROM Users WHERE username='$login';");
                if (!$q) {
                    throw new Exception("Base de données utilisateur non fonctionnelle.");
                }

                $row = mysqli_fetch_assoc($q);
                logUser($row); // Enregistre l'utilisateur dans la session

                $returns['successful_connection'] = true;
            }
            else { // Mot de passe invalide
                $returns['try_connect'] = true;
            }
        }
        else { // Login introuvable
            $returns['try_connect'] = true;
        }
    }

    if (isset($returns['try_connect'])) {
        $returns['login'] = htmlspecialchars($_POST['login'], ENT_QUOTES);
    }

    return new Controller($returns, "Login");
}

function loginView(Controller $c) : void {
    $data = $c->getData();

    ?>
    <div class='container section'>
        <div class='row'>
            <div class='card card-border col s10 offset-s1'>
                <div class='card-content'>
                    <h4 class='header'>Login</h4>
                    <?php 
                    if (isset($data['try_connect'])) {
                        echo '<h6 class="red-text">Identifiant ou mot de passe incorrect.</h6>';
                    }
                    else if (isset($data['unlogged'])) {
                        echo '<h6 class="green-text">Vous avez été déconnecté-e.</h6>';
                    }
                    else if (isset($data['successful_connection'])) {
                        echo '<h6 class="green-text">Vous vous êtes connecté-e avec succès.</h6>';
                    }
                    ?>
                    <form method='post' action='/login'>
                        <div class='input-field col s12'>
                            <input type='text' name='login' id='login' value='<?= $data["login"] ?? "" ?>' required>
                            <label for='login'>ID</label>
                        </div>
                        <div class='input-field col s12'>
                            <input class='validate' type='password' name='password' id='password' required>
                            <label for='password'>Passworde</label>
                        </div>
                        <button class='btn-flat blue-text right'>Login</button>
                        <div class='clearb'></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}
