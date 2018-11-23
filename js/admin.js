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
                current++;
                // Actualiser la barre...
                bar.style.width = String(Math.round((current / total) * 100)) + '%';
    
                num.innerText = current;
            }).catch(function (e) {
                current++;
                // Actualiser la barre...
                bar.style.width = String(Math.round((current / total) * 100)) + '%';
    
                num.innerText = current;
            });
        }
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

async function launchMakeBlast() {
    var modal = document.getElementById('modal-admin');

    // Obligatoire pour conserver les attributs
    $(modal).modal({
        dismissible: false
    });
    var inst = M.Modal.getInstance(modal);
    inst.open();
    
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
        <h4>Build complete</h4>
        <p>
            ${ok ? "BLAST database has been successfully builded" : 
                "<span class='red-text'>An error occurred while creating BLAST database.</span>"}.
        </p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat">Close</a>
    </div>`;
}

async function launchDatabaseBuild(file, species, trim_first, read_first) {
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

    var ok = true;
    var spec_text = "";

    await request({
        url: '/api/tools/database_creator.php',
        method: 'POST',
        body: 'file=' +  encodeURIComponent(file) + '&empty=true&species=' + 
            encodeURIComponent(species.join(',')) + '&trim_first=' + trim_first + '&read_first=' + read_first
    }).then(function (data) {
        var json = JSON.parse(data);

        for (var specie in json) {
            spec_text += String(json[specie].count) + " genes from " + specie + " (" + (json[specie].name === null ? 
                    "<span class='red-text'>No acronym is set. (Check spelling)</span>" : json[specie].name) + ")";

            spec_text += "<br>";
        }
    }).catch(function (e) {
        ok = false;
    });

    // Affiche un message signalant la fin
    modal.innerHTML = `<div class="modal-content">
        <h4>Import complete</h4>
        <p>
            ${ok ? "Database has been successfully builded." : 
                "<span class='red-text'>An error occured during database creation.</span>"}
        </p>
        ${ok ? "<h5>Imported genes and species</h5><p>" + spec_text + "</p>" : ''}
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

function buildGenomeDbModal(file, trim_first, read_first) {
    var modal = document.getElementById('modal-admin');

    var species = document.getElementById('collection-build').innerHTML;

    var text = '';

    if (read_first) {
        text = `First line of the file will be used to define specie name.<br>
        Be sure that file's specie names are correctly written (case-sensitive).<br><br>
        Please note that order of first colomns (Name, family...) will NOT be defined by reading the first line and must
        respect the following order : Name, Role, Pathway, Fullname, Family, SubFamily.<br>
        Species must be defined after this colomns.`;
    }
    else {
        text = `Check if the order of species is correct. 
        If a specie is missing or is not meant to be present, 
        please enter/delete it using the "manage database species" utility.<br>
        Please be aware that if too many species are present, the build may fail.
        ${(trim_first ? '<br>First line of the file will be ignored.' : '')}`;
    }
    var str = `<div class="modal-content">
        <h4>Build genome database</h4>
        <p>
            <span class="underline">
                All previous database genes and sequences will be cleared and replaced by choosen file</span>.<br><br>
            ${text}
        </p>

        ${!read_first ? `<div class="modal-draggable" id="species-draggable">
            ${species}
        </div>`: ''}
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat btn-perso left">Close</a>
        ${!read_first ? `<a href="#!" class="btn-flat orange-text btn-perso left" id="reset-spec">Reset species</a>`: ''}

        <a href="#!" id="next-step-build" class="green-text btn-flat btn-perso right">Build database</a>
    </div>`;

    modal.innerHTML = str;

    var reset = document.getElementById('reset-spec');

    if (reset) {
        document.getElementById('reset-spec').onclick = function () {
            document.getElementById('species-draggable').innerHTML = species;
            // Initialisation de sortable.js
            var el = document.querySelector('.modal-content ul');
            Sortable.create(el);
        }
    }

    document.getElementById('next-step-build').onclick = function () {
        // Récupération de l'ordre
        var ord_sp = [];

        if (!read_first) {
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
            }
        }

        launchDatabaseBuild(file, ord_sp, trim_first, read_first);
    }

    // Obligatoire pour conserver les attributs
    $(modal).modal({
        dismissible: false
    });
    var inst = M.Modal.getInstance(modal);
    inst.open();


    // Initialisation de sortable.js
    var el = document.querySelector('.modal-content ul');
    if (el)
        Sortable.create(el);
}

function deleteFile(file, mode = "") {
    var modal = document.getElementById('modal_wipe');

    modal.innerHTML = `<div class="modal-content">
        <h4 id="wipe_header">Delete this file ?</h4>
        <p id="wipe_text">
            File will be permanetly deleted and cannot be restored.
        </p>
    </div>
    <div class="modal-footer">
        <form method="post" action="#">
            <div id="wipe_additionnal"></div>
            <a href="#!" class="waves-effect blue-text btn-flat modal-close">
                Cancel
            </a>
        
            <input type="hidden" name="delete" value="${file}">
            <input type="hidden" name="mode" value="${mode}">
            <a href="#!" onclick="this.parentElement.submit()" 
                class="waves-effect red-text btn-flat modal-close">
                Delete
            </a>
        </form>
    </div>`;

    $(modal).modal('open');
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

function initAdminModalForBlastBuild() {
    document.getElementById('build_header').innerText = 'Build BLAST database from sequences in database ?';
    document.getElementById('build_text').innerText = 'Building will wipe current BLAST database, \
        and construct BLAST DB from website SQL DB.';

    document.getElementById('setter_builder').onclick = function () {
        launchMakeBlast();
    };
}

function initAdminModalForSequenceBuild() {
    var header = document.getElementById('build_header');
    header.innerText = '';

    var text = document.getElementById('build_text')
    text.innerHTML = `<div class="center">${preloader_circle}</div>`;
    
    var setter = document.getElementById('setter_builder');
    $(setter).hide(0);

    $.get('/api/tools/get_all_fasta_files.php', {}, function(data) {
        var json = JSON.parse(data);

        var count = json.adn.length + json.pro.length;

        header.innerText = 'Insert sequences in database ?';
        text.innerHTML = '<p>' + count + ' file(s) will be parsed.<br>This operation may take a while.</p>';

        $(setter).show();

        setter.onclick = function () {
            launchFastaBuild(json);
        };
    });
}

function initAdminModalForBlastDelete() {
    document.getElementById('wipe_header').innerText = 'Wipe BLAST database ?';
    document.getElementById('wipe_text').innerText = 'After BLAST database wipe, you can\'t \
        use BLAST until you load sequences again.';

    document.getElementById('wipe_additionnal').innerHTML = `
        <input type="hidden" name="clear_blast" value="true">
    `;
}

function initAdminModalForSequenceDelete() {
    document.getElementById('wipe_header').innerText = 'Wipe database sequences ?';
    document.getElementById('wipe_text').innerText = 'If you wipe sequences, all genomic and proteic data will be lost and \
        FASTA files must be parsed again.';

    document.getElementById('wipe_additionnal').innerHTML = `
        <input type="hidden" name="wipe_seq" value="true">
    `;
}

function verifyAllGenes() {
    let comp = document.getElementById('output_verify');
    let comp2 = document.getElementById('output_first_verify');

    if (window.on_verify_work) {
        return;
    }

    window.on_verify_work = true;

    // Récupère tous les ID sous la forme {'id': null, 'id2': null, ...}
    $.get('/api/search/ids.json', {}, async function(res) {
        let promises = [];

        let max = Object.keys(res).length;
        let actual = 1;
        let valid = 0;

        comp2.innerText = String(max) + " genes to check. This may take a while.";
        comp.innerText = "0% of genes completed";

        for (let e in res) { // Pour chaque ID de gène
            promises.push(
                request({ // Requête pour le faire vérifier
                    url: "/api/tools/verify_gene.php",
                    method: 'POST',
                    body: 'gene=' + encodeURIComponent(e)
                }).then(function(d) { valid += (JSON.parse(d).success ? 1 : 0); }).catch(error => error)
            );

            // On attend max 50 promises
            if (promises.length >= 50) {
                await Promise.all(promises);

                var cur = Math.floor(actual/max * 100);

                comp.innerText = String(cur) + "% of genes completed";

                promises = [];
            }

            actual++;
        }

        if (promises.length > 0) { // Si il en reste, on les attend
            await Promise.all(promises);
        }

        comp.innerText = "Completed, " + String(valid) + " of " + String(max) + " genes with valid link";
        comp2.innerText = '';

        delete window.on_verify_work;
    });
}
