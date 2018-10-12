<nav>
    <div class="nav-wrapper light-blue darken-1">
        <a href="/home" class="brand-logo left logo-main"><?= SITE_NAME ?></a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
            <li><a href="/home"><i class='material-icons left'>home</i>Accueil</a></li>
            <li><a class='dropdown-trigger no-outline-focus' data-target='dropdown_search_menu' href="/search">
                <i class='material-icons left'>search</i>Recherche
            </a></li>
            <li><a href="/add_gene"><i class='material-icons left'>add</i>Ajouter un gène</a></li>
        </ul>
    </div>
</nav>

<!-- Dropdown Structure for Search -->
<ul id='dropdown_search_menu' class='dropdown-content'>
    <li><a href="/search/name">Par nom</a></li>
    <li class="divider" tabindex="-1"></li>
    <li><a href="/search/id">Par identifiant</a></li>
    <li class="divider" tabindex="-1"></li>
    <li><a href="/search/global">Avancée</a></li>
</ul>
