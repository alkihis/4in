<?php

function aboutControl(array $a) : Controller {
    $data = [];

    global $sql;

    // Remplit les espèces et indique leur description et image liée
    // les images doivent se trouver sous /img/species/*****
    // ***** sont les caractères à remplir sous la clé img
    // null si pas d'image liée
    $data['species'] = [
        'Apisum' => ['img' => 'apisum.jpg', 'dscr' => 'Acyrthosiphon pisum'],
        'Aaegypti' => ['img' => 'aegypti.jpg', 'dscr' => 'Aedes aegypti'],
        'Amellifera' => ['img' => 'melli.jpg', 'dscr' => 'Apis mellifera'],
        'Agambiae' => ['img' => 'myggestik.jpeg', 'dscr' => 'Anopheles gambiae'],
        'Gmorsitans' => ['img' => 'Glossina.jpg', 'dscr' => 'Glossina morsitans'],
        'Msexta' => ['img' => 'man.jpg', 'dscr' => 'Manduca sexta'],
        'Nvitripennis' => ['img' => 'Nasoniavit.jpg', 'dscr' => 'Nasonia vitripennis'],
        'Phumanus' => ['img' => 'ped.jpg', 'dscr' => 'Pediculus humanus'],
        'Soryzae' => ['img' => 'Maize_weevil.jpg', 'dscr' => 'Sitophilus oryzae'],
        'Sinvicta' => ['img' => 'sinvi.jpg', 'dscr' => 'Solenopsis invicta'],
        'Bmori' => ['img' => 'bmori.JPG', 'dscr' => 'Bombyx mori'],
        'Cfloridanus' => ['img' => 'clo.jpg', 'dscr' => 'Camponotus floridanus'],
        'Dponderosae' => ['img' => 'dren.jpg', 'dscr' => 'Dendroctonus ponderosae'],
        'Dmelanogaster' => ['img' => 'droso.jpg', 'dscr' => 'Drosophila melanogaster'],
        'Pxylostella' => ['img' => 'plut.jpg', 'dscr' => 'Plutella xylostella'],
        'Tcastaneum' => ['img' => 'tribo.jpg', 'dscr' => 'Tribolium castaneum']
    ];

    return new Controller($data, 'About database');
}

function aboutView(Controller $c) : void { 
    $data = $c->getData();
    ?>
    <div class="linear-nav-to-white top-float"></div>

    <div class="container">
        <h2 class="white-text light-text">About database</h2>

        <div class="row">
            <div class="col s12">
            <h4 class="light-text">Team</h4>
                <div class="col s12 l6">
                    <a href="http://bf2i.insa-lyon.fr/">
                        <div class="card-panel linkable-card grey lighten-5 card-border">
                            <div class="row valign-wrapper no-margin-bottom">
                                <div class="col s2">
                                    <img alt="BF2i logo" src="/img/bf2i.jpg" class="circle responsive-img">
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
                                    <img alt="UCBL logo" src="/img/ucbl.png" class="circle responsive-img">
                                </div>
                                <div class="col s10">
                                    <span class="black-text">
                                        Designers and conceptors of this website are in Lyon 1 Claude Bernard University.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class="col s12">
                <h4 class="light-text">Species</h4>

                <?php $i = 0; 
                
                foreach ($data['species'] as $key => $s) { 
                    if ($i === 0) { echo '<div class="row no-margin-bottom">'; }
                    ?>

                    <div class="col s12 l4">
                        <div class='card card-border'>
                        <?php if ($s['img']) { ?>
                            <div class="card-image">
                                <img alt="Specie <?= $key ?> image" src="/img/species/<?= $s['img'] ?>">
                                <span class="card-title"><?= $key ?></span>
                            </div>
                        <?php } ?>
                        <div class="card-content light-text">
                            <p><?= htmlspecialchars($s['dscr']) ?></p>
                        </div>
                        </div>
                    </div>

                    <?php $i++;
                    if ($i === 3) {
                        echo '</div>';
                    }
                    if ($i >= 3) { $i = 0; }
                } ?>

                <?= ($i !== 0 ? '</div>' : '') ?>
            </div>
        </div>
    </div>
    
    <?php
}
