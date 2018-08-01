# Anakeen cli

Ce paquet contient les sources du module anakeen-cli.

## Présentation

Ce module met à disposition une commande permettant de faire les tâches utilitaires autour d'un module anakeen-platform
(build, deploy, init, etc.).

## Pré-requis

* nodejs > 6
* npm > 5.2

## Installation

Pour commencer un nouveau projet avec @anakeen/anakeen-cli, la démarche est simple.

* Créer le répertoire
* Faire un npm init dans le répertoire
* Enregistrer le registry anakeen ```npm login --registry=http://npm.corp.anakeen.com:4873 --scope=@anakeen```
* lancer le npm install du module ```npm install @anakeen/anakeen-cli```
* ensuite les commandes sont accessibles de deux manières :
 * via un outil en cli ```npx anakeen-cli```
 * via des tâches gulp en créant un fichier ```gulpfile.js``` et ajouter les tâches dans ce fichier