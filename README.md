# Anakeen cli

Ce paquet contient les sources du module npm anakeen-cli.

## Présentation

Ce module met à disposition une commande permettant de faire les tâches utilitaires autour d'un module anakeen-platform
(build, deploy, init, etc.).

## Pré-requis

* nodejs > 6
* npm > 5.2

## Installation

Pour commencer un nouveau projet avec anakeen-cli, la démarche est simple.

* Créer le répertoire
* Faire un npm init dans le répertoire
* lancer le npm install du module ```npm install git+ssh://git@gitlab.anakeen.com:Anakeen/Platform-4/anakeen-cli.git#master```
* ensuite les commandes sont accessibles de deux manières :
 * via un outil en cli ```npx anakeen-cli```
 * via des tâches gulp en créant un fichier ```gulpfile.js``` et ajouter les tâches dans ce fichier