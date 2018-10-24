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

    modal.innerHTML = `<div class="modal-content">
        <h4>Loading sequences</h4>
        <p>
            Importing file <span id="file_number">1</span> of ${total}
        </p>
        ${preloader_bar}
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
        <div class="center-align" style="margin-top: 30px;">
            ${preloader_circle}
        </div>
    </div>
    <div class="modal-footer">
        
    </div>`;

    await request({
        url: '/api/tools/blast_creator.php',
        method: 'POST',
        body: 'make=true'
    }).catch(function (e) { });

    // Affiche un message signalant la fin
    modal.innerHTML = `<div class="modal-content">
        <h4>Import complete</h4>
        <p>
            <span id="file_number">${success}</span> files has been successfully imported (${(total - success)} failed).<br>
            BLAST database has been successfully builded.
        </p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close red-text btn-flat">Close</a>
    </div>`;
}
