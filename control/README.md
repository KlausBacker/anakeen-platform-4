# Anakeen Control

## Description

_Install and control update of Anakeen Platform modules._

Anakeen Control is the official installer program needed to manage the
installation of your Anakeen Platform modules.

## Installation

L'installation d'Anakeen Platform se déroule en 5 étapes :

1. Téléchargement et installation d'Anakeen Control
2. Configuration serveur Apache
3. Configuration serveur Postgresql
4. Initialisation d'Anakeen Platform
5. Installation de modules additionnels

### Installation Anakeen Control

Télécharger le fichier `anakeen-control.tgz`.

Créer le répertoire

`/var/www/html/anakeen`

Configurer le serveur _apache_

```apacheconfig
<VirtualHost *:80>
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html/anakeen/platform/public

	Alias /control /var/www/html/anakeen/control/public
</VirtualHost>
```

```bash
cd /var/www/html/anakeen
tar zxf anakeen-control-current.tar.gz
```

### Initialisation d'Anakeen Platform

Initialiser Anakeen Platform.

Au préalable, il faut déclarer le dépôt à partir duquel les modules seront téléchargés

```bash
./control/anakeen-control registery add myrepo "http://eec-integration.corp.anakeen.com/anakeen/repo/4.0/webinst/"
```

Autres commandes relatives à la gestion des dépôts :

```bash
./control/anakeen-control registery add myrepo  "http://.../"
./control/anakeen-control registery show
./control/anakeen-control registery set-url myrepo "http://..."
./control/anakeen-control registery remove  myrepo
```

Ensuite, il faut initialiser Anakeen Platform.

```bash
./control/anakeen-control init --pg-service=mydatabase
```

Cette commande va demander les paramètres suivants :

- _service de la BD_ : [default "anakeen-platform"]

Lancer l'installation avec la configuration par défaut.
Seuls les modules obligatoires sont installés : Le seul module obligatoire par défaut est le module `smart-data-engine`.

L'option `--default` indique que la valeur par défaut des paramètres sera utilisé si cela n'est pas précisé

```bash
./control/anakeen-control init --default
```

## Manipulation en ligne de commande

### Gestion des modules

Ces commandes suivantes sont exécutées en tâche de fond.
Elle rendent la main après avoir vérifiée les préconditions d'exécution et après avoir lancé
le job en tâche de fond.
Installation d'un module

```bash
./control/anakeen-control install [--default] "my_module"
```

La commande "status" permet d'avoir l'état du gestionnaire de module.
Indique :

- si le serveur web est stop ou start
- si un job est en cours - et dans ce cas les infos sur le job
  - log du job
  - état d'avancement du job
- si une nouvelle version de anakeen-control est dispo
- si les dépôts sont accessibles

Si une installation est en cours, il faut que l'installation soit achevée pour pouvoir lancer une autre opération d'installation ou de mise à jour.

```bash
./control/anakeen-control status  [--json] [--spool=[1000]]
```

L'option `--spool` affiche l'état du job toutes les _n_ ms (1s par défaut) si un job est en cours jusqu'à le fin du job.

La commande "kill" supprime le job en cours

```bash
./control/anakeen-control kill
```

Installation de tous les modules

```bash
./control/anakeen-control install --all [--default]
```

Mise à jour d'un module

L'option `--init` indique qu'il faut installer le module s'il n'est pas déjà installé.

```bash
./control/anakeen-control update [--default] [--init] "my_module"
```

Mise à jour de tous les modules

```bash
./control/anakeen-control update --all [--default]
```

Installation / mise à jour d'un module externe

```bash
./control/anakeen-control install --file=path.app
./control/anakeen-control update [--init] --file=path.app
```

Suppression d'un module

```bash
./control/anakeen-control uninstall "my_module"
```

### Info sur les modules

Liste des modules à mettre à jour

```bash
./control/anakeen-control outdated  [--json]
```

liste tous les modules installés.

```bash
./control/anakeen-control show [--json]
```

Recherche de modules.  
Sans argument, cela liste tous les modules en indiquant leur status (outdated, up-to-date, not installed).
Recherche sur le nom des modules.

```bash
./control/anakeen-control search "my"
```

Informations

Retourne la liste des modules, les paramètres et le nombre d'utilisateurs enregistrés (actif et non actif)

```bash
./control/anakeen-control info [--json]
```

### Autres commandes

Paramètres de Anakeen Control

```bash

./control/anakeen-control get --all
./control/anakeen-control get "<key>"
./control/anakeen-control set "<key>" "<value>"
```

Command

Execute une commande shell sous l'uid de serveur apache dans l'environnement d'exécution

```bash
./control/anakeen-control run "command"
```

### Archive

Sauvegarde une archive (BD + Coffres + control + platform)
Fait en mode asynchrone.

```bash
./control/anakeen-control archive --file="mybackup.tgz" [--with-vault]
```

Restauration d'une archive

```bash
mkdir /var/www/html/backup
cd /var/www/html/backup
unzip -q mybackup.tgz

./control/anakeen-control restore --pg-service=backdb
```

## API REST

La colonne `Job` indique si un job est lancé à l'issue de la requête.

| Méthode | Url                                          | Description                                      | Équivalent commande   | Job ? | REST | CLI |
| ------- | -------------------------------------------- | ------------------------------------------------ | --------------------- | :---: | ---- | --- |
| GET     | /control/api/status                          | Retourne le statut - état du job en cours        | status                |       | OK   | OK  |
| GET     | /control/api/registeries/                    | Liste des dépôts enregistrés                     | registery show        |       | OK   | OK  |
| POST    | /control/api/registeries/[name]?url          | Ajoute un dépôt                                  | registery add         |       | OK   | OK  |
| PUT     | /control/api/registeries/[name]?url          | Modifie un dépôt                                 | registery set-url     |       |      |     |
| DELETE  | /control/api/registeries/[name]              | Enlève un dépôt                                  | registery remove      |       | OK   | OK  |
| GET     | /control/api/modules/[name]                  | Info sur un module                               | search [module]       |       | OK   |     |
| POST    | /control/api/modules/[name]                  | Installation d'un module                         | install [module]      |   X   | OK   | OK  |
| POST    | /control/api/modules/                        | Installation de tous les modules                 | install               |   X   | OK   | OK  |
| PUT     | /control/api/modules/[name]                  | Mets à jour un module                            | update [module]       |   X   | OK   | OK  |
| PUT     | /control/api/modules/                        | Mets à jour tous les modules                     | update                |   X   | OK   | OK  |
| PUT     | /control/api/modules?<file.app>              | Mets à jour le module (.app) donné dans le corps | install --file        |   X   | OK   | OK  |
| DELETE  | /control/api/modules/[name]                  | Suppression d'un module                          | remove                |   X   |      | OK  |
| GET     | /control/api/modules/                        | Liste des modules installés                      | show                  |       | OK   | OK  |
| GET     | /control/api/search/                         | Liste des modules disponibles                    | search                |       | OK   | OK  |
| GET     | /control/api/info                            | Information sur l'état et nombre d'utilisateur   | info                  |       | OK   | OK  |
| GET     | /control/api/parameters/internal/            | Liste des paramètres interne                     | get --all             |       | OK   | OK  |
| GET     | /control/api/parameters/modules/             | Liste des paramètres des modules                 | get --all [--module]  |       | OK   | OK  |
| PUT     | /control/api/parameters/internal/[key]?value | Modifier le paramètre                            | set [--internal][key] |       | OK   | OK  |

Remarque : la gestion des archives n'est pas proposée avec l'api REST.

Installation ou mise à jour d'un module depuis le poste de développement :

```bash
curl --user admin:secret --request POST --data-binary "@/home/users/Anakeen/4/myModule/mymodule-2.0.0.app"  "http://localhost:10180/control/api/modules"
```

## Licence

Merci de vous référer au fichier [LICENSE](LICENSE) pour connaitre les droits
de modification et de distribution du module et de son code source.

La licence s'applique à l'ensemble des codes source du module.

Elle prévaut sur toutes licences qui pourraient être mentionnées dans certains
fichiers.
