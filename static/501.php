<?php

// Page d'erreur 501 (like, le 501 n'est PAS autorisé réellement)

function serverImplementControl(Throwable $e) : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    return new Controller(['error' => $e], 'Function not yet implemented');
}

function serverImplementView(Controller $c) : void {
    $e = $c->getData()['error'];
    ?>
    <div id='particle-holder'>
        <div class='container'>
            <div class='row section'>
                <div style='margin-top: 90px;'></div>
                <div class='tiny-container'>
                    <h2 class='header lighter-text red-att-text'>501 <span class='tiny-text'>Unavailable Function</span></h2>
                    <div class='divider divider-white'></div>
                    <p class='white-text text-justify'>
                        This page or functionnality is not yet available on our website.<br>
                        Please try again later.
                        <br>
                    </p>

                    <?php if (DEBUG_MODE) { ?>
                    <div class='server-error-text center' style="margin: 10px auto">
                        <pre><?= htmlspecialchars($e->getMessage()) ?></pre>
                    </div>
                    <?php } ?>

                    <p class='flow-text center'>
                        <a href='#!' class='blue-att-text underline-hover' onclick='window.history.back()'>Previous</a>
                    </p>
                    <div class='clearb' style='margin-bottom: 50px'></div>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/particles.min.js"></script>
    <script>
        $(document).ready(function () {
            particlesJS.load('particle-holder', '/assets/particlesjs-config.json');

            $('main').addClass('with-particle');
        });
    </script>

    <?php
}
