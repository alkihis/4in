var base_pathway = document.getElementById('base_path').innerHTML;

$(function () {
    $('.s-pathway').each(function() {
        this.innerHTML = base_pathway;
    });

    $('select.s-pathway').formSelect();

    $('.modal').modal();
});

function removePathway(e) {
    $(e).slideUp(200, function() {
        $(e).remove();
    });
}

function addPathway() {
    var str = '<div class="s-wrapper" style="display: none">\
        <div class="input-field col s11">\
            <select class="s-pathway" onchange="detectChange(this)" name="pathway[]">' + base_pathway + '\
            </select>\
            <label>Pathway</label>\
        </div>\
        <a href="#!" class="col s1" onclick="removePathway(this.parentElement)" style="margin-top: 20px;">\
            <i class="material-icons red-text right-align">delete_forever</i>\
        </a>\
        <div class="clearb"></div>\
    </div>';

    document.getElementById('s-container').insertAdjacentHTML('beforeend', str);

    $('select.s-pathway').formSelect();

    $('.s-wrapper').slideDown(200);
}

function detectChange(e) {
    if (e.value === "") {
        insertOption(e);
    }
}

function insertOption(e) {
    var modal = document.getElementById('modal_modif');

    modal.innerHTML = '<div class="modal-content">\
        <h4>New pathway</h4>\
        <p>Write your new pathway. Please check first if the pathway didn\'t already exists.<br>Pathways are case-sensitive.</p>\
        <div class="input-field col s12"><input type="text" id="new_p"><label for="new_p">New pathway</label></div>\
        <div class="clearb"></div>\
    </div>\
    <div class="modal-footer">\
        <a href="#!" class="modal-close btn-perso red-text btn-flat">Cancel</a>\
        <a href="#!" id="valid_insert" class="btn-perso green-text btn-flat">Insert</a>\
    </div>';

    var input = document.getElementById('new_p');
    document.getElementById('valid_insert').onclick = function() {
        if (input.value.trim() !== "") {
            var new_pa = escapeHtml(input.value.trim());
            $(e).val('');

            $('<option value="' + new_pa + '" selected>'+new_pa+'</option>').insertBefore($(e).find('[value=""]'))

            $(e).formSelect();

            $(modal).modal('close');
        }
        else {
            M.toast({html: "Pathway is empty.", displayLength: 5000});
        }
    };

    $(modal).modal('open');
}

function deleteGene() {
    var modal = document.getElementById('modal_modif');

    modal.innerHTML = '<div class="modal-content">\
        <h4>Delete gene ?</h4>\
        <p>Are you sure you want to delete gene ? Homologous won\'t be affected.<br>Affiliated gene will be deleted if\
        any homologous remains attached to it.</p>\
    </div>\
    <div class="modal-footer">\
        <a href="#!" class="modal-close btn-perso green-text left btn-flat">Cancel</a>\
        <form method="post" action="#">\
            <input type="hidden" name="delete" value="true">\
            <button type="submit" class="btn-perso right red-text btn-flat">Delete</button>\
        </form>\
    </div>';

    $(modal).modal('open');
}
