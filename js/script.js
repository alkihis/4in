$(document).ready(function () {
    $('.parallax').parallax();

    $('.dropdown-trigger').dropdown({
        coverTrigger: false,
        hover: true,
        outDuration: 150
    });

    $('select').formSelect();

    $('.modal').modal();

    if (!localStorage.getItem('fasta_line_breaks')) {
        localStorage.setItem('fasta_line_breaks', "0");
    }
});

function isPopupOpen() {
    return document.getElementsByClassName('popup-download')[0].classList.contains('open');
}

function showPopup() {
    document.getElementsByClassName('popup-download')[0].classList.add('open');
}

function hidePopup() {
    document.getElementsByClassName('popup-download')[0].classList.remove('open');
}

function initCheckboxes() {
    var number_checked = document.getElementById('count_popup');

    $('.chk-srch').on('change', function(evt) {
        var len = $('.chk-srch:checked').length;
        number_checked.innerText = len;

        if (this.checked) {
            showPopup();
        }
        else if (len === 0) {
            hidePopup();
        }
    });
}

function checkAllPageBoxes(checked) {
    var elements = document.getElementsByClassName('chk-srch');
    var number_checked = document.getElementById('count_popup');

    if (checked) {
        for (var i = 0; i < elements.length; i++) {
            elements[i].checked = true;
        }
    }
    else {
        for (var i = 0; i < elements.length; i++) {
            elements[i].checked = false;
        }

        hidePopup();
    }

    var len = $('.chk-srch:checked').length;
    number_checked.innerText = len;
}

function downloadCheckedSequences(mode, download_all = false) {
    var e = $('.chk-srch' + (download_all ? '' : ':checked'));

    var ids = '';
    var first = true;

    e.each(function() {
        if (first) {
            first = false;
        }
        else {
            ids += ',';
        }

        ids += this.dataset.id;
    });

    let line_breaks = Number(localStorage.getItem('fasta_line_breaks'));

    gotoUrl('/api/seq/get_sequences_fasta.php', {ids: ids, mode: mode, chars_by_line: line_breaks}, 'post');
}

function gotoUrl(path, params, method) {
    //Null check
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    form.setAttribute("target", '_blank');

    //Fill the hidden form
    if (typeof params === 'string') {
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", 'data');
        hiddenField.setAttribute("value", params);
        form.appendChild(hiddenField);
    }
    else {
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", key);
                if(typeof params[key] === 'object'){
                    hiddenField.setAttribute("value", JSON.stringify(params[key]));
                }
                else{
                    hiddenField.setAttribute("value", params[key]);
                }
                form.appendChild(hiddenField);
            }
        }
    }

    document.body.appendChild(form);
    form.submit();
}

function sortTable(idTable) {
    var table = $('#' + idTable);

    $('#' + idTable + ' th.sortable')
        .wrapInner('<span title="Sort"/>')
        .each(function() {
            var th = $(this),
                thIndex = th.index(),
                inverse = false;

            th.click(function() {
                table.find('td').filter(function() {

                    return $(this).index() === thIndex;

                }).sortElements(function(a, b) {
                    if ($.text([a]).toLowerCase() == $.text([b]).toLowerCase())
                        return 0;

                    return $.text([a]).toLowerCase() > $.text([b]).toLowerCase() ?
                        inverse ? -1 : 1
                        : inverse ? 1 : -1;

                }, function() {
                    // parentNode is the element we want to move
                    return this.parentNode; 
                });
                inverse = !inverse;
            });

        });
}

function loadOrthologuesModal(element) {
    var modal = document.getElementById('modal-orthologues');

    var list_of_h = element.dataset.genes.split(',');
    var str = '';

    str = '<div class="modal-content"><h4>Homologous genes in ' + element.innerText + '</h4>';

    if (list_of_h.length) {
        str += "<h6>" + list_of_h.length + " homologous gene" + (list_of_h.length > 1 ? 's' : '') + "</h6>";

        var first = true;

        str += '<p>';
        for (var i = 0; i < list_of_h.length; i++) {
            if (first) {
                first = false;
            }
            else {
                str += ', ';
            }
            str += '<a href="/gene/' + list_of_h[i] + '" target="_blank">' + list_of_h[i] + '</a>';
        }
        str += '</p>';
    }
    else {
        str += '<p>' + element.innerText + ' has no homologous of this gene. </p>';
    }

    str += '</div>';

    modal.innerHTML = str + '<div class="modal-footer"><a href="#!" class="modal-close red-text btn-flat">Close</a></div>';

    $(modal).modal('open');
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function addSpecie(spec) {
    spec = spec.trim();

    if (spec.match(/'"<>&/) || spec === "") {
        M.toast({html: 'Entered specie name is invalid', displayLength: 6000});
        return;
    }

    var input = document.getElementById('new_specie');
    var checkboxes = document.getElementsByClassName('chk-spe');
    var species_actual = {};

    for (var i = 0; i < checkboxes.length; i++) {
        species_actual[checkboxes[i].value] = checkboxes[i].checked;
    }
    species_actual[spec] = true;

    var checkboxes_container = document.getElementById('multiple_species');

    var str = '';
    for (var key in species_actual) {
        str += "<p>\
            <label>\
                <input type='checkbox' class='chk-spe' name='species[]' \
                    "+ (species_actual[key] ? 'checked' : '') +" value='"+ key +"'>\
                <span>"+ key +"</span>\
            </label>\
            <br>\
        </p>";
    }

    checkboxes_container.innerHTML = str;
    input.value = "";
}

function refreshSelect(element) {
    var instance = M.FormSelect.getInstance(element);
    var values = instance.getSelectedValues();
    var change = false;

    // element.value ne renvoie que le premier élément.... > passage par l'outil materialize
    if (values.indexOf('all') !== -1) {
        var all_o = document.querySelector('.all_option[data-mode='+ element.dataset.mode +']');

        if (all_o.dataset.onlyOne) { // Si all_option était le seul à être coché
            values.splice(values.indexOf('all'), 1);

            element.value = values;
            all_o.dataset.onlyOne = "";
        }
        else { // Il y avait d'autres options cochées, on les enlève
            element.value = ['all'];
            all_o.dataset.onlyOne = "true";
        }

        change = true;
    }

    if (change) {
        M.FormSelect.init(element);
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

    var PAM30 = {
        "1": [8, 10, [13, 14]],
        "2": [5, 7, [14]] 
    };
    var PAM250 = {
        "1": [17, 21],
        "2": [13, 17],
        "3": [11, 15]
    };
    var BLOSUM45 = {
        "1": [16, 19],
        "2": [12, 16],
        "3": [10, 13]
    };
    var BLOSUM62 = {
        "1": [9, 13],
        "2": [6, 11] 
    };
    var BLOSUM80 = {
        "1": [9, 11],
        "2": [6, 9, [13]] 
    };
    var BLOSUM90 = {
        "1": [9, 11],
        "2": [6, 9] 
    };

    var sel = null;
    switch (mode) {
        case 'PAM30':
            sel = PAM30;
            break;
        case 'PAM250':
            sel = PAM250;
            break;
        case 'BLOSUM45':
            sel = BLOSUM45;
            break;
        case 'BLOSUM62':
            sel = BLOSUM62;
            break;
        case 'BLOSUM80':
            sel = BLOSUM80;
            break;
        default:
            sel = BLOSUM90;
    }

    var element = document.getElementById('gapvalues');
    var str = '';

    for (var key in sel) {
        for (var i = sel[key][0]; i <= sel[key][1]; i++) {
            str += "<option value='" + i + "/" + key + "' ";

            if (Number(key) === 1 && i === 11) { // Si gap 11/1 : on select (valeur par défaut basique)
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

function initNumberForm() {
    var deb = document.getElementById('subset-low');
    var fin = document.getElementById('subset-up');

    $('input[type=number]').on('change', function() {
        deb.classList.remove('valid', 'invalid');
        fin.classList.remove('valid', 'invalid');
        
        if (deb.value !== '') {
            if (deb.value < 0) {
                deb.classList.add('invalid');
            } 
            else { 
                deb.classList.add('valid');
            }

            if (fin.value !== '') {
                if (fin.value < 0 || Number(deb.value) >= fin.value) {
                    fin.classList.add('invalid');
                } 
                else { 
                    fin.classList.add('valid');
                }
            }
        }
            
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
