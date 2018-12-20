$(document).ready(function () {
    $('.jarallax').jarallax({
        speed: 0.15,
    });

    $('.dropdown-trigger').dropdown({
        coverTrigger: false,
        hover: true,
        outDuration: 150
    });

    $('select').formSelect();

    $('.modal').modal();

    // Enregistre la possibilité de casser les lignes des fasta à faux
    // Si elle n'existe pas
    if (!localStorage.getItem('fasta_line_breaks')) {
        localStorage.setItem('fasta_line_breaks', "0");
    }
});

function htmlToElement(html) {
    var template = document.createElement('template');
    html = html.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = html;
    return template.content.firstChild;
}

function isNightModeOn() {
    return document.querySelectorAll('#dark-mode-css').length > 0;
}

function enableNightMode() {
    if (!isNightModeOn()) {
        document.querySelector('main').classList.add('on-dark-mode');

        document.head.appendChild(htmlToElement('<link type="text/css" rel="stylesheet" id="dark-mode-css" href="/css/dark.css">'));

        setTimeout(function() {
            document.querySelector('main').classList.remove('on-dark-mode');
        }, 500);
    }
}

function disableNightMode() {
    var nm = document.getElementById('dark-mode-css');

    if (nm) {
        document.querySelector('main').classList.add('on-dark-mode');

        document.head.removeChild(nm);

        setTimeout(function() {
            document.querySelector('main').classList.remove('on-dark-mode');
        }, 500);
    }
}

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

    $('.chk-srch').on('change', function() {
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

function sortTable(idTable, interval_for_view) {
    var tbod = document.getElementById('tbody_sort');

    var table = $('#' + idTable);

    $('#' + idTable + ' th.sortable')
        .each(function() {
            var title = "Sort by " + this.innerText.toLowerCase().trim();
            this.innerHTML = "<div class='sort' title='"+title+"'><i class='material-icons left sort sort-anim'>unfold_more</i>"+this.innerHTML+"<div class='clearb'></div></div>";
        })
        .each(function() {
            var th = $(this),
                thIndex = th.index(),
                inverse = false;

            th.click(function() {
                resetSegments(tbod);

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

                initSegments(tbod, interval_for_view);
            });
        });
}

function resetSegments(element) {
    var childs = element.childNodes;

    for (var i = 0; i < childs.length; i++) {
        if (childs[i].classList && childs[i].classList.contains('segment')) {
            childs[i].parentElement.removeChild(childs[i]);
            // la liste est actualisée en temps réel, il faut diminuer i
            i--;
        }
        else if (childs[i].classList && childs[i].classList.contains('segment-container')) {
            childs[i].dataset.segment = "null";
        }
    }
}

function initSegments(element, interval) {
    var childs = element.childNodes;
    var count = 0;

    for (var i = 0; i < childs.length; i++) {
        if (childs[i].classList) { // si l'enfant n'est pas du texte
            var seg = Math.floor(count / interval);

            if (count % interval === 0 && count !== 0) {
                // Si on dépasse le premier segment, on en crée un nouveau
                var tr = document.createElement('tr');
                tr.classList.add('segment');
                tr.dataset.nextSegment = seg;
    
                childs[i].parentNode.insertBefore(tr, childs[i]);
                // childs est actualisée en temps réel, donc i va maintenant pointer sur
                // le .segment ajouté, on incrémente i
                i++;
    
                childs[i].dataset.segment = seg;
                childs[i].style.display == "none";
            }
            else {
                // tr classique, on enregistre le numéro de segment
                childs[i].dataset.segment = seg;

                // Si jamais le tr est dans le premier segment, il faut le rendre visible
                if (seg === 0 && childs[i].style.display === "none") {
                    childs[i].style.display = "";
                }
                else if (seg !== 0) {
                    childs[i].style.display == "none";
                }
            }
            count++;
        }
    }

    initScrollFireSegments();
}

function loadOrthologuesModal(element) {
    var modal = document.getElementById('modal-orthologues');

    var list_of_h = element.dataset.genes.split(',');
    var str = '';

    str = '<div class="modal-content"><h4>Homologous genes in ' + element.dataset.specie + '</h4>';

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
        str += '<p>' + element.dataset.specie + ' has no homologous of this gene. </p>';
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
        M.toast({html: 'Entered species name is invalid', displayLength: 6000});
        return;
    }

    // On recupère les checkbox
    var input = document.getElementById('new_specie');
    var checkboxes = document.getElementsByClassName('chk-spe');
    var species_actual = {};

    // On construit le tableau de relation nom_espece => cochée
    for (var i = 0; i < checkboxes.length; i++) {
        species_actual[checkboxes[i].value] = checkboxes[i].checked;
    }
    // On rajoute l'actuelle
    species_actual[spec] = true;

    // On actualise le conteneur de checkbox en contruisant une nouvelle DOMString
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
    var values = $(element).val();
    var change = false;
    var right_now = false;

    // element.value ne renvoie que le premier élément.... > passage par l'outil materialize
    if (values.indexOf('all') !== -1) {
        var all_o = document.querySelector('.all_option[data-mode='+ element.dataset.mode +']');

        if (all_o.dataset.onlyOne) { // Si all_option était le seul à être coché
            values.splice(values.indexOf('all'), 1);

            element.value = values;

            all_o.dataset.onlyOne = "";

            // Tente de décocher visuellement "all", tombe sur le premier input (la checkbox) dans le premier li
            document.querySelector('.' + element.dataset.mode +' .select-dropdown li input').checked = false;
            // Tombe sur le premier input (la checkbox) dans le premier li

            // Enlève la classe "selected"
            document.querySelector('.' + element.dataset.mode +' .select-dropdown li').classList.remove('selected');
        }
        else { // Il y avait d'autres options cochées, on les enlève
            element.value = ['all'];
            all_o.dataset.onlyOne = "true";

            right_now = true;
        }

        change = true;
    }

    if (change) {
        if (right_now) {
            M.FormSelect.init(element);
        }
        else {
            // Dit au form select de s'actualiser quand on le ferme
            var $ele = $('.' + element.dataset.mode + ' .select-dropdown');
            $ele.on('close', function() {
                M.FormSelect.init(element);

                $ele.unbind('close');
            });
        }
    }
}

function initScrollFireSegments() {
    // Initialise les segments de génération de carte
    $('.segment').scrollfire({
        // Offsets
        offset: 0,
        topOffset: 0,
        bottomOffset: 0,
        // Fires once when element begins to come in from the bottom, with scroll
        onScrollDown: function(elm) {
            // console.log('triggered', elm, elm.dataset, distance_scrolled);
            var seg = Number(elm.dataset.nextSegment);
            // Affiche les éléments situés en dessous de ce segment, puis supprime le segment
            $('[data-segment=' + seg + ']').show();

            $(elm).remove();
        },
    });
}

function searchFormMake(form) {
    var instance = M.Chips.getInstance(document.getElementById('chip_container'));

    form.global.value = "";
    var str = "";

    for (var i = 0; i < instance.chipsData.length; i++) {
        if (i > 0) {
            str += " ";
        }
        // Entoure le texte de guillemets doubles ""
        str += '"' + instance.chipsData[i].tag + '"'; 
    } 

    var form_val = form.global_chip.value.trim();

    if (form_val !== "") {
        form.global.value = str + ' "' + form_val + '"';
    }
    else {
        form.global.value = str;
    }

    // Chip additionnelles
    instance = document.getElementById('chip_container_addi');
    if (instance) {
        instance = M.Chips.getInstance(instance);
        str = "";
    
        for (var i = 0; i < instance.chipsData.length; i++) {
            if (i > 0) {
                str += " ";
            }
            // Entoure le texte de guillemets doubles ""
            str += '"' + instance.chipsData[i].tag + '"'; 
        } 
    
        var form_val = form.addi_chip.value.trim();
    
        if (form_val !== "") {
            form.addi.value = str + ' "' + form_val + '"';
        }
        else {
            form.addi.value = str;
        }
    }

    return true;
}

function checkChips(insts) {
    var e = insts[0];

    for (var i = 0; i < e.chipsData.length; i++) {
        e.chipsData[i].tag = e.chipsData[i].tag.trim();

        if (e.chipsData[i].tag.length < 2) {
            e.deleteChip(i--);
            M.toast({html: "Keyword must be as long as 2 characters minimal.", displayLength: 5000});
        }
    } 
}

function initGlobalSearchForm(dat, addi) {
    var elems = document.querySelectorAll('.chips-autocomplete');

    var id_loading = "loading_block_form";

    // Affiche le message de chargement si jamais cela met plus de 800ms à se lancer
    setTimeout(function() {
        var load = document.getElementById(id_loading);

        if (load) {
            $(load).slideDown(200);
        }
    }, 500);

    $.get(
        "/api/search/get_all.json", 
        { } 
    ).then(function (json) {
        var instance = M.Chips.init(elems, {
            data: dat,
            autocompleteOptions: {
                data: json,
                limit: 5,
                minLength: 1
            },
            onChipAdd: function() { checkChips(instance) }
        });
    }).catch(function () {
        var instance = M.Chips.init(elems, {
            data: dat,
            onChipAdd: function() { checkChips(instance) }
        });
    }).always(function() {
        $('#' + id_loading).slideUp(150, function() { $(this).remove() });
    });

    // Chip additionnelles
    var e = document.querySelectorAll('.chips.addi');
    if (e) {
        var ist = M.Chips.init(e, {
            data: addi,
            onChipAdd: function() { checkChips(ist) }
        });
    }

    $('#submit_form').on('submit', function() {
        return searchFormMake(this);
    });
}
