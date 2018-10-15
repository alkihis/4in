<?php

// Page d'erreur 404

function notFoundControl() : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    return new Controller([], 'Page non trouvée');
}

function notFoundView(Controller $c) : void {
    ?>
    <div class='container'>
        <div class='row section'>
            <h1 class='header center red-text'>Page non trouvée</h1>
            <p class='flow-text center'>
                Vous tentez d'accéder à une page qui n'existe pas.<br><br>
                <a href='/' class='underline-hover'>Accueil</a>
            </p>
        </div>
    </div>

    <?php
}
