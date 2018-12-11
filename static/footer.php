<footer class="page-footer footer-color">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">Interactive database for Insect Innate Immunity</h5>
                <p class="grey-text text-lighten-4">
                    Developed in order to facilitate access to the data harvested from different species of 
                    insects inside INSA's BF2i laboratory, 
                    This database is centered around the genetic study of the rice weevil.<br>
                    Learn more about our team <a class="underline white-text" href="/about">here</a>.
                </p>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Menu</h5>
                <ul>
                    <li><a class="white-text" href="/login<?= isBasicUserLogged() ? "?logout=1" : '' ?>">
                        Log<?= isBasicUserLogged() ? "out" : 'in' ?>
                    </a></li>
                    <li><a class="white-text" href="/search">Search</a></li>
                    <li><a class="white-text" href="/blast_search">BLAST</a></li>
                    <li><a class="white-text" href="/contact">Contact us</a></li>
                    <?php if (isAdminLogged()) { ?>
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
        <div class="row">
            <div class="col s12">
                <div class="footer-logo">
                    <a href="http://www.inra.fr/" target="_blank">
                        <img src="/img/footer/inra.png" alt="INRA logo">
                    </a>
                </div>
                <div class="footer-logo">
                    <a href="http://insa-lyon.fr/" target="_blank">
                        <img src="/img/footer/insa.png" alt="INSA logo">
                    </a>
                </div>
                <div class="footer-logo">
                    <a href="http://bf2i.insa-lyon.fr/" target="_blank">
                        <img src="/img/footer/bf2i.png" alt="BF2I logo">
                    </a>
                </div>
                <div class="footer-logo">
                    <a href="http://universite-lyon.fr/" target="_blank">
                        <img src="/img/footer/univlyon.png" alt="University of Lyon logo">
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copyright" style='min-height: unset;'>
    </div>
</footer>
