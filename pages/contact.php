<?php

function contactControl(array $args) : Controller {
    $data = [];

    if (isset($_POST['mail']) && is_string($_POST['mail'])) {
        $mail = trim($_POST['mail']);

        if ($mail) {
            // send mail...
            // todo

            $data['no_mail'] = true;
            $data['mail'] = htmlspecialchars($mail);
        }
    }

    return new Controller($data, 'Contact us');
}

function contactView(Controller $c) : void {
    $data = $c->getData();

    ?>
    <div class="parallax-container parallax-contact-page">
        <div class="section no-pad-bot">
            <div class="container">
                <h1 class="header center  white-text">Contact Us</h1>
                <div class="divider" style="margin-bottom: 20px;"></div>
                <div class="row center">
                    <p class='flow-text head-main-title white-text '>
                        Let us know what you're thinking about. 
                    </p>
                </div>
                <br><br>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2 class="header light-text" style="margin-top: 10px;">Send an e-mail</h2>
            <form method="post">
                <div class="row">
                    <?php if (isset($data['no_mail'])) { ?>
                        <h6 class="red-text">This service is not ready yet. We're hard working on it, please be patient !</h6>
                    <?php } ?>
                    <div class="input-field col s12">
                        <textarea class="materialize-textarea" placeholder="Write here your message" 
                        id="mail" name="mail" required><?= $data['mail'] ?? '' ?></textarea>
                        <label for="mail">Content</label>
                    </div>

                    <button class="btn-flat blue-text right">Send</button>
                </div>
            </form>
        </div>

        <div class="divider"></div>

        <div class="section">
            <h2 class="header light-text" style="margin-top: 10px;">Call</h2>
            <div class="row">
                <p class="flow-text">
                    You can be in touch with us by calling BF2i laboratory (UMR0203: Biologie Fonctionnelle Insectes et Interactions), at 
                    this number : <a class="underline-hover" href="tel:+33472438356">+33 4 72 43 83 56</a> (France).
                </p>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <h2 class="header light-text" style="margin-top: 10px;">More infos</h2>
            <div class="row">
                <p class="flow-text">
                    You shall find more informations about us by consulting our <a href="/team" class="underline-hover">team</a>, 
                    or by visiting <a href="http://bf2i.insa-lyon.fr/" class="underline-hover">BF2i website</a>.
                </p>
            </div>
        </div>
    </div>

    <?php
}
