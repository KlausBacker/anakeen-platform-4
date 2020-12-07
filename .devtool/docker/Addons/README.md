# Overview

Par défaut les conteneurs lancés sont : `web`, `postgres` et `mail`.

Le mécanisme de chargement de « Addons » permet d'ajouter de nouveaux conteneurs
ou de surcharger des conteneurs existants.

Exemple de définition d'un nouveau conteneur `php-fpm` :
- [`php-fpm/docker-compose.yml`](./php-fpm/docker-compose.yml)
- [`php-fpm/Dockerfile`](./php-fpm/Dockerfile)
- [`php-fpm/Makefile.params.mk`](./php-fpm/Makefile.params.mk)

Exemple de surcharge du conteneur `web` par le modèle `nginx` :
- [`nginx/docker-compose.yml`](./nginx/docker-compose.yml)
- [`nginx/Dockerfile`](./nginx/Dockerfile)
- [`nginx/Makefile.params.mk`](./nginx/Makefile.params.mk)

Chaque « addon » livre alors :
- un fichier `Dockerfile` qui définit les règles de construction du conteneur ;
- un fichier `docker-compose.yml` d'override pour ajouter un nouveau conteneur
  ou surcharger un conteneur existant ;
- un fichier `Makefile.params.mk` qui ajoute la définition de ce conteneur dans
  les variables décrites ci-dessous (`DOCKER_COMPOSE_SERVICS`,
  `DOCKER_COMPOSE_SERVICES_WAIT_LIST` et `DOCKER_COMPOSE_OVERRIDES`).

Le « chargement » d'un addon se fait alors en incluant son fichier
`.devtool/docker/Addons/{addonName}/Makefile.params.mk` depuis le fichier
`Makefile.local.mk` à la racine du dépôt : voir
[`Makefile.local.mk.sample.nginx+php-fpm+te`](./../../../Makefile.local.mk.sample.nginx+php-fpm+te)

# Makefile variables

La liste des services (nom des conteneurs) exécutés est définie dans la variable
Makefile `DOCKER_COMPOSE_SERVICES` qui contient la liste des noms des conteneurs
à lancer :

```
DOCKER_COMPOSE_SERVICES = web postgres mail
```

La liste des services dont il faut attendre le lancement est définie dans la
variable `DOCKER_COMPOSE_SERVICES_WAIT_LIST` :

```
DOCKER_COMPOSE_SERVICES_WAIT_LIST = postgres:5432 web:80
```

Le chargement de fichiers `docker-compose.yml` additionnels se fait via la
variable `DOCKER_COMPOSE_OVERRIDES` qui contient la liste des options `-f
<file>` des fichiers `docker-compose.yml` à charger en supplément.

```
DOCKER_COMPOSE_OVERRIDES += -f .devtool/docker/Addons/transformation-server/docker-compose.yml
```
