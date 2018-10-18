<?php

// Page d'erreur 500

function serverErrorControl(Throwable $e) : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    return new Controller(['error' => $e], 'Erreur interne du serveur');
}

function serverErrorView(Controller $c) : void {
    $e = $c->getData()['error'];
    ?>
    <div class='container'>
        <div class='row section'>
            <h1 class='header center'>500</h1>
            <h3 class='header center red-text'>Internal server error</h3></h3>
            <p class='flow-text center'>
                The provided data is incorrect, 
                or the server encountered an unexpected issue. <br>
                
                Please try again later.
                <br>
            </p>
            
            <?php if (DEBUG_MODE) { ?>
            <div class='server-error-text center' style="margin: 10px auto">
                <pre><?= htmlspecialchars($e->getMessage()) ?></pre>
            </div>
            <?php } ?>

            <p class='flow-text center'>
                <a href='#!' onclick='window.history.back()'>Previous</a>
            </p>
        </div>
    </div>

    <?php
}
