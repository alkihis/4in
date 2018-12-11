<?php

function statsController() : array {
    $data = ['active_page' => 'stats'];

    // la page de stats répertorie :
    /* 
    - Genes chargés
    - Gene ID chargés
    - Nb moyen de gene ID par gene
    - Nombre de genes avec séquences ADN / PRO
    - Nombre de genes avec alias
    - Nombre de gènes avec lien non validé
    - Nombre de gènes avec lien en échec
    - Nombre de gènes présentant des informations additionnelles
    */

    global $sql;

    $q = mysqli_query($sql, "SELECT COUNT(*) c FROM Gene");
    $data['genes'] = 0;
    while ($row = mysqli_fetch_assoc($q)) {
        $data['genes'] = $row['c'];
    }

    $q = mysqli_query($sql, "SELECT COUNT(*) c FROM GeneAssociations");
    $data['ids'] = 0;
    while ($row = mysqli_fetch_assoc($q)) {
        $data['ids'] = $row['c'];
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE sequence_adn IS NOT NULL OR sequence_pro IS NOT NULL");
    $data['seqs'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['seqs'][] = $row['gene_id'];
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE sequence_adn IS NULL AND sequence_pro IS NULL");
    $data['noseqs'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['noseqs'][] = $row['gene_id'];
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE alias IS NOT NULL");
    $data['alias'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['alias'][] = $row['gene_id'];
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE linkable IS NULL");
    $data['link_invalid'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['link_invalid'][] = $row['gene_id'];
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE linkable=0");
    $data['link_fail'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['link_fail'][] = $row['gene_id'];
    }

    $q = mysqli_query($sql, "SELECT gene_id FROM GeneAssociations WHERE addi IS NOT NULL");
    $data['addi'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['addi'][] = $row['gene_id'];
    }

    $q = mysqli_query($sql, "SELECT DISTINCT specie FROM GeneAssociations");
    $data['species'] = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data['species'][] = $row['specie'];
    }

    return $data;
}

function statsView(array $data) : void { ?>
    <div class="hide" id="seqs"><?= implode(',', $data['seqs']) ?></div>
    <div class="hide" id="noseqs"><?= implode(',', $data['noseqs']) ?></div>
    <div class="hide" id="alias"><?= implode(',', $data['alias']) ?></div>
    <div class="hide" id="link_invalid"><?= implode(',', $data['link_invalid']) ?></div>
    <div class="hide" id="link_fail"><?= implode(',', $data['link_fail']) ?></div>
    <div class="hide" id="addi"><?= implode(',', $data['addi']) ?></div>
    <div class="hide" id="species" data-not-link="1"><?= implode(',', $data['species']) ?></div>

    <div class="row">
        <div class="col s12">
            <div class="card-panel light-blue darken-1 card-border white-text panel-settings">
                <p>
                    You can find statistics about the currently built database here.
                </p>
            </div>
        </div>
    </div>

    <table class="striped">
        <thead>
            <tr>
                <th>Component</th>
                <th>Count</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Loaded genes</td>
                <td><?= $data['genes'] ?></td>
            </tr>
            <tr>
                <td>Loaded unique genes</td>
                <td><?= $data['ids'] ?></td>
            </tr>
            <tr>
                <td>Average homologous per gene</td>
                <td><?= round($data['ids'] /$data['genes']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="species">Loaded species</td>
                <td><?= count($data['species']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="seqs">Loaded genes with sequences</td>
                <td><?= count($data['seqs']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="noseqs">Loaded genes without sequences</td>
                <td><?= count($data['noseqs']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="alias">Loaded genes with alias</td>
                <td><?= count($data['alias']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="link_invalid">Loaded genes with unverified link</td>
                <td><?= count($data['link_invalid']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="link_fail">Loaded genes with invalid link</td>
                <td><?= count($data['link_fail']) ?></td>
            </tr>
            <tr>
                <td class="stats-link" data-info="addi">Loaded genes with additionnal informations</td>
                <td><?= count($data['addi']) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row">
        <div class="clearb" style="margin-bottom: 20px"></div>
    </div>

    <script>
        $(function() {
            var mod = document.getElementById('modal-admin');
            $(mod).modal();

            $('.stats-link').on('click', function () {
                var str = '<div class="modal-content">\
                    <h4>Matched elements</h4>\
                    <div id="mod-content"><div class="center-block center">' + preloader_circle + '</div></div>\
                </div>\
                <div class="modal-footer">\
                    <a href="#!" class="modal-close btn-perso red-text btn-flat">Close</a>\
                </div>';

                mod.innerHTML = str;

                $(mod).modal('open');

                var timeout = 1;

                var content = document.getElementById(this.dataset.info);
                var genes = content.innerText.split(',');
                if (genes.length > 500) {
                    timeout = 150;
                }

                var not_link = (content.dataset.notLink || false);

                setTimeout(function() {
                    str = "";
                    var first = true;
                    for (var i = 0; i < genes.length; i++) {
                        if (first) first = false;
                        else str += ", ";

                        if (!not_link) {
                            str += '<a target="_blank" href="/gene/'+genes[i]+'">'+genes[i]+'</a>';
                        }
                        else {
                            str += genes[i];
                        }
                    }

                    document.getElementById('mod-content').innerHTML = str;
                }, timeout);
            });
        });
    </script>
    <?php
}
