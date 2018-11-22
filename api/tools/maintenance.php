<?php

if (isAdminLogged()) {
    session_write_close();

    if (isset($_POST['status']) && is_string($_POST['status'])) {
        $maintenance = ($_POST['status'] === 'maintenance');

        var_dump($maintenance);
        saveMaintenanceStatus($maintenance);
    }
    else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
