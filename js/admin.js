window.request = obj => {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
        xhr.open(obj.method || "GET", obj.url);
        if (obj.headers) {
            Object.keys(obj.headers).forEach(key => {
                xhr.setRequestHeader(key, obj.headers[key]);
            });
        }

        if (obj.method && obj.method === 'POST') {
            //Send the proper header information along with the request
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(xhr.response);
            } else {
                reject(xhr.statusText);
            }
        };
        xhr.onerror = () => reject(xhr.statusText);
        xhr.send(obj.body);
    });
};

$(document).ready(function () {
    $('.sidenav').sidenav();
    $('.modal:not(.not-dismissible)').modal();
});

var preloader_bar = `<div class="progress">
    <div class="determinate" id="progress-bar" style="width: 0%"></div>
</div>`;

var preloader_circle = `<div class="preloader-wrapper active">
    <div class="spinner-layer spinner-blue-only">
        <div class="circle-clipper left">
            <div class="circle"></div>
        </div>
        <div class="gap-patch">
            <div class="circle"></div>
        </div>
        <div class="circle-clipper right">
            <div class="circle"></div>
        </div>
    </div>
</div>`;

async function launchFastaBuild(files) {
    var total = files.adn.length + files.pro.length;
    var success = 0;
    var current = 1;

    var modal = document.getElementById('modal-admin');

    // Obligatoire pour conserver les attributs
    $(modal).modal({
        dismissible: false
    });
    var inst = M.Modal.getInstance(modal);
    inst.open();

    modal.innerHTML = `<div class="modal-content">
        <h4>Importing sequences</h4>
        <p>
            Reading file <span id="file_number">1</span> of ${total}
        </p>
        <div>${preloader_bar}</div>
        <div class="row no-margin-bottom">
            <p>This may take a while.</p>
        </div>
    </div>
    <div class="modal-footer">
        
    </div>`;

    var bar = document.getElementById('progress-bar');
    var num = document.getElementById('file_number');

    for (var mode in files) {
        for (var f of files[mode]) {
            await request({
                url: '/api/tools/fasta_reader.php',
                method: 'POST',
                body: 'file=' +  encodeURIComponent(f) + '&mode=' + encodeURIComponent(mode)
            }).then(function (e) {
                success++;
            }).catch(function (e) {
    
            }).finally(function (e) {
                current++;
                // Actualiser la barre...
                bar.style.width = String(Math.round((current / total) * 100)) + '%';
    
                num.innerText = current;
            });
        }
    }

    launchMakeBlast(success, total);
}

async function launchMakeBlast(success, total) {
    var modal = document.getElementById('modal-admin');
    
    modal.innerHTML = `<div class="modal-content">
        <h4>Making BLAST database</h4>
        <p>
            This may take a while.
        </p>
        <div class="center-align" style="margin-top: 30px;">
            ${preloader_circle}
        </div>
    </div>
    <div class="modal-footer">
        
    </div>`;

    var ok = true;

    await request({
        url: '/api/tools/blast_creator.php',
        method: 'POST',
        body: 'make=true'
    }).catch(function (e) { ok = false; });

    // Affiche un message signalant la fin
    modal.innerHTML = `<div class="modal-content">
        <h4>Import complete</h4>
        <p>
            <span id="file_number">${success}</span> files has been successfully imported (${(total - success)} failed).<br>
            ${ok ? "BLAST database has been successfully builded" : 
                "<span class='red-text'>An error occurred while creating BLAST database.</span>"}.
        </p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat">Close</a>
    </div>`;
}

async function launchDatabaseBuild(file, species) {
    var modal = document.getElementById('modal-admin');

    // Obligatoire pour conserver les attributs
    $(modal).modal({
        dismissible: false
    });
    var inst = M.Modal.getInstance(modal);
    inst.open();

    modal.innerHTML = `<div class="modal-content">
        <h4>Loading sequences</h4>
        <p>
            Importing database file, please wait.<br>
            This may take a while.
        </p>
        <div class="center-align" style="margin-top: 30px;">
            ${preloader_circle}
        </div>
    </div>
    <div class="modal-footer">
        
    </div>`;

    await request({
        url: '/api/tools/database_creator.php',
        method: 'POST',
        body: 'file=' +  encodeURIComponent(file) + '&empty=true&species=' + encodeURIComponent(species.join(','))
    }).catch(function (e) {

    });

    // Affiche un message signalant la fin
    modal.innerHTML = `<div class="modal-content">
        <h4>Import complete</h4>
        <p>
            Database has been successfully builded.
        </p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat">Close</a>
    </div>`;
}

async function launchMapBuild(files) {
    var total = files.length;
    var success = 0;
    var current = 1;

    var modal = document.getElementById('modal-admin');

    // Obligatoire pour conserver les attributs
    $(modal).modal({
        dismissible: false
    });
    var inst = M.Modal.getInstance(modal);
    inst.open();

    modal.innerHTML = `<div class="modal-content">
        <h4>Registering aliases</h4>
        <p>
            Reading file <span id="file_number">1</span> of ${total}
        </p>
        <div>${preloader_bar}</div>
        <div class="row no-margin-bottom">
            <p>This may take a while.</p>
        </div>
    </div>
    <div class="modal-footer">
        
    </div>`;

    var bar = document.getElementById('progress-bar');
    var num = document.getElementById('file_number');

    for (var f of files) {
        await request({
            url: '/api/tools/do_mapping.php',
            method: 'POST',
            body: 'file=' +  encodeURIComponent(f)
        }).then(function (e) {
            success++;
        }).catch(function (e) {

        }).finally(function (e) {
            current++;
            // Actualiser la barre...
            bar.style.width = String(Math.round((current / total) * 100)) + '%';

            num.innerText = current;
        });
    }

    // Affiche un message signalant la fin
    modal.innerHTML = `<div class="modal-content">
        <h4>Import complete</h4>
        <p>
            <span id="file_number">${success}</span> files has been successfully imported (${(total - success)} failed).
        </p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat">Close</a>
    </div>`;
}

function buildGenomeDbModal(file) {
    var modal = document.getElementById('modal-admin');

    var species = document.getElementById('collection-build').innerHTML;

    var str = `<div class="modal-content">
        <h4>Build genome database</h4>
        <p>
            Check if the order of species is correct. 
            If a specie is missing or is not meant to be present, 
            please enter/delete it using the "manage database species" utility.<br>
            Please be aware that if too many species are present, the build may fail.
        </p>

        <div class="modal-draggable" id="species-draggable">
            ${species}
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat btn-perso left">Close</a>
        <a href="#!" class="btn-flat orange-text btn-perso left" id="reset-spec">Reset species</a>

        <a href="#!" id="next-step-build" class="green-text btn-flat btn-perso right">Build database</a>
    </div>`;

    modal.innerHTML = str;

    document.getElementById('reset-spec').onclick = function () {
        document.getElementById('species-draggable').innerHTML = species;
        // Initialisation de sortable.js
        var el = document.querySelector('.modal-content ul');
        Sortable.create(el);
    }

    document.getElementById('next-step-build').onclick = function () {
        // Récupération de l'ordre
        var ord_sp = [];
        // Récupération de l'élément contenant les espèces
        var contenant = document.querySelector('.modal-content ul');

        if (contenant.hasChildNodes()) {
            var childs = contenant.childNodes;

            for (var i = 0; i < childs.length; i++) {
                // Vérification que l'élément est bien un DocumentElement (les espaces vides de texte entre les balises
                // sont des childNode) et vérifie que la classe spéciale "collection-specie" est bien présente
                // pour assurer qu'on manipule forcément notre élément voulu
                // Si c'est le cas, on l'ajoute au tableau. Vu que les éléments sont parcourus dans
                // l'ordre des enfants (du premier au dernier), le tableau sera trié
                if (childs[i].classList && childs[i].classList.contains('collection-specie')) {
                    ord_sp.push(childs[i].dataset.specie);
                }
            }

            launchDatabaseBuild(file, ord_sp);
        }
    }

    // Obligatoire pour conserver les attributs
    $(modal).modal({
        dismissible: false
    });
    var inst = M.Modal.getInstance(modal);
    inst.open();


    // Initialisation de sortable.js
    var el = document.querySelector('.modal-content ul');
    var sortable = Sortable.create(el);
}

function deleteSpecie(specie) {
    var modal = document.getElementById('modal_wipe');

    modal.innerHTML = `<div class="modal-content">
        <h4 id="wipe_header">Delete ${specie} ?</h4>
        <p id="wipe_text">
            Specie will be removed from ordered list (if present) and from supported species.<br>
            It will NOT remove specie from current built database.
        </p>
    </div>
    <div class="modal-footer">
        <form method="post" action="#">
            <div id="wipe_additionnal"></div>
            <a href="#!" class="waves-effect blue-text btn-flat modal-close">
                Cancel
            </a>
        
            <input type="hidden" name="delete" value="${specie}">
            <a href="#!" onclick="this.parentElement.submit()" 
                class="waves-effect red-text btn-flat modal-close" id="setter_wiper">
                Delete
            </a>
        </form>
    </div>`;

    $(modal).modal('open');
}

function sendSpecieOrder() {
    var coll = document.getElementById('specie_order');
    var hidden = document.getElementById('hidden_order');

    var modal = document.getElementById('modal_wipe');
    var prec = document.getElementById('prec_order').innerText;

    // Récupération de l'ordre
    var ord_sp = [];

    if (coll.hasChildNodes()) {
        var childs = coll.childNodes;

        for (var i = 0; i < childs.length; i++) {
            // Explications : voir buildGenomeDbModal()
            if (childs[i].classList && childs[i].classList.contains('collection-specie')) {
                ord_sp.push(childs[i].dataset.specie);
            }
        }

        hidden.value = ord_sp.join(',');
    }

    if (prec === ord_sp.join(', ')) {
        M.toast({html:  "Selected order is the same as currently saved."});
        return;
    }

    modal.innerHTML = `<div class="modal-content">
        <h4 id="wipe_header">Save order ?</h4>
        <p id="wipe_text">
            Precedent order will be overwritten (${prec}).<br><br>
            You're about to save this order:<br> ${ord_sp.join(', ')}
        </p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="waves-effect blue-text btn-flat modal-close">
            Cancel
        </a>
    
        <a href="#!" id="save_definitive_order" 
            class="waves-effect red-text btn-flat modal-close btn-perso" id="setter_wiper">
            Save
        </a>
    </div>`;

    document.getElementById('save_definitive_order').onclick = function () {
        document.getElementById('order_form').submit();
    }

    $(modal).modal('open');
}

function changeWebsiteAccess(ele) {
    var st = (ele.dataset.access === "1" ? "maintenance" : "ok");
    var txt = document.getElementById('accessible_text');
        
    request({
        url: '/api/tools/maintenance.php',
        method: 'POST',
        body: 'status=' + st
    }).then(function () {
        M.toast({html: "Status has been successfully updated."});

        if (ele.dataset.access === "1") {
            ele.dataset.access = "0";
            ele.innerText = "Restore site visibility";
            txt.innerText = 'in maintenance mode';
            ele.classList.remove('red-text');
            ele.classList.add('green-text');
        }
        else {
            ele.dataset.access = "1";
            ele.innerText = "Toggle site maintenance mode";
            txt.innerText = 'accessible';
            ele.classList.remove('green-text');
            ele.classList.add('red-text');
        }
    }).catch(function (e) { 
        M.toast({html: "Modification failed."});
    });
}
