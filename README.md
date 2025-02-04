# 4IN

Ceci est un petit guide de développement pour créer rapidement une page web à destination de 4IN, la base de données de gènes
de l'immunité des insectes.
L'intégralité des pages web "classiques" doivent passer via index.php, qui chargera votre nouvelle page.
La redirection automatique est réalisée en fond par Apache, serveur web.

# Introduction

Ce site web utilise PHP comme langage serveur, ainsi que d'autres modules.

Les versions requises sont les suivantes:
- PHP 7.1 minimum, 7.2 recommandé, compatible 7.3
- MySQL/MariaDB
- Apache 2.4 recommandé
    - mod_headers
    - mod_rewrite
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
Au coeur de la fonction contrôleur, le développeur est libre de lancer toutes les exceptions qu'il souhaite.

Certaines exceptions sont particulières si elles ne sont pas attrapées:
- `PageNotFoundException` déclenchera une page 404. Le texte de l'exception (facultatif) sera affiché à l'utilisateur.
- `ForbiddenPageException` affichera une page 403.
- `NotImplementedException` affichera un texte précisant que la fonctionnalité n'est pas implémentée actuellement.

Toute autre exception non attrapée sera une erreur 500 et sera enregistrée dans les fichiers de log (voir [logging](#Logging)).

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
    - mapping
        - Fichiers de mapping
    - fasta
        - adn
        - pro
    - db
        - Fichiers TSV uploadés
        - Schéma SQL
        - Paramètres du site au format JSON
    - log
        - Fichiers de log du site web
- css
- img
- inc
    - Fichiers PHP inclus (ne faisant référence à aucune page)
- js
- ncbi
    - bin
        - base
            - Bases de données BLAST
- pages
    - Pages PHP du site web
- static
    - Pages PHP, mais étant implicitement incluses
        - Footer
        - Header
        - Page d'accueil
        - Pages d'erreurs
- index.php (Page générale, traite toutes les requêtes)


## Ajouter une page web

Pour ajouter une nouvelle page de zéro, vous aurez besoin de créer un fichier et d'en modifier un seul:

- `pages/your_name.php` - Le point de départ de votre page, qui contiendra toutes les fonctions nécessaires à son exécution. Vous devez le créer.
- `inc/cst.php` - Vous devez **modifier** ce fichier, plus précisément le contenu de la constante **PAGES_DIR** dans l'optique d'intégrer votre page dans la **route** du site web.

### Utilisation du modèle vue/contrôleur basique

Premièrement, vous devez créer deux fonctions dans votre fichier **your_name.php** :
your_function_name_for_controller() et your_function_name_for_view().

#### Contrôleur/Modèle

`your_function_name_for_controller` prend un tableau en paramètre (les arguments supplémentaires de l'URL).

Son prototype peut ressembler à :
```php
function myController(array $url_arguments) : Controller {}
```

---

**Attention**

Si vous écrivez une page d'erreur, votre fonction peut prendre en paramètre un objet `Throwable`. Vous devrez néanmoins respecter ces règles:
- Le nom de votre page (clé dans le fichier de constantes) doit être un chiffre
- Pour passer un `Throwable` en paramètre, vous devez capturer une exception particulière dans la fonction `getRoute()`

---

Votre fonction **DOIT** renvoyer un objet **Controller** dont le constructeur est:
```php
return new Controller(['array_of_mixed_data_of_what_you_want'], 'page title in FORMATTED HTML');
```
Cette fonction sera le **contrôleur** de votre page et calculera tous les éléments (requêtes SQL, opérations complexes, tris, etc.) nécessaires à l'affichage sans ne faire **AUCUN AFFICHAGE** à l'écran, et stockera tous les résultats -organisés à votre sauce- dans un tableau, premier argument du constructeur de Controller.

#### Vue

`your_function_name_for_view` prend un TABLEAU en paramètre (les arguments supplémentaires de l'URL).
Elle **DOIT** prendre en paramètre un objet **Controller**, le prototype de votre fonction serait:
```php
function your_function_name_for_view(Controller $c) : void {}
```
Cette fonction sera la **vue** de votre page et affichera tous les éléments (tableaux, images, texte) et doit faire **AUCUN** calcul, requête ou écriture de fichier. Vous pouvez obtenir votre tableau de résultat passé au Controller au constructeur vu plus haut, de cette façon :
```php
$data = $c->getData();
```

#### Fichier de constante

Ces deux fonctions sont à préciser, ainsi qu'avec le chemin de votre fichier dans le tableau de constantes,
avec en clé le nom que vous souhaitez donner à votre page (dans l'URL).
Par exemple, pour `NC3I.fr/test/other_data`, La clé sera "test"

Vous rajouterez une ligne dans le tableau **PAGES_DIR** de `inc/cst.php` de ce format:
```php
'test' => ['file' => 'pages/your_name.php', 'view' => 'your_function_name_for_view', 'controller' => 'your_function_name_for_controller'],
```
Ainsi, l'URL `domain.tld/test` chargera les fonctions de la page `pages/your_name.php`.

# Debug

Le mode de debug est géré par la constante `DEBUG_MODE` dans le fichier `inc/cst.php`.

Activé, le mode active l'affichage des erreurs PHP dans l'HTML (notice y compris), l'affichage des erreurs BLAST dans l'HTML (si l'utilisateur est connecté) et l'affichage du contenu des `Exception` dans les pages 500.
Activer le mode de debug n'arrête PAS le logging dans les fichiers texte.

# BLAST

Pour vous servir du module BLAST, vous devrez dézipper les binaires de [BLAST+](ftp://ftp.ncbi.nlm.nih.gov/blast/executables/blast+/LATEST/) dans un dossier `ncbi` à la racine du site web. Les fichiers binaires `blastn`, `blastp`, etc... doivent donc se trouver dans `ncbi/bin/...`.

Notez qu'un système UNIX est requis pour le fonctionnement de BLAST.

# Fonctionnalités

## Recherche avancée
La recherche avancée combine tous les modes de recherche possible du site web.
Un critère de recherche est nécessaire au minimum pour la lancer.

Les critères disponibles pour rechercher des gènes sont:
- Voie métabolique
- Espèce
- Mots clés, comprenant :
  - Nom
  - Nom complet
  - Fonction
  - Identifiant
  - Famille
  - Sous-famille
- Pour les utilisateurs ayant les droits, une recherche sur les détails additionnels (marqués entre parenthèses dans le fichier source TSV) est possible. Cette recherche est limitée en raison de la nécessité d'utiliser une expression régulière pour rechercher dans ce champ. Prenez garde à limiter les recherches avec lui, celles-ci sont consommatrices pour le serveur SQL et les résultats peu lisibles, ce champ n'apparaissant pas dans le tableau de résultat de recherche

## BLAST
Un BLAST peut être effectué sur les séquences présentes sur le site.

La base de données BLAST doit être préalablement être construite depuis la console d'administration pour fonctionner.
Pour les utilisateurs n'ayant pas les droits, les espèces dont le génome n'est pas public (considérées comme "protégées" dans l'interface d'administration) ne sont pas recherchées par BLAST.

Tous les paramètres classiques de BLAST sont disponibles depuis le formulaire de requête.

# Détails additionnels

### Console d'administration
Arborescence sous `/admin`
- import
  - genome
  - blast
- build
  - genome
  - blast
- alias
  - import
  - build
- messages
- db_species
- species
- reset_link
- password
- stats
- checker
- converter
- verify
- create_user
- manage_user

### Images de la page d'accueil
Vous pouvez rajouter des images s'affichant sur la page d'accueil en collant des fichiers .jpg (JPEG) dans le dossier `img/home/`.

### Mode nuit
Un mode nuit est pseudo implémenté. 

Vous pouvez l'activer/désactiver temporairement en appelant `enableNightMode()` / `disableNightMode()` depuis la console JavaScript.

L'activation permanente est possible en ajoutant la query string `?night_mode=1` à n'importe quelle page. La désactivation se réalise en affectant 0 au paramètre `night_mode` de la query string.
