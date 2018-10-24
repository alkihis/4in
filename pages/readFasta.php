<?php

function readFastaControl($args) : Controller {
    // Dans le contrôleur, on exploite le GET ou le POST

    if (isset($_GET['refresh'])) {
        global $sql;
        
        // Construction séquences dans la BDD SQL
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_adn=NULL;");
        mysqli_query($sql, "UPDATE GeneAssociations SET sequence_pro=NULL;");

        $adn = glob('fasta/adn/*.fasta');
        $pro = glob('fasta/pro/*.fasta');
    
        foreach($adn as $a) {
            loadFasta($a, 'adn');
        }
        foreach($pro as $a) {
            loadFasta($a, 'pro');
        }

        // Toutes les séquences ont été chargées, on construit la base BLAST
        // Effacement des anciennes
        $base = glob('ncbi/bin/base/adn_base.*');
        foreach ($base as $file) {
            unlink($file);
        }

        $base = glob($_SERVER['DOCUMENT_ROOT'] . '/ncbi/bin/base/pro_base.*');
        foreach ($base as $file) {
            unlink($file);
        }

        // Construction des 4 bases :
        // ADN sans autorisation et complète (génomes protégés), de même protéine
        makeBlastDB('adn', true);
        makeBlastDB('adn', false);
        makeBlastDB('pro', true);
        makeBlastDB('pro', false);
    }
    

    // On donne les données au contrôleur
    return new Controller([], 'Read fasta');
}

function readFastaView(Controller $c) : void {

}
