# Anakeen platform 4

Soyez le bienvenu dans le mono repo d'Anakeen platform 4

## TL; DR : comment je développe

Quelques prérequis :

- docker
- docker-compose
- un compte sur le repo docker de notre gitlab

Ensuite quelques commandes :

1.  Faire un `make app-autorelease` pour builder les dernières versions des paquets
2.  En cas de première initialisation du docker :
    1. `make init-docker`
    1. Répondre aux questions
    1. patienter pendant l'installation avec la commande `make control-status`
    1. Lorsque la commande indique : `Ready` votre contexte est prêt sur http://localhost:8080/
3.  J'ai rebooté mais je veux tout relancer `make start-env`
4.  J'ai fini ma journée, je veux tout quitter `make stop-env`
5.  Meine gott, j'ai tout cassé, y a plus rien qui marche :
    1. `make clean-env`
    2. Reprendre une première installation

`make run-bash` : Lance un bash en tant root sur le docker PHP  
`make run-sql` : Lance la commande "psql" sur la base de donnée  
`make control-bash` : Lance un bash en tant "www-data" sur le docker PHP 


