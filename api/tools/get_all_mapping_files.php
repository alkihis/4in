<?php

if (isAdminLogged()) {
    session_write_close();

    $q = glob($_SERVER['DOCUMENT_ROOT'] . MAPPING_DIR . '*');
    $new = [];
    foreach ($q as $adn) {
        $new[] = basename($adn);
    }

    echo json_encode($new);
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
