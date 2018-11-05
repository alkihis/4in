<?php

function searchBlastControl(array $args) : Controller {
    return new Controller([], "BLAST Search");
}

function searchBlastView(Controller $c) : void { ?>
    <div class="linear-nav-to-white top-float"></div>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <form method="post" action="/blast" enctype="multipart/form-data" onsubmit="return checkBlastForm()">
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

                                    <div class="col s4">
                                        <label>
                                            <input name="program" value="meg" class="radio-blast" type="radio">
                                            <span>megablastn</span>
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
                                    <textarea id="query" name="query" class="materialize-textarea" 
                                    style="resize: vertical;"></textarea>
                                    <label for="query">Query sequence</label>
                                </div>

                                <div class="col s12">
                                    <span>or from file</span>
                                </div>

                                <div class="file-field input-field col s12">
                                    <div class="btn light-blue darken-1">
                                        <span>FASTA input</span>
                                        <input type="file" id="query_file" name="fasta_file">
                                    </div>
                                    <div class="file-path-wrapper">
                                        <input class="file-path validate" type="text">
                                    </div>
                                </div>

                                <div class="clearb"></div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">build</i>General</div>
                            <div class="collapsible-body">
                                <div class="input-field col s6">
                                    <select name="num_descriptions">
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500" selected>500</option>
                                        <option value="2000">2000</option>
                                        <option value="5000">5000</option>
                                    </select>
                                    <label>Max descriptions</label>
                                </div>

                                <div class="input-field col s6">
                                    <select name="num_alignments">
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                        <option value="2500">2500</option>
                                    </select>
                                    <label>Max alignements</label>
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

                                <div class="input-field col s6">
                                    <select class="blast-select" name="word_size" id="word_size">
                                        
                                    </select>
                                    <label>Word size</label>
                                </div>
                            
                                <div class="clearb"></div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">exposure_plus_2</i>Scoring</div>
                            <div class="collapsible-body">
                                <div class="blast-message red-text show-for-n show-for-meg" style='margin-bottom: 15px;'>
                                    Scoring parameters are disabled for BLASTn and megaBLAST. Default parameters
                                    will be used instead.
                                </div>

                                <div class="blast-message red-text show-for-tx" style='margin-bottom: 15px; display: none;'>
                                    tBLASTx only supports ungapped searches.
                                </div>

                                <div class="input-field col s6"> <!-- NOT FOR BLASTN -->
                                    <select disabled class="blast-select not-for-n" name="matrix" id="matrix"
                                        onchange="refreshBlastGapMatrix(this.value)">
                                        <option value="PAM30">PAM30</option>
                                        <option value="PAM70">PAM70</option>
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

                                <div class="input-field col s12"> <!-- NOT FOR TBLASTX, MEGABLAST AND BLASTN -->
                                    <select class="blast-select not-for-tx not-for-n not-for-meg" name="gapvalues" id="gapvalues">
                                        
                                    </select>
                                    <label>Gap penalities</label>
                                </div>
                            
                                <div class="clearb"></div>
                            </div>
                        </li>
                        <li>
                            <div class="collapsible-header"><i class="material-icons">filter_b_and_w</i>Filters</div>
                            <div class="collapsible-body">
                                <p>
                                    <label>
                                        <input type="checkbox" id="low_complex" 
                                            name="filter_low_complexity" data-tip="seg(p) or dust(n)">
                                        <span>Filter low complexity regions</span>
                                    </label>
                                </p>

                                <p>
                                    <label>
                                        <input type="checkbox" name="soft_masking" id="soft_masking" checked>
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
                                    </select>
                                    <label>Alignement view</label>
                                </div>
                            
                                <div class="clearb"></div>
                                
                            </div>
                        </li>
                    </ul>

                    <div class="clearb"></div>
                    <div class="divider divider-margin"></div>

                    <button type="submit" class="btn-flat btn-perso right green-text">Launch BLAST</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(function() { 
            $('.collapsible').collapsible(); 
            initRadioBlast(); 
            refreshBlastForm(document.querySelector('[name=program]:checked').value); 
            refreshBlastGapMatrix(document.getElementById('matrix').value);
        });
    </script>
    <?php
}
