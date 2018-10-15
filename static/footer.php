<footer class="page-footer blue lighten-1">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">

                <h5 class="white-text">Développeurs</h5>
                <p class="grey-text text-lighten-4">
                    Nous sommes une équipe d'étudiants en master qui travaille
                    sans relâche pour créer la meilleure base de données de gènes liés à l'immunité du monde.
                </p>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Menu</h5>
                <ul>
                    <li><a class="white-text" href="/login"><?= isUserLogged() ? "Déc" : 'C' ?>onnexion</a></li>
                    <li><a class="white-text" href="/search">Rechercher</a></li>
                    <li><a class="white-text" href="/add_gene">Ajouter un gène</a></li>
                    <li><a class="white-text" href="https://blast.ncbi.nlm.nih.gov/Blast.cgi">BLAST</a></li>
                </ul>
            </div>
            <div class="col l3 s12">
                <h5 class="white-text">Partenaires</h5>
                <ul>
                    <li><a class="white-text" href="http://www.inra.fr/">INRA</a></li>
                    <li><a class="white-text" href="http://bf2i.insa-lyon.fr/">BF2i</a></li>
                    <li><a class="white-text" href="https://www.universite-lyon.fr/">Université de Lyon</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-copyright" style='min-height: unset;'>
    </div>
</footer>
