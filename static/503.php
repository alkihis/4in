<?php

// Page d'erreur 503

function maintenanceControl() : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable');
    return new Controller([], 'Service Unavailable');
}

function maintenanceView(Controller $c) : void {
    ?>
    <div id='particle-holder' class="full-page">
        <div class='container'>
            <div class='row section'>
                <div style='margin-top: 90px;'></div>
                <div class='tiny-container'>
                    <h2 class='header lighter-text red-att-text'>503 <span class='tiny-text'>Service Unavailable</span></h2>
                    <div class='divider divider-white'></div>
                    <p class='white-text text-justify'>
                        Website is currently under maintenance.<br>
                        Please come back later.
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
