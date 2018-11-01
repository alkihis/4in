<nav>
    <div class="nav-wrapper light-blue darken-1">
        <a href="/home" class="brand-logo logo-main">
            <img src='/img/favicon.png'>
            <span class="logo-text">NC3I</span>
        </a>
        <!-- <a href="/home" class="brand-logo logo-main">
            <i class="material-icons brand-logo">bug_report</i><span class="hide-on-med-and-down"><?= SITE_NAME ?></span>
        </a> -->
        <ul id="nav-mobile" class="right hide-on-med-and-down">
            <li><a href="/home"><i class='material-icons left'>home</i>Home</a></li>
            <li><a class='dropdown-trigger no-outline-focus' data-target='dropdown_search_menu' href="/search">
                <i class='material-icons left'>search</i>Search
            </a></li>
            <li><a href="/blast_search"><i class='material-icons left'>sort</i>BLAST</a></li>
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
