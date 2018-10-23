<?php

function adminControl(array $args) : Controller {
    if (!isUserLogged()) {
        throw new ForbiddenPageException();
    }

    if (!isset($args[0]) || empty($args[0])) {
        $page = homePageController();
    }
    else if ($args[0] === 'import') {
        if (!isset($args[1])) {
            throw new PageNotFoundException();
        }
        else if ($args[1] === 'genome') {
            $page = importGenomeController();
        }
        else if ($args[1] === 'blast') {
            $page = importBlastController();
        }
        else {
            throw new PageNotFoundException();
        }
    }
    else if ($args[0] === 'build') {
        if (!isset($args[1])) {
            throw new PageNotFoundException();
        }
        else if ($args[1] === 'genome') {
            $page = buildGenomeController();
        }
        else if ($args[1] === 'blast') {
            $page = buildBlastController();
        }
        else {
            throw new PageNotFoundException();
        }
    }
    else if ($args[0] === 'reset_link') {
        $page = resetLinkController();
    }
    else if ($args[0] === 'password') {
        $page = passwordController();
    }
    else if ($args[0] === 'species') {
        $page = speciesController();
    }
    else {
        throw new PageNotFoundException();
    }

    return new Controller($page, 'Admin console');
}

function homePageController() : array {
    return [];
}

function importGenomeController() : array {
    throw new NotImplementedException();
}

function importBlastController() : array {
    throw new NotImplementedException();
}

function buildGenomeController() : array {
    throw new NotImplementedException();
}

function buildBlastController() : array {
    throw new NotImplementedException();
}

function resetLinkController() : array {
    throw new NotImplementedException();
}

function passwordController() : array {
    throw new NotImplementedException();
}

function speciesController() : array {
    throw new NotImplementedException();
}

function adminView(Controller $c) : void {
    $d = $c->getData();

    ?>

    <!-- Sidenav -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <li>
            <div class="user-view">
                <div class="background">
                    <img src="/img/ADN.jpg">
                </div>
                <a><img class="circle" src="/img/favicon.png"></a>
                <a><span class="white-text name">Admin Console</span></a>
                <a><span class="white-text email"></span></a>
            </div>
        </li>
        <li><a class="subheader">Build</a></li>
        <li><a class="waves-effect" href="/admin/build/genome"><i class="material-icons">storage</i>Build genome database</a></li>
        <li><a class="waves-effect" href="/admin/build/blast"><i class="material-icons">sort</i>Build BLAST database</a></li>

        <li><div class="divider"></div></li>

        <li><a class="subheader">Import</a></li>
        <li><a class="waves-effect" href="/admin/import/genome"><i class="material-icons">backup</i>Import genome file</a></li>
        <li><a class="waves-effect" href="/admin/import/blast"><i class="material-icons">playlist_add</i>Import sequence files</a></li>

        <li><div class="divider"></div></li>

        <li><a class="subheader">Manage</a></li>
        <li><a class="waves-effect" href="/admin/reset_link"><i class="material-icons">refresh</i>Reset link status</a></li>
        <li><a class="waves-effect" href="/admin/species"><i class="material-icons">lock</i>Protected species</a></li>
        <li><a class="waves-effect" href="/admin/password"><i class="material-icons">vpn_key</i>Change password</a></li>
    </ul>

    <style>
        header, main, footer {
            padding-left: 300px;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('.sidenav').sidenav();
        });
    </script>
    <?php

}
