<?php

// Page d'erreur 500

function serverErrorControl(Throwable $e) : Controller {
    // Log de l'exception
    Logger::write($e->__toString());

    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    return new Controller(['error' => $e], 'Internal Server Error');
}

function serverErrorView(Controller $c) : void {
    $e = $c->getData()['error'];
    ?>
    <div id='particle-holder'>
        <div class='container'>
            <div class='row section'>
                <div style='margin-top: 90px;'></div>
                <div class='tiny-container'>
                    <h2 class='header lighter-text red-att-text'>500 <span class='tiny-text'>Internal Server Error</span></h2>
                    <div class='divider divider-white'></div>
                    <p class='white-text text-justify'>
                        The provided data is incorrect, 
                        or the server encountered an unexpected issue. <br>
                        
                        Please try again later.
                    </p>

                    <?php if (DEBUG_MODE && $e->getMessage()) { ?>
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
