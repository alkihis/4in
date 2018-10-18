<footer class="page-footer blue lighten-1">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">Developpers</h5>
                <p class="grey-text text-lighten-4">
                    We are a team of students in our first year of master, working hastily to achieve the world's greatest database.
                </p>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Menu</h5>
                <ul>
                    <li><a class="white-text" href="/login">Log<?= isUserLogged() ? "out" : 'in' ?></a></li>
                    <li><a class="white-text" href="/search">Search</a></li>
                    <li><a class="white-text" href="/add_gene">Add a gene</a></li>
                    <li><a class="white-text" href="https://blast.ncbi.nlm.nih.gov/Blast.cgi">BLAST</a></li>
                    <li><a class="white-text" href="/help">Help</a></li>
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
