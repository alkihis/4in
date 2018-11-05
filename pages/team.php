<?php

function teamControl(array $args) : Controller {
    return new Controller([], "Team");
}

function teamView(Controller $c) : void { ?>
    <div class="container">
        <div class="row">
            <h3 class="lighter-text">Team</h3>
            <div class="divider" style="margin-bottom: 15px;"></div>

            <div class="col s12 l6">
                <a href="http://bf2i.insa-lyon.fr/">
                    <div class="card-panel linkable-card grey lighten-5 card-border">
                        <div class="row valign-wrapper no-margin-bottom">
                            <div class="col s2">
                                <img src="/img/bf2i.jpg" class="circle responsive-img">
                            </div>
                            <div class="col s10">
                                <span class="black-text">
                                    BF2I laboratory studies insects.
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col s12 l6">
                <a href="https://www.univ-lyon1.fr/">
                    <div class="card-panel linkable-card grey lighten-5 card-border">
                        <div class="row valign-wrapper no-margin-bottom">
                            <div class="col s2">
                                <img src="/img/ucbl.png" class="circle responsive-img">
                            </div>
                            <div class="col s10">
                                <span class="black-text">
                                    Designers and conceptors of this website are at Lyon 1 Claude Bernard University.
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <?php
}
