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
* Enregistrer le registry anakeen ```npm config set @anakeen http://npm-stable.corp.anakeen.com:4873```
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

