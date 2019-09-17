# Anakeen platform 4

Soyez le bienvenu dans le mono repo d'Anakeen platform 4

## TL; DR : comment je développe

Quelques prérequis :

- [`docker`](https://docs.docker.com/install/linux/docker-ce/ubuntu/) ;
- [`docker-compose`](https://docs.docker.com/compose/install/) ;
- Avoir accès en lecture à la registry Docker qui contient les [images Docker](https://gitlab.anakeen.com/customers/docker-images/dev-images) utilisées pour le développement de Anakeen Platform 4.

- composer
- yarn (version au moins 1.17)
- npm (+ npm update)
- php-xml
- php-gd
- php-mbstring
- php-zip

Ensuite quelques commandes :

1.  Faire un `make app-autorelease` pour builder les dernières versions des paquets
2.  En cas de première initialisation des conteneurs Docker :
    1. Se logger sur la registry d'images Docker de GitLab : `docker login gitlab.anakeen.com:4567` (avec login et mot de passe de votre compte [GitLab Anakeen](https://gitlab.anakeen.com/))
    1. `make init-docker`
    1. Répondre aux questions
    1. patienter pendant l'installation avec la commande `make control-status`
    1. Lorsque la commande indique : `Ready` votre contexte est prêt sur http://localhost:8080/
3.  J'ai rebooté mais je veux tout relancer `make start-env`
4.  J'ai fini ma journée, je veux tout quitter `make stop-env`
5.  Meine gott, j'ai tout cassé, y a plus rien qui marche :
    1. `make clean-env`
    2. Reprendre une première installation

`make run-bash` : Lance un bash en tant que root dans le conteneur Docker PHP
`make run-sql` : Lance la commande "psql" sur la base de donnée  
`make control-bash` : Lance un bash en tant que "www-data" dans le conteneur Docker PHP

Pour mettre en place pimp-my-log

1. Lancer `make -C .devtool/docker install-pimp-my-log`
2. Copier le contenu du fichier [001-anakeen-logs.conf.default](.devtool/docker/Docker/Volumes/php/etc/apache2/sites-enabled/custom-vhost/001-anakeen-logs.conf.default) en `001-anakeen-logs.conf`
3. `make start-env`
