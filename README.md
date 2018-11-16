# NC3I

## Démarrage rapide

Ceci est un petit guide de développement pour créer rapidement une page web à destination de NC3I, la base de données de gènes
de l'immunité des insectes.
L'intégralité des pages web "classiques" doivent passer via index.php, qui chargera votre nouvelle page.
La redirection automatique est réalisée en fond par Apache, serveur web.

Pour le guide d'utilisation complet, commencez par [introduction](#introduction).

---

**Comment ajouter une nouvelle page ?**

Pour ajouter une nouvelle page de zéro, vous aurez besoin de créer un fichier et d'en modifier un seul:

- `pages/your_name.php` - Le point de départ de votre page, qui contiendra toutes les fonctions nécessaires à son exécution. Vous devez le créer.
- `inc/cst.php` - Vous devez **modifier** ce fichier, plus précisément le contenu de la constante **PAGES_DIR** dans l'optique d'intégrer votre page dans la **route** du site web.

Si vous ne comprenez pas le principe et le concept, envoyez moi un [e-mail](mailto:louis.beranger@etu.univ-lyon1.fr).

## Utilisation du modèle vue/contrôleur basique

Premièrement, vous devez créer deux fonctions dans votre fichier **your_name.php** :
your_function_name_for_controller() et your_function_name_for_view().

**Contrôleur**

`your_function_name_for_controller` prend un TABLEAU en paramètre (les arguments supplémentaires de l'URL).
Elle **DOIT** renvoyer un objet **Controller** dont le constructeur est:
```php
return new Controller(['array_of_mixed_data_of_what_you_want'], 'page title in FORMATTED HTML');
```
Cette fonction sera le **contrôleur** de votre page et calculera tous les éléments (requêtes SQL, opérations complexes, tris, etc.) nécessaires à l'affichage sans ne faire **AUCUN AFFICHAGE** à l'écran, et stockera tous les résultats -organisés à votre sauce- dans un tableau, premier argument du constructeur de Controller.

**Vue**

`your_function_name_for_view` prend un TABLEAU en paramètre (les arguments supplémentaires de l'URL).
Elle **DOIT** prendre en paramètre un objet **Controller**, le prototype de votre fonction serait:
```php
function your_function_name_for_view(Controller $c) : void {}
```
Cette fonction sera la **vue** de votre page et affichera tous les éléments (tableaux, images, texte) et doit faire **AUCUN** calcul, requête ou écriture de fichier. Vous pouvez obtenir votre tableau de résultat passé au Controller au constructeur vu plus haut, de cette façon :
```php
$data = $c->getData();
```

**Fichier de constante**

Ces deux fonctions sont à préciser, ainsi qu'avec le chemin de votre fichier dans le tableau de constantes,
avec en clé le nom que vous souhaitez donner à votre page (dans l'URL).
Par exemple, pour NC3I.fr/test; La clé sera "test"

Vous rajouterez une ligne dans le tableau **PAGES_DIR** de `inc/cst.php` de ce format:
```php
'test' => ['file' => 'pages/your_name.php', 'view' => 'your_function_name_for_view', 'controller' => 'your_function_name_for_controller'],
```
Ainsi, l'URL `domain.tld/test` chargera les fonctions de la page `pages/your_name.php`.

# Introduction

Ce site web utilise PHP comme langage serveur, ainsi que d'autres modules.

Les versions requises sont les suivantes:
- PHP 7.1 minimum, 7.2 recommandé
- MySQL/MariaDB
- Apache 2.4 recommandé
    - mod_headers
    - mod_rewite
    - AllowOverride All
- Bash (système UNIX requis)

BLAST requiert l'utilisation de Bash.
PHP 7.1 est requis pour le typage d'arguments dans les fonctions notamment, ainsi que pour l'opérateur `??`.
Apache et ses modules sont utilisés pour gérer les accès à certains fichiers et surtout construire la route depuis l'URL.
MySQL est nécessaire pour le stockage des données.
  
Côté client:
- Navigateur supportant JavaScript ES5 (2009) minimum
- JavaScript ES7 (2016) minumum demandé pour la console d'administration
    - Google Chrome 55
    - Mozilla Firefox 52
    - Safari 10.1
    - Internet Explorer (toutes versions) n'est **pas** compatible

Le JavaScript "public" (destiné à tous les utilisateurs du site) est
volontairement rétro-compatible avec de vieilles versions de navigateurs,
mais Internet Explorer < 11 peut ne pas être compatible avec tous les modules.

Dans la console d'administration, les fonctions asynchrones `async / await` sont utilisées et restreignent fortement la compatibilité.

# Configuration initiale

Le site web est techniquement prêt à l'emploi dès le zippage de son archive. Les deux conditions nécessaires à la création de la route est d'être à la racine du serveur web (la variable `DOCUMENT_ROOT` **DOIT** pointer sur le dossier où est index.php et tous les autres dossiers) **ET** le site doit être à la racine d'un nom de domaine.

Par exemple, pour que la route fonctionne, l'URL doit ressembler à `base_donnees.domain.tld/` et ne doit surtout pas être localisée dans un sous-dossier d'un domaine. Ce comportement est facilement configurable dans la fonction `getSelectedUrl()` située dans `inc/func.php`.

# Paramètres du site

Les paramètres généraux du site liés à son comportement sont présents dans le fichier `inc/cst.php`, des commentaires expliquent leurs utilités.

D'autres paramètres (espèces supportées, ordre d'importation) se gèrent depuis la console d'administration du site.

# Fonctionnement

À partir de ce point, il sera supposé que le lecteur dispose d'un niveau en PHP à minima intermédiaire.

## Architechture
Le site web est ordonné selon un modèle vue-contrôleur (sans framework) basique, organisé autour de la classe `Controller`. Une fois l'URL parsée, le modèle correspondant au premier paramètre de l'URL sera chargée (c'est la fonction "controller" définie dans le fichier de constantes), cette fonction devant renvoyer un tableau de données résultant du traitement effectué pour récupérer les données nécessaires à l'affichage. La fonction de vue est ensuite appelée avec ce tableau en argument.

Dans l'ordre de traitement, la fonction contrôleur est d'abord appelée, puis le début du document HTML (head, header) est envoyé, puis la fonction de vue est appelée après l'ouverture de la balise &lt;main&gt;.
Le footer est ensuite généré.


## Gestions des erreurs

### Contrôleur 
Au coeur de la fonction contrôleur, le développeur est libre de lancer toutes les exceptions qu'il souhaite. Si elles ne sont pas attrapées, elles déclencheront une erreur 500.

Certaines exceptions sont particulières:
- `PageNotFoundException` déclenchera une page 404. Le texte de l'exception sera affiché à l'utilisateur.
- `ForbiddenPageException` affichera une page 403.
- `NotImplementedException` affichera un texte précisément que la fonctionnalité n'est pas implémentée à ce moment.
- Toute autre exception sera une erreur 500.

### Vue

Dans la fonction de vue, le programmeur ne doit PAS lancer d'Exception et faire attention d'appeler des fonctions n'en lançant pas (ou faire des blocs `try / catch`).

### Logging

Dans la vue et le contrôleur, vous êtes libre de logger des informations si jamais une opération particulière se produit.

Les logs sont enregistrés dans `assets/log/*year*_*month*.log`.

Pour logger, utilisez la méthode statique `write` du singleton `Logger`.
```php
Logger::write(string $message, bool $put_eol = true, bool $put_date = true);
```

Il est rappelé de ne PAS lancer d'exceptions non attrapées dans la fonction de vue : l'exécution de PHP sera interrompue et l'intégralité des logs effectués durant cette exécution seront perdus.

### Erreurs de PHP

Les erreurs rencontrées par PHP après l'initilisation de `Logger` seront consignées dans les logs.
**TOUTES** les erreurs excepté les **NOTICE** seront consignés avec les autres messages dans `year_month.log`, les notices seront elles conservées dans `php_notices.log`.

Une erreur aura la forme: 
```
[day hour:min:sec] E_TYPE : array_keys() expects parameter 1 to be array, null given in file /path/to/file.php at line 32
```


## Design

Ce site utilise [Materialize CSS](http://materializecss.com). La lecture de la documentation est recommandée et l'ensemble des pages écrites doivent utiliser en priorité les fonctionnalités du framework.

Les règles CSS supplémentaires sont à consigner dans `css/style.css`.


## Client

L'utilisation de JavaScript est autorisée, en version ES5 pour les pages générales du site. Pour des raisons pratiques, ce site utilise jQuery (3.3). Essayez à minima d'utiliser VanillaJS au possible et de n'utiliser jQuery que lorsque le gain de code est important, pour maximiser les performances.


## API

Une API est utilisée pour servir des requêtes internes.
Une utilisation depuis l'extérieur est prohibée. 
Son système est extrêmement simpliste et n'implémente aucun système d'identification (hors la session PHP) et est uniquement présente pour répondre aux demandes de l'autocomplétion et de la console d'administration (reconstruction de la base, import de fichier FASTA...).
Vous pouvez rajouter des fichiers à la racine ou dans des sous-dossiers du dossier `api/`.

Pour des raisons pratiques, l'API renvoie en général du JSON et convertit automatiquement l'URL demandée pour pointer sur une page PHP. Ainsi, `domain.tld/api/search/ids.json` pointera sur `api/search/ids.php`.


## Arborescence

Description rapide de l'arborescence du site web

- api
    - main.php (Traite les requêtes)
    - fichiers PHP supplémentaires
- assets
    - cache
        - Fichiers en cache
    - db
        - Base de données BLAST
        - Fichiers TSV uploadés
        - Schéma SQL
        - Paramètres du site au format JSON
    - log
        - Fichiers de log du site web
- css
- fasta
    - adn
        - Fichiers FASTA nucléiques
    - map
        - Fichiers de mapping (.txt)
    - pro
        - Fichiers FASTA protéiques
- img
- inc
    - Fichiers PHP inclus (ne faisant référence à aucune page)
- js
- ncbi
    - Fichiers de BLAST+
- pages
    - Pages PHP du site web
- static
    - Pages PHP, mais étant implicitement incluses
        - Footer
        - Header
        - Page d'accueil
        - Pages d'erreurs
- index.php (Page générale, traite toutes les requêtes)



# Licence

[CC BY-NC-ND 4.0](LICENSE.md)
