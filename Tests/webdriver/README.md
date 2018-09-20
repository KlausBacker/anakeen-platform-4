# Autottest

## Installation

Installer le paquet `user-interfaces-test`
Lancer la commande `yarn install` à la racine du projet

## Lancement des tests automatiques

### Tous les tests

A la racine du projet lancer la commande :

`npx wdio ./Tests/webdriver/wdio.conf.js -b <url_du_contexte>`

### Juste un fichier

`npx wdio ./Tests/webdriver/wdio.conf.js --spec ./Tests/webdriver/test/specs/40-smartElementList.js`

### Trier par nom

`npx wdio ./Tests/webdriver/wdio.conf.js --spec ./Tests/webdriver/test/specs/30-ank-identity.js -b <url_du_contexte>  --mochaOpts.grep=email`

## Créer une nouvelle série de test

### Route

Ajouter une route dans le fichier `autotest.xml`
Ajouter une classe dans le répertoire `Tests/src/vendor/Anakeen/Routes/`