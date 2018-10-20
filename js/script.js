$(document).ready(function () {
    $('.parallax').parallax();

    $('.dropdown-trigger').dropdown({
        coverTrigger: false,
        hover: true,
        outDuration: 150
    });

    $('select').formSelect();
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

    gotoUrl('/api/seq/get_sequences_fasta.php', {ids: ids, mode: mode}, 'post');
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
        .each(function(){
            var th = $(this),
                thIndex = th.index(),
                inverse = false;

            th.click(function() {
                table.find('td').filter(function(){

                    return $(this).index() === thIndex;

                }).sortElements(function(a, b){
                    if( $.text([a]).toLowerCase() == $.text([b]).toLowerCase() )
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
