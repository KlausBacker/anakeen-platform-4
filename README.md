# Anakeen platform 4

Soyez le bienvenu dans le mono repo d'Anakeen platform 4

## TL; DR : comment je développe

Quelques prérequis :

- [`docker`](https://docs.docker.com/install/linux/docker-ce/ubuntu/) ;
- [`docker-compose`](https://docs.docker.com/compose/install/) ;
- Avoir accès en lecture à la registry Docker qui contient les [images Docker](`https://registry.ap4.anakeen.com/`) utilisées pour le développement de Anakeen Platform 4.

- composer
- yarn (version au moins 1.17)
- npm (+ npm update)
- php-xml
- php-gd
- php-mbstring
- php-zip

Ensuite quelques commandes :

1.  En cas de première initialisation des conteneurs Docker :
    1. - connectez vous à la registry docker d'Anakeen : `docker login registry.ap4.anakeen.com`
    1. `make install-all`
1.  Démarrer le contexte `make start-env`
1.  Fermer le contexte `make stop-env`

Je cherche une commande pour faire quelque chose :

`make help`
