<?php

function converterController() : array {
    return ['active_page' => 'converter'];
}

function converterView(array $data) : void { ?>
    <div class="row">
        <div class="col s12">

            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    In order to use FASTA file to update sequences informations, the gene ID in the fasta 
                    sequence comment MUST be the first element of the comment line (after the &gt;), and be
                    separated from other elements by a non-writable character (space, tabulation, form-feed...).<br>
                    If file is not correctly formatted, you can use this FASTA converter.<br>
                    Provide the theorical gene ID position (used in this database) in a array that represents
                    the comment line, splitted by defined separator.<br>
                </p>
            </div>


            <div class="card card-border">
                <div class="card-content">
                    <form method="post" target="_blank" 
                        action="/api/tools/fasta_converter.php" enctype="multipart/form-data">

                        <div class="file-field input-field col s12">
                            <div class="btn light-blue darken-1">
                                <span>FASTA file</span>
                                <input name="input" accept="application/fasta" required type="file">
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text">
                            </div>
                        </div>

                        <div class="input-field col s12">
                            <input type="number" name="id_pos" id="pos" value="0">
                            <label for="pos">ID position in comment line (0+ notation)</label>
                        </div>

                        <div class="input-field col s12">
                            <input type="text" name="sep" id="sep" value="\s">
                            <label for="sep">Separator between FASTA comment items</label>
                            <span style="font-size: .9rem;">For space or tabulation, use "\s" and check "Regex separator"</span>
                        </div>

                        <p class="col s6">
                            <label>
                                <input type="checkbox" name="is_regex" checked>
                                <span>Regex separator</span>
                            </label>
                        </p>

                        <p class="col s6">
                            <label>
                                <input type="checkbox" name="no_empty" value="true">
                                <span>Skip empty parts while splitting</span>
                            </label>
                        </p>

                        <button type="submit" class="btn-flat blue-text right">Convert</button>
                        <div class="clearb"></div>
                    </form>
                </div>
            </div>

            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    <span class="flow-text">Hint</span><br>
                    If comment line in your file is written ">{alias}|{random_infos}:{other_things}|{real_gene_id}",
                    separator will be "|" and ID position will be 2.<br><br>
                    Explaination: Split the line by "|" will give <br>
                    Array (<br>
                        &nbsp;&nbsp;0: "{alias}", <br>
                        &nbsp;&nbsp;1: "{random_infos}:{other_things}", <br>
                        &nbsp;&nbsp;2: "{real_gene_id}"<br>
                    )
                </p>
            </div>
        </div>
    </div>
    <?php
}
