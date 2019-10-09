# Anakeen cli

Ce paquet contient les sources du module anakeen-cli.

## Présentation

Ce module met à disposition une commande permettant de faire les tâches utilitaires autour d'un module anakeen-platform
(build, deploy, init, etc.).

## Prérequis

* nodejs > 6
* npm > 5.2

## Installation

Pour commencer un nouveau projet avec @anakeen/anakeen-cli, la démarche est simple.

* Créer le répertoire
* Faire un npm init dans le répertoire
* Enregistrer la registry anakeen ```npm config set @anakeen:registry http://npm-stable.corp.anakeen.com:4873```
* lancer le npm install du module ```npm install @anakeen/anakeen-cli```
* ensuite les commandes sont accessibles de deux manières :
 * via un outil en cli ```npx @anakeen/anakeen-cli```
 * via des tâches gulp en créant un fichier ```gulpfile.js``` et ajouter les tâches dans ce fichier
 
 ## Déploiement
 
 On peut configurer les paramètres de déploiement dans le fichier `.anakeen-cli.xml`
 
 Exemple :
 
```xml
    <?xml version="1.0" encoding="utf-8" ?>
    <config>
        <contextConfig>
            <contextUrl>http://localhost:10080</contextUrl>
            <contextUsername>admin</contextUsername>
            <contextPassword>anakeen</contextPassword>
        </contextConfig>
        <controlConfig>
            <controlUrl>http://localhost:10080/control</controlUrl>
            <controlUsername>admin</controlUsername>
            <controlPassword>anakeen</controlPassword>
            <controlContext>anakeen-platform</controlContext>
        </controlConfig>
    </config>
```
 
 ## Utilisation sans installation
 
 Comme tout module npm, on peut aussi l'utiliser directement sans installation :
 
 ```npx @anakeen/anakeen-cli```
 
## Dévelopement 

Pour lancer manuellement anakeen-cli après avoir cloné le repo, il est possible de faire :

```node ./index.js```

## Run CLI locally

```
$ yarn run anakeen-cli --help
```

## A4PPM registry authentication

Quand un dépôt A4PPM est ajouté avec les options `--authUser` et
`--authPassword`, les identifiants de connexion sont stockés dans un fichier
`.anakeen-cli.credentials`.

Le fichier `.anakeen-cli.credentials` est recherché dans l'ordre suivant :

* dans le répertoire courant (e.g. `./.anakeen-cli.credentials`) ;
* dans le répertoire de l'utilisateur (e.g. `$HOME/.anakeen-cli.credentials`) ;
* de manière récursive dans les répertoires depuis le répertoire courant jusqu'à
  la racine du système de fichier (`/`).

Le fichier `.aankeen-cli.credentials` est au format XML et structuré comme suit
:

```xml
<?xml version="1.0" encoding="utf-8" ?>
<credentials>
    <credential url="http://a4ppm.example.net/bucketName/" authUser="john.doe" authPassword="secret" />
</credentials>
```
