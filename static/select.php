<?php

?>

<div class="container"> 
    <p class="light">Vous pouvez rechercher vos gènes d'intérêts selon vos critères.</p>
    <div class="row"> 
        <div class="input-field col s6">
            <input placeholder="Coagulation, phagocyte, etc..." id="name" type="text" class="validate">
            <label for="name">Nom du gène</label>
        </div>
        <div class="input-field col s12">
            <select multiple>
            <option value="" disabled>Choisir votre espèce</option>
            <option value="1">SORY</option>
            <option value="2">ACYPI</option>
            <option value="3">AGAP</option>
            <option value="1">AAEL</option>
            <option value="2">SINV</option>
            <option value="3">PHUM</option>
            </select>
            <label>Espèces d'insecte</label>
        </div>
    </div>

    <div class="input-field col s12">
        <select multiple>
        <option value="" disabled>Choisir votre voie</option>
        <option value="1">TOLL</option>
        <option value="2">Apoptose</option>
        <option value="3">Autophagie</option>
        <option value="1">IMD</option>
        <option value="2">Cellular response</option>
        <option value="3">Serine proteases</option>
        </select>
        <label>Voies de l'immunité</label>
    </div>
        
    <form action="#">
        <div class="file-field input-field">
            <div class="btn">
                <span>Fichier</span>
                <input type="file" multiple>
            </div>

            <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Télécharger une ou plusieurs séquences">
            </div>
        </div>
    </form>

    <div class="input-field col s12">
            <input placeholder="SORY0000069, ACYPI064, etc... id="ID" type="text" class="validate">
            <label for="ID">ID du gène</label>
    </div>
    
</div>
  


