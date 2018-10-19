<?php

// Page d'erreur 403

function forbiddenControl() : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    return new Controller([], 'Forbidden');
}

function forbiddenView(Controller $c) : void {
    ?>
    <div class='container'>
        <div class='row section'>
            <h1 class='header center red-text'>Page not available</h1>
            <p class='flow-text center'>
                Yu're trying to reach a page that requires specific authorizations.<br>
                Try logging in or try again later.<br><br>
                <a href='/' class='underline-hover'>Home</a>
            </p>
        </div>
    </div>

    <?php
}
