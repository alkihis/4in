<?php

// Page d'erreur 501 (like, le 501 n'est PAS autorisé réellement)

function serverImplementControl(Throwable $e) : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    return new Controller(['error' => $e], 'Fonctionnalité non implémentée');
}

function serverImplementView(Controller $c) : void {
    $e = $c->getData()['error'];
    ?>
    <div class='container'>
        <div class='row section'>
            <h1 class='header center'>501</h1>
            <h3 class='header center red-text'>Fonction non disponible</h3>
            <p class='flow-text center'>
                Cette page ou fonctionnalité n'est pas encore disponible sur le site.<br>
                Nous vous invitons à revenir sur ce module ultérieurement.
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
