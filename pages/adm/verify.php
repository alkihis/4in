<?php

function verifyController() : array {
    return ['active_page' => 'verify'];
}

function verifyView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Check all available genes linked to ArthopodaCyc.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="card card-border">
                <div class="card-content">
                    <button class="btn-flat btn-perso left green-text center-block center" onclick="verifyAllGenes()">Verify all genes</button>

                    <div class="clearb"></div>
                    <div class="divider divider-margin"></div>

                    <div class="black-text flow-text medium-light-text" id="output_first_verify">
                        Click on "Verify all genes" to start verification.
                    </div>
                    <div class="black-text flow-text medium-light-text" id="output_verify"></div>
                    
                </div>
            </div>
        </div>
    </div>
    <?php
}
