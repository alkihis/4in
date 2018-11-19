var BLAST_ERROR_MSG = "<h4 class='red-text'>An error occurred. Check request informations.</h4>";
var PRELOADER = '<div class="center"><div class="preloader-wrapper big active">\
<div class="spinner-layer spinner-blue-only">\
  <div class="circle-clipper left">\
    <div class="circle"></div>\
  </div><div class="gap-patch">\
    <div class="circle"></div>\
  </div><div class="circle-clipper right">\
    <div class="circle"></div>\
  </div>\
</div>\
</div></div>';

function closeBlastForm() {
    $('#blue_gradient').slideUp(300);
    $('#blast_form').slideUp(300, function() { $('#back_to_form').slideDown(300); });
}

function openBlastForm() {
    $('#back_to_form').slideUp(200);
    $('#blast_form').slideDown(350);
    $('#blue_gradient').slideDown(300);
}

function makeBlastError(str) {
    return '<div class="divider-margin divider"></div><h5 class="red-text">' + escapeHtml(str) + "</h5>";
}

$(function() {
    $('.collapsible').collapsible(); 
    initRadioBlast(); 
    refreshBlastForm(document.querySelector('[name=program]:checked').value); 
    refreshBlastGapMatrix(document.getElementById('matrix').value);

    function sendData() {
        var xhr = new XMLHttpRequest();
    
        // Liez l'objet FormData et l'élément form
        var FD = new FormData(form);
    
        // Définissez ce qui se passe si la soumission s'est opérée avec succès
        xhr.addEventListener("load", function(event) {
            var json_text = event.target.responseText;

            try {
                // Tente d'obtenir les données téléchargées
                var json = JSON.parse(json_text);

                if (Number(json.error) == 0) {
                    placeholder_send.innerHTML = '<div class="divider-margin divider"></div>' + json.html;
                }
                else {
                    switch (Number(json.error)) {
                        case 1:
                            M.toast({html: "BLAST is not available", displayLength: 8000});
                            placeholder_send.innerHTML = makeBlastError("BLAST is not currently available. Please try again later.");
                            break;
                        case 2:
                            M.toast({html: "Query is empty", displayLength: 8000});
                            placeholder_send.innerHTML = saved_blast;
                            break;
                        case 3:
                            M.toast({html: "Please wait before a new request", displayLength: 8000});
                            placeholder_send.innerHTML = saved_blast;
                            break;
                        default:
                            placeholder_send.innerHTML = makeBlastError("An unknown error occurred");

                    }

                    throw "Error";
                }
            } catch (e) {
                openBlastForm();
            }
        });
    
        // Definissez ce qui se passe en cas d'erreur
        xhr.addEventListener("error", function(event) {
            placeholder_send.innerHTML = makeBlastError("An unknown error occurred");
            openBlastForm();
        });
    
        // Configurez la requête
        xhr.open("POST", "/api/blast/make_search.json", true);
    
        // Les données envoyées sont ce que l'utilisateur a mis dans le formulaire
        xhr.send(FD);
    }
     
    // Accédez à l'élément form …
    var form = document.getElementById("blast_form");
    var placeholder_send = document.getElementById('placeholder_blast');
    var saved_blast = "";

    // … et prenez en charge l'événement submit.
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (checkBlastForm()) {
            saved_blast = placeholder_send.innerHTML;
            placeholder_send.innerHTML = '<div class="divider-margin divider"></div>' + PRELOADER;

            closeBlastForm();
            
            // Attend que le formulaire soit caché   
            setTimeout(function() {
                sendData();
            }, 500);
        }
    });
});

function refreshBlastForm(mode) {
    var select_megablast = `<option value="16">16</option>
    <option value="20">20</option>
    <option value="24">24</option>
    <option value="28" selected>28</option>
    <option value="32">32</option>
    <option value="48">48</option>`;

    var select_blastn = `<option value="7">7</option>
    <option value="11" selected>11</option>
    <option value="15">15</option>`;

    var select_blastx = `<option value="2">2</option>
    <option value="3" selected>3</option>
    <option value="6">6</option>`;

    var $b = $('.blast-select');

    try { $b.formSelect('destroy'); } catch (e) {}

    $b.prop('disabled', false);

    var select = document.getElementById('word_size');

    if (mode === "n") {
        $('.blast-select.not-for-n').prop('disabled', true);
        select.innerHTML = select_blastn;
    }
    else if (mode === "p") {
        $('.blast-select.not-for-p').prop('disabled', true);
        select.innerHTML = select_blastx;
    }
    else if (mode === "x") {
        $('.blast-select.not-for-x').prop('disabled', true);
        select.innerHTML = select_blastx;
    }
    else if (mode === "tn") {
        $('.blast-select.not-for-tn').prop('disabled', true);
        select.innerHTML = select_blastx;
    }
    else if (mode === "tx") {
        $('.blast-select.not-for-tx').prop('disabled', true);
        select.innerHTML = select_blastx;
    }
    else if (mode === "meg") {
        $('.blast-select.not-for-n').prop('disabled', true);
        $('.blast-select.not-for-meg').prop('disabled', true);
        select.innerHTML = select_megablast;
    }

    // Actualisation de la checkbox low complexity si megablast
    document.getElementById('low_complex').checked = mode === 'meg' || mode === 'n';

    $('.blast-message:not(.show-for-' + mode + ')').slideUp(200);
    $messages = $('.blast-message.show-for-' + mode);
    if (! $messages.is(":visible")) {
        $messages.slideDown(200);
    }

    $b.formSelect();
}

function initRadioBlast() {
    $('.radio-blast').on('click', function() {
        refreshBlastForm(this.value);
    });
}

function checkBlastForm() {
    var q = document.getElementById('query'), f = document.getElementById('query_file');

    if (q.value.length > 0 || f.files.length > 0) {
        return true;
    }
    else {
        M.toast({html: "You must precise either a query string or a query file.", displayLength: 5000});
        return false;
    }
}

function refreshBlastGapMatrix(mode) {

    // COMPOSITION D'UN TABLEAU
    /* 
    MATRIX_NAME = {
        gap_extend_penality_one: [
            start_of_gap_open_range(for this gap_extend_penality), 
            end_of_gap_open_range,
            [abberant value of this gap_extend_penality, another abberant value of this gap_extend_penality]
        ],
        ...
    }
    */

    var sel = null;
    switch (mode) {
        case 'PAM30':
            sel = {
                "1": [8, 10, [13, 14]],
                "2": [5, 7, [14]],
                "default": [9, 1]  
            };
            break;
        case 'PAM70':
            sel = {
                "1": [9, 11],
                "2": [6, 8, [11]],
                "default": [10, 1]  
            };
            break;
        case 'PAM250':
            sel = {
                "1": [17, 21],
                "2": [13, 17],
                "3": [11, 15],
                "default": [14, 2] 
            };
            break;
        case 'BLOSUM45':
            sel = {
                "1": [16, 19],
                "2": [12, 16],
                "3": [10, 13],
                "default": [15, 2] 
            };
            break;
        case 'BLOSUM62':
            sel = {
                "1": [9, 13],
                "2": [6, 11],
                "default": [11, 1] 
            };
            break;
        case 'BLOSUM80':
            sel = {
                "1": [9, 11],
                "2": [6, 9, [13]],
                "default": [10, 1]  
            };
            break;
        default:
            sel = {
                "1": [9, 11],
                "2": [6, 9],
                "default": [10, 1] 
            };
    }

    var element = document.getElementById('gapvalues');
    var str = '';

    for (var key in sel) {
        if (key === "default") continue;

        for (var i = sel[key][0]; i <= sel[key][1]; i++) {
            str += "<option value='" + i + "/" + key + "' ";

            if (Number(key) === sel.default[1] && i === sel.default[0]) { // Si gap est la valeur par défaut : on séléectionne
                str += 'selected';
            }
            
            str += ">Existence: " + i + " / Extension: " + key + "</option>";
        }

        if (sel[key].length > 2) { 
            // Si on a défini des valeurs "aberrantes" (le tableau sel[key] a 3 cases)
            for (var i = 0; i < sel[key][2].length; i++) {
                str += "<option value='" + sel[key][2][i] + "/" + key + "'>Existence: " + 
                    sel[key][2][i] + " / Extension: " + key + "</option>";
            }
        }
    }

    element.innerHTML = str;

    // Actualisation du select
    try { $(element).formSelect('destroy'); } catch (e) {}
    $(element).formSelect();
}