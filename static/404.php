<?php

// Page d'erreur 404

function notFoundControl(PageNotFoundException $t) : Controller {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    return new Controller(['message' => $t->getMessage()], 'Page not found');
}

function notFoundView(Controller $c) : void {
    $data = $c->getData();
    ?>
    <div id='particle-holder'>
        <div class='container'>
            <div class='row section'>
                <div style='margin-top: 90px;'></div>
                
                <div class='tiny-container'>
                    <h2 class='header lighter-text red-att-text'>404 <span class='tiny-text'>Page Not Found</span></h2>
                    <div class='divider divider-white'></div>
                    <p class='white-text text-justify'>
                        <?php if ($data['message']) { 
                            $msg = htmlspecialchars($data['message']);
                            echo "<span class='fatal-error-message'>$msg</span><br><br>"; 
                        } else {
                            echo "You're trying to reach a non-existant page.<br><br>";
                        } ?>
                        Please check the page URL or use search module to find specific informations.
                    </p>
                    <p class='flow-text center'>
                        <br>
                        <a href='/' class='underline-hover blue-att-text'>Home</a>
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
