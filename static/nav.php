<?php
if (isAdminLogged())
    $unread_msg = getUnreadMessages();
?><nav>
    <div class="nav-wrapper nav-color">
        <a href="/home" class="brand-logo logo-main">
            <img alt="Site logo" src='/img/logo.png'>
            <span class="logo-text"><?= SITE_NAME ?></span>
        </a>

        <ul id="nav-mobile" class="right hide-on-med-and-down">
            <li><a href="/home"><i class='material-icons left'>home</i>Home</a></li>
            <li><a class='dropdown-trigger no-outline-focus' data-target='dropdown_search_menu' href="/search">
                <i class='material-icons left'>search</i>Search
            </a></li>
            <li><a href="/blast_search"><i class='material-icons left'>sort</i>BLAST</a></li>
            <?php if (isAdminLogged()) { ?>
                <li><a href="/admin/messages">
                    <i class='material-icons left glob-count-icon'><?= ($unread_msg ? 'mail' : 'drafts') ?></i>Messages
                    <?= ($unread_msg ? "<span class='new glob-count badge yellow darken-4' data-badge-caption=''>$unread_msg</span>" : '') ?>
                </a></li>
            <?php } ?>
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
