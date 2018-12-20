<?php

function manageUserController() : array {
    $data = ['active_page' => 'manage_user'];

    global $sql;

    if (isset($_POST['delete_user']) 
    && is_numeric($_POST['delete_user'])) {
        $user = (int)$_POST['delete_user'];

        if ($user !== $_SESSION['user']['id']) {
            $q = mysqli_query($sql, "SELECT id_user, rights FROM Users WHERE id_user='$user'");
            if (mysqli_num_rows($q)) {
                $r = mysqli_fetch_assoc($q);
    
                if ((int)$r['rights'] === USER_PERM_ADMINISTRATOR) {
                    // Vérification qu'il reste au moins un admin après suppression
                    $q = mysqli_query($sql, "SELECT id_user FROM Users WHERE rights=" . USER_PERM_ADMINISTRATOR);
                    if (mysqli_num_rows($q) < 2) {
                        $data['no_admin'] = true;
                    }
                }
    
                if (!isset($data['no_admin'])) {
                    mysqli_query($sql, "DELETE FROM Users WHERE id_user=$user");
                    $data['deleted'] = true;
                }
            }
            else {
                $data['not_exists'] = true;
            }
        }
        else {
            $data['delete_yourself'] = true;
        }
    }

    if (isset($_POST['change_level_user'], $_POST['user_level']) 
    && is_numeric($_POST['change_level_user']) && is_string($_POST['user_level'])) {
        $user = (int)$_POST['change_level_user'];
        $level = (int)$_POST['user_level'];

        if ($user !== $_SESSION['user']['id']) {
            $q = mysqli_query($sql, "SELECT id_user FROM Users WHERE id_user='$user'");
            if (mysqli_num_rows($q)) {
                if ($level > USER_PERM_UNLOGGED && $level <= USER_PERM_ADMINISTRATOR) {
                    $q = mysqli_query($sql, "UPDATE Users SET rights=$level WHERE id_user='$user'");
                    
                    $data['updated'] = true;
                }
                else {
                    $data['odd_perm'] = true;
                }
            }
            else {
                $data['not_exists'] = true;
            }
        }
        else {
            $data['delete_yourself'] = true;
        }
    }

    $q = mysqli_query($sql, "SELECT id_user, username, rights FROM Users");

    $data['users'] = [];
    foreach ($q as $user) {
        $data['users'][] = $user;
    }

    return $data;
}

function textualLevel(int $level) {
    switch ($level) {
        case -1:
            return "Basic user";
        case 0:
            return "Trusted user";
        case 1:
            return "Contributor";
        case 2:
            return "Administrator";
    }
}

function manageUserView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Manage existing users here.
                </p>
            </div>

            <?php 
            if (isset($data['updated'])) {
                echo '<h5 class="green-text">The user has been successfully updated.</h5>';
            }
            else if (isset($data['deleted'])) {
                echo '<h5 class="green-text">The user has been successfully deleted.</h5>';
            }
            else if (isset($data['not_exists'])) {
                echo '<h5 class="red-text">User does not exists.</h5>';
            }
            else if (isset($data['odd_perm'])) {
                echo '<h5 class="red-text">Incorrect user level.</h5>';
            } 
            else if (isset($data['no_admin'])) {
                echo '<h5 class="red-text">You can\'t delete an admin if there is no other admin.</h5>';
            }
            else if (isset($data['delete_yourself'])) {
                echo '<h5 class="red-text">You can\'t modify or delete your own account.</h5>';
            } ?>
           
            <ul class="collection">
                <?php foreach ($data['users'] as $user) { ?>
                    <li class="collection-item">
                        <div>
                            <?= htmlspecialchars($user['username']) ?> (<?= textualLevel($user['rights']) ?>)
                            <a href="#!" class="secondary-content edit-user" data-username="<?= htmlspecialchars($user['username']) ?>"
                            data-id="<?= $user['id_user'] ?>" data-level="<?= $user['rights'] ?>">
                                <i class="material-icons">mode_edit</i>
                            </a>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <script>
        $(function() { initUserEditFeatures(); });
    </script>
    <?php
}
