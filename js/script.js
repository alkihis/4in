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

function refreshBlastForm(mode) {
    $b = $('.blast-select');
    $b.formSelect('destroy');
    $b.prop('disabled', false);

    if (mode === "n") {
        $('.blast-select.not-for-n').prop('disabled', true);
    }
    else if (mode === "p") {
        $('.blast-select.not-for-p').prop('disabled', true);
    }
    else if (mode === "x") {
        $('.blast-select.not-for-x').prop('disabled', true);
    }
    else if (mode === "tn") {
        $('.blast-select.not-for-tn').prop('disabled', true);
    }
    else if (mode === "tx") {
        $('.blast-select.not-for-tx').prop('disabled', true);
    }

    $b.formSelect();
}

function initRadioBlast() {
    $('.radio-blast').on('click', function() {
        refreshBlastForm(this.value);
    });
}
