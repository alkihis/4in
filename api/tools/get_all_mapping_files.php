<?php

session_start();

if (isUserLogged()) {
    session_write_close();

    $q = glob($_SERVER['DOCUMENT_ROOT'] . '/fasta/map/*');
    $new = [];
    foreach ($q as $adn) {
        $new[] = basename($adn);
    }

    echo json_encode($new);
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
