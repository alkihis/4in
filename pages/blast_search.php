<?php

function searchBlastControl(array $args) : Controller {
    return new Controller([], "BLAST Search");
}

function searchBlastView(Controller $c) : void { ?>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <form method="post" action="/blast" enctype="multipart/form-data">
                    <div class="card-panel card-border" style='margin-top: 20px;'>
                        <div class="row no-margin-bottom">
                            <div class="col s2">
                                <div style="font-weight: bold;">Program</div>
                            </div>
                            <div class="col s10">
                                <div class="row no-margin-bottom">
                                    <div class="col s4">
                                        <label>
                                            <input name="program" value="n" type="radio" class="radio-blast" checked>
                                            <span>blastn</span>
                                        </label>
                                    </div>

                                    <div class="col s4">
                                        <label>
                                            <input name="program" value="x" class="radio-blast" type="radio">
                                            <span>blastx</span>
                                        </label>
                                    </div>
                                    
                                    <div class="col s4">
                                        <label>
                                            <input name="program" value="p" class="radio-blast" type="radio">
                                            <span>blastp</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="row no-margin-bottom">
                                    <div class="col s4">
                                        <label>
                                            <input name="program" value="tn" class="radio-blast" type="radio">
                                            <span>tblastn</span>
                                        </label>
                                    </div>

                                    <div class="col s4">
                                        <label>
                                            <input name="program" value="tx" class="radio-blast" type="radio">
                                            <span>tblastx</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="collapsible">
                        <li class="active">
                            <div class="collapsible-header"><i class="material-icons">sort</i>Query</div>
                            <div class="collapsible-body">
                                <div class="col s12">
                                    <span class="underline">Sequence in FASTA format</span>
                                </div>

                                <div class="input-field col s12">
                                    <textarea id="query" name="query" class="materialize-textarea" required></textarea>
                                    <label for="query">Query sequence</label>
                                </div>

                                <div class="clearb"></div>
                                <div class="divider" style="margin-bottom: 15px;"></div>
                               
                                <div class="col s12">
                                    <span class="underline">Set subsequence</span>
                                </div>
                                
                                <div class="input-field col s6">
                                    <input id="subset-low" type="number" name="subsetl" class="validate">
                                    <label for="subset-low">From</label>
                                </div>
                                <div class="input-field col s6">
                                    <input id="subset-up" type="number" name="subseth" class="validate">
                                    <label for="subset-up">To</label>
                                </div>
                                <div class="clearb"></div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">build</i>General</div>
                            <div class="collapsible-body">
                                <div class="input-field col s6">
                                    <select name="max_target_seqs">
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500" selected>500</option>
                                        <option value="2000">2000</option>
                                        <option value="5000">5000</option>
                                    </select>
                                    <label>Max target sequences</label>
                                </div>
                                <div class="input-field col s6">
                                    <select name="evalue">
                                        <option value="0.0001">0.0001</option>
                                        <option value="0.001">0.001</option>
                                        <option value="0.01">0.01</option>
                                        <option value="1">1</option>
                                        <option value="10" selected>10</option>
                                        <option value="100">100</option>
                                    </select>
                                    <label>Expect threshold (evalue)</label>
                                </div>

                                <div class="input-field col s12">
                                    <select name="word_size" id="word_size">
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="6">6</option>
                                        <option value="7" selected>7</option>
                                        <option value="11">11</option>
                                        <option value="15">15</option>
                                    </select>
                                    <label>Word size</label>
                                </div>
                            
                                <div class="clearb"></div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">exposure_plus_2</i>Scoring</div>
                            <div class="collapsible-body">
                                <div class="input-field col s6"> <!-- NOT FOR BLASTN -->
                                    <select disabled class="blast-select not-for-n" name="matrix">
                                        <option value="PAM30">PAM30</option>
                                        <option value="PAM250">PAM250</option>
                                        <option value="BLOSUM45">BLOSUM45</option>
                                        <option value="BLOSUM62" selected>BLOSUM62</option>
                                        <option value="BLOSUM80">BLOSUM80</option>
                                        <option value="BLOSUM90">BLOSUM90</option>
                                    </select>
                                    <label>Matrix</label>
                                </div>

                                <div class="input-field col s6"> <!-- NOT FOR BLASTN, TBLASTX -->
                                    <select disabled class="blast-select not-for-n not-for-tx" name="comp_based_stats">
                                        <option value="0">No adjustement</option>
                                        <option value="1">Composition-based statistics</option>
                                        <option value="2" selected>Conditional compositional score adj.</option>
                                        <option value="3">Universal compositional score adj.</option>
                                    </select>
                                    <label>Compositional adjustments</label>
                                </div>

                                <div class="input-field col s6"> <!-- NOT FOR TBLASTX -->
                                    <select class="blast-select not-for-tx" name="gapopen" id="gapopen">
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11" selected>11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                    </select>
                                    <label>Open gap cost</label>
                                </div>

                                <div class="input-field col s6"> <!-- NOT FOR TBLASTX -->
                                    <select class="blast-select not-for-tx" name="gapextend" id="gapextend">
                                        <option value="0">0</option>
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                    <label>Extend gap cost</label>
                                </div>
                            
                                <div class="clearb"></div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">filter_b_and_w</i>Filters</div>
                            <div class="collapsible-body">
                                <p>
                                    <label>
                                        <input type="checkbox" name="filter_low_complexity" data-tip="seg(p) or dust(n)">
                                        <span>Filter low complexity regions</span>
                                    </label>
                                </p>

                                <p>
                                    <label>
                                        <input type="checkbox" name="db_soft_mask">
                                        <span>Mask for lookup table only</span>
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input type="checkbox" name="lcase_masking">
                                        <span>Mask lower case letters</span>
                                    </label>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">add_box</i>Additionnals</div>
                            <div class="collapsible-body">
                                <div class="input-field col s6"> <!-- ONLY FOR BLASTX,TBLASTX -->
                                    <select disabled class="blast-select not-for-n not-for-p not-for-tn" name="query_genetic_code">
                                        <option value="1" selected>Standard</option>
                                        <option value="2">Vertebrate Mitochondrial</option>
                                        <option value="5">Invertebrate Mitochondrial</option>
                                        <option value="6">Ciliate Nuclear</option>
                                        <option value="9">Echinoderm Mitochondrial</option>
                                        <option value="10">Euplotid Nuclear</option>
                                        <option value="11">Bacterial</option>
                                        <option value="12">Alternative Yeast Nuclear</option>
                                        <option value="14">Flatworm Mitochondrial</option>
                                        <option value="15">Blepharisma Macronuclear</option>
                                    </select>
                                    <label>Query genetic code</label>
                                </div>

                                <div class="input-field col s6"> <!-- ONLY FOR TBLAST* -->
                                    <select disabled class="blast-select not-for-n not-for-p not-for-x" name="db_gen_code">
                                        <option value="1" selected>Standard</option>
                                        <option value="2">Vertebrate Mitochondrial</option>
                                        <option value="5">Invertebrate Mitochondrial</option>
                                        <option value="6">Ciliate Nuclear</option>
                                        <option value="9">Echinoderm Mitochondrial</option>
                                        <option value="10">Euplotid Nuclear</option>
                                        <option value="11">Bacterial</option>
                                        <option value="12">Alternative Yeast Nuclear</option>
                                        <option value="14">Flatworm Mitochondrial</option>
                                        <option value="15">Blepharisma Macronuclear</option>
                                    </select>
                                    <label>Database genetic code</label>
                                </div>

                                <div class="input-field col s12">
                                    <select name="outfmt">
                                        <option value="0" selected>Pairwise</option>
                                        <option value="1">Query-anchored (show identities)</option>
                                        <option value="2">Query-anchored (no identities)</option>
                                        <option value="3">Flat query-anchored (show identities)</option>
                                        <option value="4">Flat query-anchored (no identities)</option>
                                        <option value="5">XML</option>
                                        <option value="6">Tabular</option>
                                    </select>
                                    <label>Alignement view</label>
                                </div>
                            
                                <div class="clearb"></div>
                                
                            </div>
                        </li>
                    </ul>

                    <div class="clearb"></div>
                    <div class="divider divider-margin"></div>

                    <button type="submit" class="btn-flat right green-text">Launch BLAST</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function() { $('.collapsible').collapsible(); initRadioBlast(); });
    </script>
    <?php
}
