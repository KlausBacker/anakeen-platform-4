# Anakeen platform 4

Soyez le bienvenu dans le mono repo d'Anakeen platform 4

## TL; DR : comment je développe

Quelques prérequis :

- [`docker`](https://docs.docker.com/install/linux/docker-ce/ubuntu/) ;
- [`docker-compose`](https://docs.docker.com/compose/install/) ;
- Avoir accès en lecture à la registry Docker qui contient les [images
  Docker](`https://registry.ap4.anakeen.com/`) utilisées pour le développement
  de Anakeen Platform 4.

- composer
- yarn (version au moins 1.17)
- npm (+ npm update)
- php-xml
- php-gd
- php-mbstring
- php-zip

Ensuite quelques commandes :

1.  En cas de première initialisation des conteneurs Docker :
    1. - connectez vous à la registry docker d'Anakeen : `docker login
         registry.ap4.anakeen.com`
    1. `make install-all`
1.  Démarrer le contexte `make env-start`
1.  Fermer le contexte `make env-stop`

Je cherche une commande pour faire quelque chose :

`make help`

## Utilisation avancée

### Utilisation « Addons » docker-compose

Les addons docker (`.devtools/Addons`) permettent d'ajouter ou de remplacer des
conteneurs du docker-compose de base via l'utilisation de la fonctionnalité de «
[Multiple Compose
files](https://docs.docker.com/compose/extends/#multiple-compose-files) », qui
permet de spécifier plusieurs fichiers `docker-compose.yml`.

Exemple pour activer l'utilisation du conteneur `nginx` (HTTP+HTTPS+HTTP2) +
conteneur `php-fpm` à la place du conteneur par défaut `apache-mod_php` et
l'ajout d'un conteneur `transformation-server` (pour faire tourner un serveur de
transformation) :

- Supprimer au préalable toutes les images de l'environnement Apache précédent.

- Créer un fichier `Makefile.local.mk` pour activer le chargement des fichiers
  `docker-compose.yml` des « Addons » souhaités (voir fichier d'exemple
  `Makefile.local.mk.sample.nginx+php-fpm+te`):

```
$ cp Makefile.local.mk.sample.nginx+php-fpm+te Makefile.local.mk
```

- Lancer l'environnement pour faire une install complète :

```
$ make env-start
$ make install-all
```

Note : lors de ce premier lancement, le conteneur `trnasformation-server` ne
sera pas lancé car son installation dépend de l'archive Zip produite par le
`make install-all`. Un arrêt + relance de l'environnement sera nécessaire une
fois les paquets produits pour finaliser son lancement.

- Stopper l'environnement et relancer celui-ci afin de déclencher l'installation
  du conteneur `transformation-server` (car cela nécessite l'archive Zip
  produite par le `make install-all` précédent) :

```
$ make env-stop
$ make env-start
```

Le nouveau conteneur `transformation-server` doit à présent être accessible par
le nom d'hôte `te` depuis le conteneur Anakeen Platform, et le conteneur Anakeen
Platform doit être accessible par callback sur le nom d'hôte `web` (par HTTP
`http://web` ou HTTPS `https://web`).

De l'extérieur (i.e. depuis le poste de développement) le conteneur
`transformation-server` doit être accessible sur `localhost:51968`.

### Script `.devtool/docker-compose`

Le script `.devtool/docker-compose` peut être utilisé en lieu et place de la
commande `docker-compose` afin d'inspecter et manipuler le docker-compose.

C'est une wrapper shell qui passe main à la commande `docker-compose` originale
en s'assurant de lui positionner tous les fichiers de configuration nécessaires
(« addons »).

```
$ ./.devtool/docker-compose up
$ ./.devtool/docker-compose logs web
etc.
```
