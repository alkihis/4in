<?php

function passwordController() : array {
    $data = ['active_page' => 'password'];

    if (isset($_POST['old_psw'], $_POST['new_psw'], $_POST['repeat_psw'])) {
        global $sql;

        $old = trim($_POST['old_psw']);
        $new = trim($_POST['new_psw']);
        $rep = trim($_POST['repeat_psw']);

        $data['form'] = [];

        $q = mysqli_query($sql, "SELECT passw FROM Users LIMIT 0,1;");
        $row = mysqli_fetch_assoc($q);
        $hash = $row['passw'];

        if ($new !== $rep) {
            $data['form']['not_indentical'] = true;
        }
        else if ($old === $new) {
            $data['form']['new_indentical'] = true;
        }
        else if (empty($new)) {
            $data['form']['empty_new'] = true;
        }
        else if (!password_verify($old, $hash)) {
            $data['form']['invalid_old'] = true;
        }
        else {
            // On change le mot de passe
            $new_hash = password_hash($new, PASSWORD_BCRYPT);

            mysqli_query($sql, "UPDATE Users SET passw='$new_hash';");

            $data['new_password'] = true;
        }
    }
    // Sinon : on génère le formulaire

    return $data;
}

function passwordView(array $data) : void { ?>
    <div class="row">
        <div class="card col s12 card-border" style='margin-top: 20px;'>
            <form method="post" action="#">
                <div class="card-content">
                    <?php 
                    if (isset($data['new_password'])) {
                        echo "<h5 class='green-text'>Password has been successfully updated</h5>";
                    }
                    else if (isset($data['form']['not_indentical'])) {
                        echo "<h5 class='red-text'>New passwords doesn't match</h5>";
                    }
                    else if (isset($data['form']['new_indentical'])) {
                        echo "<h5 class='red-text'>New password can't be your old password</h5>";
                    }
                    else if (isset($data['form']['empty_new'])) {
                        echo "<h5 class='red-text'>Password can't be empty</h5>";
                    }
                    else if (isset($data['form']['invalid_old'])) {
                        echo "<h5 class='red-text'>Old password is invalid</h5>";
                    }
                    ?>

                    <div class="row no-margin-bottom">
                        <div class="input-field col s12">
                            <input name="old_psw" id="password" type="password" class="validate" required>
                            <label for="password">Old password</label>
                        </div>
                    </div>
                    
                    <div class="row no-margin-bottom">
                        <div class="input-field col s12">
                            <input name="new_psw" id="password_new" type="password" class="validate" required>
                            <label for="password_new">New password</label>
                        </div>
                    </div>
                    <div class="row no-margin-bottom">
                        <div class="input-field col s12">
                            <input name="repeat_psw" id="password_rep" type="password" class="validate" required>
                            <label for="password_rep">Repeat new password</label>
                        </div>
                    </div>
                    <button type="submit" class="btn-flat right red-text">Change password</button>
                    <div class="clearb"></div>
                </div>
            </form>
        </div>
    </div>
    <?php
}
