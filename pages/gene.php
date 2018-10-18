<?php

// Page de gène : informations et liens

/**
 * geneControl
 * Contrôleur de la page de gène
 * 
 * Doit, en fonction de l'ID de gène passé dans $args[0],
 * récupérer ses informations: famille, nom, espèce, gènes orthologues chez les autres espèces,
 * et son lien vers ArthroCyc-truc si possible. Doit également récupérer sa séquence.
 *
 * @param array $args
 * @return Controller
 */
function geneControl(array $args) : Controller {
    return new NotImplementedException();
}

/**
 * geneView
 * Vue de la page de gène
 * 
 * Doit afficher correctement, en HTML, à l'écran, les informations contenues
 * dans le contrôleur, récupérées dans geneControl
 *
 * @param Controller $c
 * @return void
 */
function geneView(Controller $c) : void {
    // TODO
}
