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
            <h3 class='header center red-text'>Erreur interne du serveur</h3>
            <p class='flow-text center'>
                Les données transmises sont invalides, 
                ou le serveur a rencontré un problème inattendu. <br>
                
                Veuillez réessayer ultérieurement.
                <br>
            </p>
            
            <?php if (DEBUG_MODE) { ?>
            <div class='server-error-text center' style="margin: 10px auto">
                <pre><?= htmlspecialchars($e->getMessage()) ?></pre>
            </div>
            <?php } ?>

            <p class='flow-text center'>
                <a href='#!' onclick='window.history.back()'>Page précédente</a>
            </p>
        </div>
    </div>

    <?php
}
