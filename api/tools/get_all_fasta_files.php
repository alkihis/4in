<?php

if (isUserLogged()) {
    session_write_close();

    $q = glob($_SERVER['DOCUMENT_ROOT'] .FASTA_ADN_DIR . '*');
    $new = [];
    foreach ($q as $adn) {
        $new['adn'][] = basename($adn);
    }

    $q = glob($_SERVER['DOCUMENT_ROOT'] . FASTA_PRO_DIR . '*');
    foreach ($q as $pro) {
        $new['pro'][] = basename($pro);
    }

    echo json_encode($new);
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
