<nav>
    <div class="nav-wrapper light-blue darken-1">
        <a href="/home" class="brand-logo logo-main">
            <img src='/img/favicon.png' style='height: 48px; margin-top: 8px;'>
        </a>
        <a href="/home" class="brand-logo logo-main" style='margin-left: 80px;'><?= SITE_NAME ?></a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
            <li><a href="/home"><i class='material-icons left'>home</i>Home</a></li>
            <li><a class='dropdown-trigger no-outline-focus' data-target='dropdown_search_menu' href="/search">
                <i class='material-icons left'>search</i>Search
            </a></li>
            <li><a href="/add_gene"><i class='material-icons left'>add</i>Add a gene</a></li>
        </ul>
    </div>
</nav>

<!-- Dropdown Structure for Search -->
<ul id='dropdown_search_menu' class='dropdown-content'>
    <li><a href="/search/name">By name</a></li>
    <li class="divider" tabindex="-1"></li>
    <li><a href="/search/id">By ID</a></a></li>
    <li class="divider" tabindex="-1"></li>
    <li><a href="/search/pathway">By pathway</a></li>
    <li class="divider" tabindex="-1"></li>
    <li><a href="/search/global">Advanced</a></li>
</ul>
