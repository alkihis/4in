# NC3I

**Démarrage rapide**

Ceci est un petit guide de développement pour créer rapidement une page web à destination de NC3I, la base de données de gènes
de l'immunité des insectes.
L'intégralité des pages web "classiques" doivent passer via index.php, qui chargera votre nouvelle page.
La redirection automatique est réalisée en fond par Apache, serveur web.

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
return new Controller(['array_of_mixed_data_of_what_you_want'], 'page title in F-O-R-M-A-T-T-E-D HTML');
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
$my_array = $c->getData();
```

**Fichier de constante**

Ces deux fonctions sont à préciser, ainsi qu'avec le chemin de votre fichier dans le tableau de constantes,
avec en clé le nom que vous souhaitez donner à votre page (dans l'URL).
Par exemple, pour NC3I.fr/test; La clé sera "test"

Vous rajouterez une ligne dans le tableau **PAGES_DIR** de `inc/cst.php` de ce format:
```php
'test' => ['file' => 'pages/your_name.php', 'view' => 'your_function_name_for_view', 'controller' => 'your_function_name_for_controller'],
```
Ainsi, l'URL `NC3I.fr/test` chargera les fonctions de la page `pages/your_name.php`.

## Éléments additionnels


## Licence

[CC BY-NC-ND 4.0](LICENSE.md)
