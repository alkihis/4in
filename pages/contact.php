<?php

function contactControl(array $args) : Controller {
    $data = [];

    if (isset($_POST['content'], $_POST['token_rec']) && is_string($_POST['content']) && is_string($_POST['token_rec'])) {
        $mail = trim(filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL));
        $content = trim($_POST['content']);

        // Vérification du token Recaptcha
        $token = $_POST['token_rec'];

        $c = curl_init();
        curl_setopt(
            $c, 
            CURLOPT_URL, 
            "https://www.google.com/recaptcha/api/siteverify?secret=6LcHAnkUAAAAAPSHEhzucnNMKK65WI1EaPe5go2X&response=$token"
        );
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $return = curl_exec($c);

        // Décode la réponse du Captcha
        $json = json_decode($return, true);

        if (!$json['success']) {
            $data['duplicate'] = true;
        }
        else if ($json['score'] >= THRESHOLD_RECAPTCHA) {
            if ($content && strlen($content) >= 10) { // 10 chars mini pour le message
                $data['content'] = $content;

                if ($mail) {
                    $data['email_address'] = $mail;
                } // Sauvegarde des données pour les réinjecter dans le formulaire si besoin

                if (strlen($content) > MAX_LEN_MESSAGE) {
                    $data['too_long'] = true;
                }
                else if (strlen($mail) > MAX_LEN_EMAIL) {
                    $data['too_long_mail'] = true;
                }
                else {
                    if ($mail) {
                        // Vérification du délai
                        if (isset($_SESSION['contact_time_reset']) && $_SESSION['contact_time_reset'] > time()) {
                            $data['too_recent'] = true;
                            $data['remaining_time'] = $_SESSION['contact_time_reset'] - time();
                        }
                        else { // Si toutes les conditions ont été réunies, on peut envoyer le message
                            global $sql;
                            $content = mysqli_real_escape_string($sql, $content);
                            $mail = mysqli_real_escape_string($sql, $mail);

                            $_SESSION['contact_time_reset'] = time() + TIME_BEFORE_NEW_MESSAGE;

                            $q = mysqli_query($sql, "INSERT INTO Messages (content, sender) VALUES ('$content', '$mail');");

                            if ($q) {
                                $data['sended'] = true;
                                $data['content'] = null;
                            }
                            else {
                                Logger::write(mysqli_error($sql));

                                $data['fail_send'] = true;
                            }
                        }
                    }
                    else {
                        $data['mail_invalid'] = true;
                    }
                }
            }
            else {
                $data['no_content'] = true;
            }
        }
        else {
            $data['error_captcha'] = true;
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
                <div class="divider contact-us" style="margin-bottom: 20px;"></div>
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
            <form method="post" action="#">
                <div class="row">
                    <?php if (isset($data['too_long'])) { ?>
                        <h6 class="red-text">Your message is too long. Maximum length is <?= MAX_LEN_MESSAGE ?> characters.</h6>
                    <?php } 
                    if (isset($data['too_long_mail'])) { ?>
                        <h6 class="red-text">Your e-mail address is too long. Maximum length is <?= MAX_LEN_EMAIL ?> characters.</h6>
                    <?php } 
                    if (isset($data['too_recent'])) { ?>
                        <h6 class="red-text">Please wait 
                        <span id="remaining_time"><?= $data['remaining_time'] ?></span>
                        second<span id="seconds_s"><?= $data['remaining_time'] > 1 ? 's' : '' ?></span> 
                        before sending a new message.</h6>
                    <?php }
                    if (isset($data['fail_send'])) { ?>
                        <h6 class="red-text">Unable to send message. Please renew your request.</h6>
                    <?php }
                    if (isset($data['mail_invalid'])) { ?>
                        <h6 class="red-text">Your e-mail address is invalid.</h6>
                    <?php }
                    if (isset($data['no_content'])) { ?>
                        <h6 class="red-text">Your message may have at least 10 characters.</h6>
                    <?php }
                    if (isset($data['duplicate'])) { ?>
                        <h6 class="red-text">You may have tried to send an e-mail twice. Please renew your request.</h6>
                    <?php }
                    if (isset($data['error_captcha'])) { ?>
                        <h6 class="red-text">You seem to have automated behaviour. Try again later.</h6>
                    <?php } 
                    if (isset($data['sended'])) { ?>
                        <h6 class="green-text">Your message has been successfully sended.</h6>
                    <?php } ?>
                    <div class="input-field col s12">
                        <input type='email' class="validate" id='mail' name="mail" required
                        value="<?= htmlspecialchars($data['email_address'] ?? "", ENT_QUOTES) ?>">
                        <label for="mail">Your e-mail address</label>
                    </div>
                    <div class="input-field col s12">
                        <textarea class="materialize-textarea" placeholder="Write your message" maxlength="<?= MAX_LEN_MESSAGE ?>"
                        id="content" name="content" required><?= htmlspecialchars($data['content'] ?? '') ?></textarea>
                        <label for="content">Content</label>
                    </div>

                    <span class="left light-text black-text" style="margin-top: 4px; margin-left: 10px;">
                        Protected by <a target="_blank" href="https://www.google.com/recaptcha">ReCaptcha</a>
                    </span>

                    <button class="btn-flat blue-text right">Send</button>
                </div>

                <input type="hidden" value="" name="token_rec">
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

    <script src="https://www.google.com/recaptcha/api.js?render=6LcHAnkUAAAAABcAGti5NQsg2iX3Lt6g-0_bYTA-"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LcHAnkUAAAAABcAGti5NQsg2iX3Lt6g-0_bYTA-', {action: 'homepage'}).then(function(token) {
                document.querySelector('input[name=token_rec]').value = token;
            });
        });
    </script>

    <script>
        $(function() {
            var d = document.getElementById('remaining_time');
            var s = document.getElementById('seconds_s');

            function refreshTimer(count) {
                if (count <= 0) {
                    $(d.parentElement).slideUp(200, function() {
                        $(this).remove();
                    });
                } 
                else {
                    if (count === 1) {
                        s.innerText = "";
                    }
                    
                    d.innerText = count;

                    setTimeout(function() {
                        refreshTimer(count - 1);
                    }, 1000);
                }
            }

            if (d) {
                refreshTimer(Number(d.innerText));
            }
        });
    </script>

    <?php
}
