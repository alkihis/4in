<?php

function iControl(array $args) : Controller {
    if (!defined('INITIAL_CONFIGURATION') || !INITIAL_CONFIGURATION) {
        throw new PageNotFoundException;
    }

    // Définit l'utilisateur courant comme admin
    logUser(['id_user' => 0, 'username' => 'TEMPORARY_USER', 'rights' => 2], false);

    $a = buildDatabaseFromScratch('');

    return new Controller(['data' => $a], 'Initial configuration');
}

function iView(Controller $c) : void {
    var_dump($c->getData()['data']);
    echo '<br>Vous êtes connecté en tant qu\'administrateur. 
    Créez désormais un nouvel utilisateur via la console d\'administration.';
}
