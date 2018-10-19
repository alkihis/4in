<?php

// Page d'erreur 404

function notFoundControl() : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    return new Controller([], 'Page not found');
}

function notFoundView(Controller $c) : void {
    ?>
    <div class='container'>
        <div class='row section'>
            <h1 class='header center red-text'>Page not found</h1>
            <p class='flow-text center'>
                You're trying to reach a non-existant page.<br><br>
                <a href='/' class='underline-hover'>Accueil</a>
            </p>
        </div>
    </div>

    <?php
}
