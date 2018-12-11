<?php

if (isAdminLogged()) {
    session_write_close();
    global $sql;

    if (isset($_POST['sender'])) {
        $sender = mysqli_real_escape_string($sql, $_POST['sender']);

        $q = mysqli_query($sql, "DELETE FROM Messages WHERE sender='$sender';");

        if (!$q) {
            http_response_code(500);
        }
    }
    else if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];

        if ($id > 0) {
            $q = mysqli_query($sql, "DELETE FROM Messages WHERE id_message=$id;");
    
            if (!$q) {
                http_response_code(500);
            }
        }
        else {
            http_response_code(400);
        }
    }
    else {
        http_response_code(400);
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}

