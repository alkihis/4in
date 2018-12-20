<?php

function createUserController() : array {
    $data = ['active_page' => 'create_user'];

    global $sql;

    if (isset($_POST['new_name'], $_POST['new_password'], $_POST['user_level']) 
    && is_string($_POST['new_name']) && is_string($_POST['user_level']) && is_string($_POST['new_password'])) {
        $user = mysqli_real_escape_string($sql, trim($_POST['new_name']));

        if (preg_match(REGEX_USERNAME, $user)) {
            $password = $_POST['new_password'];
            $level = (int)$_POST['user_level'];
    
            if (strlen(trim($password)) <= 6) {
                $data['empty_psw'] = true;
            }
            else {
                if ($level > USER_PERM_UNLOGGED && $level <= USER_PERM_ADMINISTRATOR) {
                    // Vérifier que l'utilisateur existe pas
                    $q = mysqli_query($sql, "SELECT id_user FROM Users WHERE username='$user'");
                    if (mysqli_num_rows($q) === 0) {
                        $psw = password_hash($password, PASSWORD_BCRYPT);
    
                        mysqli_query($sql, "INSERT INTO Users (username, passw, rights) VALUES ('$user', '$psw', $level)");
                        $data['added'] = true;
                    }
                    else {
                        $data['equals'] = true;
                    }
                }
                else {
                    $data['odd_perm'] = true;
                }
            }
        }
        else {
            $data['ab_username'] = true;
        }
    }

    return $data;
}

function createUserView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Create a new user here.
                </p>
            </div>

            <?php 
            if (isset($data['added'])) {
                echo '<h5 class="green-text">The user has been successfully added.</h5>';
            }
            else if (isset($data['equals'])) {
                echo '<h5 class="red-text">Username does already exists.</h5>';
            }
            else if (isset($data['empty_psw'])) {
                echo '<h5 class="red-text">Password cannot be empty and must be composed of 6 characters at least.</h5>';
            }
            else if (isset($data['odd_perm'])) {
                echo '<h5 class="red-text">Une permission incorrecte a été entrée.</h5>';
            }
            else if (isset($data['ab_username'])) {
                echo '<h5 class="red-text">Username is incorrect. Must be 4 chars long.</h5>';
            }
            ?>

            <form method="post" action="#" id="form_spec">
                <div class="card card-border">
                    <div class="card-content">
                        <p>
                            Basic user will have no permission.<br>
                            Trusted user can see protected species.<br>
                            Contributor can modify and add new genes.<br>
                            Administrator can access the administration console, build database, create and delete users.
                        </p>

                        <div class="clearb" style="margin-top: 10px;"></div>

                        <div class='input-field col s7'>
                            <input type="text" class="validate" name="new_name" id='new_name' autocomplete="off" 
                            required pattern="^[A-Za-z]{1}[A-Za-z0-9_-]{3,31}$">
                            <label for="new_name">New username</label>
                        </div>
                        <div class="input-field col s5">
                            <select name="user_level">
                                <option value="<?= USER_PERM_BASIC ?>">Basic user</option>
                                <option value="<?= USER_PERM_VISITOR ?>">Trusted user</option>
                                <option value="<?= USER_PERM_CONTRIBUTOR ?>">Contributor</option>
                                <option value="<?= USER_PERM_ADMINISTRATOR ?>">Administrator</option>
                            </select>
                            <label>User level</label>
                        </div>
                        <div class='input-field col s12'>
                            <input type="password" name="new_password" id='new_password' autocomplete="off" required>
                            <label for="new_password">Password</label>
                        </div>

                        <div class="col s12">
                            <button type="submit" class="btn-flat right blue-text">
                                <i class="material-icons left sub">add</i> Add</button>
                        </div>

                        <div class="clearb"></div>
                    </div>
                </div>
                
                <div class="clearb"></div>
            </form>
        </div>
    </div>
    <?php
}
