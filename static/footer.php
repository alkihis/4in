<footer class="page-footer footer-color">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">INSA Innate Immunity of Insect database</h5>
                <p class="grey-text text-lighten-4">
                    Developed in order to facilitate access to the data harvested from different species of 
                    insects inside INSA's BF2i laboratory, 
                    this database is centered around the genetic study of the rice weevil.<br>
                    Learn more about our <a class="underline white-text" href="/about">team</a>.
                </p>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Menu</h5>
                <ul>
                    <li><a class="white-text" href="/login<?= isUserLogged() ? "?logout=1" : '' ?>">
                        Log<?= isUserLogged() ? "out" : 'in' ?>
                    </a></li>
                    <li><a class="white-text" href="/search">Search</a></li>
                    <li><a class="white-text" href="/blast_search">BLAST</a></li>
                    <li><a class="white-text" href="/contact">Contact us</a></li>
                    <?php if (isUserLogged()) { ?>
                        <li><a class="white-text" href="/admin">Administration</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Partners</h5>
                <ul>
                    <li><a class="white-text" href="http://www.inra.fr/">INRA</a></li>
                    <li><a class="white-text" href="http://bf2i.insa-lyon.fr/">BF2i</a></li>
                    <li><a class="white-text" href="https://www.universite-lyon.fr/">University of Lyon</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright" style='min-height: unset;'>
    </div>
</footer>
