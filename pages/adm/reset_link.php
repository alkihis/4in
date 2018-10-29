<?php

function resetLinkController() : array {
    $data = ['active_page' => 'reset'];

    if (isset($_POST['reset'])) {
        global $sql;

        $q = mysqli_query($sql, "UPDATE GeneAssociations SET linkable=NULL;");

        if ($q) {
            $data['reset'] = true;
        }
        else {
            echo mysqli_error($sql);
            $data['error'] = true;
        }
    }

    return $data;
}

function resetLinkView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    Reset link status will erase information who remember if links to external database are broken or not
                    for a specific gene ID.<br>
                    In case of optimisation, the website will check once -when first accessing to specific gene page- if the gene ID
                    exists in ArthropodaCyc Database.<br>
                    After storing the information, the status will not be updated anymore.<br>
                    You can use this page to force the website to renew link status again.
                </p>
            </div>

            <?php 
            if (isset($data['reset'])) {
                echo '<h5 class="green-text">Link status has been successfully reset</h5>';
            }
            else if (isset($data['error'])) {
                echo '<h5 class="red-text">An unknown error occurred</h5>';
            }
            ?>

            <form method="post" action="#">
                <div class="card card-border">
                    <div class="card-content">
                        <p>
                            Check this box to confirm reset.<br><br>
                            <label>
                                <input type="checkbox" name="reset" 
                                    onchange="(this.checked ? $('#reset_btn').slideDown(250) : $('#reset_btn').slideUp(250))">
                                <span>Reset link status</span>
                            </label>
                            <br>
                        </p>

                        <div class="clearb"></div>

                        <button type="submit" id="reset_btn" 
                            style="display: none; padding: 0; margin-top: 20px;" class="btn-flat red-text">
                            Reset all link status
                        </button>
                    </div>
                </div>
                
                <div class="clearb"></div>
            </form>
        </div>
    </div>

    <?php
}

