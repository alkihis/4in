<?php

if (isAdminLogged()) {
    session_write_close();

    require MAIN_DIR . 'inc/Gene.php';

    if (isset($_POST['gene']) && is_string($_POST['gene'])) {
        $gene = $_POST['gene'];

        header('Content-Type: application/json');

        try {
            $gene = new Gene($gene);
        } catch (Exception $e) {
            echo json_encode(['success' => false]);
            return;
        }

        if ($gene) {
            if (isProtectedSpecie($gene->getSpecie())) {
                echo json_encode(['success' => true]);
            }
            else if (!$gene->isLinkDefined()) {
                echo json_encode(['success' => checkSaveLinkValidity($gene->getSpecie(), $gene->getAlias() ?? $gene->getID(), (bool)$gene->getAlias())]);
            }
            else {
                echo json_encode(['success' => $gene->hasLink()]);
            }
        }
    }
    else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
}
