<?php

// Page d'erreur 403

function forbiddenControl() : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    return new Controller([], 'Forbidden');
}

function forbiddenView(Controller $c) : void {
    ?>
    <div id='particle-holder'>
        <div class='container'>
            <div class='row section'>
                <div style='margin-top: 90px;'></div>
                <div class='tiny-container'>
                    <h2 class='header lighter-text red-att-text'>403 <span class='tiny-text'>Forbidden</span></h2>
                    <div class='divider divider-white'></div>
                    <p class='white-text text-justify'>
                        You're trying to reach a page that requires specific authorization.<br>
                        Please log in or try again later.<br><br>
                    </p>
                    <p class='flow-text center'>
                        <br>
                        <a href='/' class='underline-hover blue-att-text'>Home</a>
                    </p>
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
