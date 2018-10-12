<?php

// Page d'erreur 403

function forbiddenControl() : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    return new Controller([], 'Accès interdit');
}

function forbiddenView(Controller $c) : void {
    ?>
    <div class='container'>
        <div class='row section'>
            <h1 class='header center red-text'>Page non disponible</h1>
            <p class='flow-text center'>
                Vous tentez d'accéder à une page qui demande certaines autorisations.<br>
                Tentez de vous connecter, ou réessayez utlérieurement.<br><br>
                <a href='/' class='underline-hover'>Accueil</a>
            </p>
        </div>
    </div>

    <?php
}
