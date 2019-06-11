# Anakeen Control

## Description

_Install and control update of Anakeen Platform modules._

Anakeen Control is the official installer program needed to manage the
installation of your Anakeen Platform modules.

## Documentation

## Installation

### Configuration système

Télécharger le fichier `anakeen-control.tgz`.

Créer le répertoire

`/var/www/html/anakeen`

Configurer apache

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

./control/anakeen-control addregistery myrepo  "http://.../" [--user=<user>] --password=[--password]

./control/anakeen-control addregistery myrepo "http://eec-integration.corp.anakeen.com/anakeen/repo/4.0/webinst/"
```

Autres commandes relatives à la gestion des dépôts :

```bash

./control/anakeen-control listregisteries
./control/anakeen-control removeregistery "http://eec-integration.corp.anakeen.com/anakeen/repo/4.0/webinst/"

```

Ensuite, il faut initialiser Anakeen Platform.

```bash
./control/anakeen-control init --pg-service=mydatabase
```

Cette commande va demander les paramètres suivants :

- _service de la BD_ : [default "anakeen-platform"]

Lancer l'installation avec la configuration par défaut.
Seuls les modules obligatoires sont installés : Le seul module obligatoire par défaut est le module `smart-data-engine`.

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
./control/anakeen-control install "my_module"
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
./control/anakeen-control status  [--json] [--verbose]
```

Le mode "verbose" spool l'état de l'installation en cours tant que c'est pas fini.

La commande "kill" supprime le job en cours

```bash
./control/anakeen-control kill
```

```bash
./control/anakeen-control status  [--json]
```

Installation de tous les modules

```bash
./control/anakeen-control install --all
```

Mise à jour d'un module

```bash
./control/anakeen-control update "my_module"
```

Mise à jour de tous les modules

```bash
./control/anakeen-control update --all [--default]
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

Recherche de modules

liste tous les modules installés.

```bash
./control/anakeen-control list [--json] [--long]
```

Sans argument, cela liste tous les modules en indiquant leur status (outdated, uptodate, not installed).
Recherche sur le nom des modules.

```bash
./control/anakeen-control search "my"
```

Informations

Retourne la liste des modules, les paramètres et le nombre d'utilisateurs enregistrés (actif et non actif)

```bash
./control/anakeen-control info [--json]
```

### Autres comamndes

Activation / désactivation du serveur web

```bash
./control/anakeen-control start
./control/anakeen-control stop
```

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
./control/anakeen-control archive --output="mybackup.tgz" [--without-vault]
```

Restauration d'une archive

```bash
mkdir /var/www/html/backup
cd /var/www/html/backup
tar zxf mybackup.tgz


./control/anakeen-control restore --pg-service=backdb --vault-path="/share/files/"

```

## API REST

| Méthode | Url                                                | Description                                    | Équivalent commande |
| ------- | -------------------------------------------------- | ---------------------------------------------- | ------------------- |
| GET     | /control/api/status                                | Retourne le statut - état du job en cours      | status              |
| GET     | /control/api/registeries/                          | Liste des dépôts enregistrés                   | listregisteries     |
| POST    | /control/api/registeries/[name]?url,login,password | Ajoute un dépôt                                | addregistery        |
| PUT     | /control/api/registeries/[name]?url,login,password | Modifie un dépôt                               | updateregistery     |
| DELETE  | /control/api/registeries/[name]                    | Enlève un dépôt                                | removeregistery     |
| POST    | /control/api/platform/?pg-service                  | Initialise Anakeen Platform                    | init                |
| POST    | /control/api/platform/modules/[name]               | Installation d'un module                       | install             |
| POST    | /control/api/platform/modules/                     | Installation de tous les modules               | install --all       |
| PUT     | /control/api/platform/modules/[name]               | Mets à jour un module                          | update              |
| PUT     | /control/api/platform/modules/                     | Mets à jour tous les modules                   | update --all        |
| DELETE  | /control/api/platform/modules/[name]               | Suppression d'un module                        | uninstall           |
| GET     | /control/api/modules/                              | Liste des modules installés                    | list                |
| GET     | /control/api/search/                               | Liste des modules disponibles                  | search              |
| GET     | /control/api/info                                  | Information sur l'état et nombre d'utilisateur | info                |
| GET     | /control/api/properties/                           | Liste des propriétés                           | get --all           |
| GET     | /control/api/properties/[prop]                     | Valeur de la propriété                         | get                 |
| PUT     | /control/api/properties/[prop]                     | Modifier la propriété                          | set                 |

## Licence

Merci de vous référer au fichier [LICENSE](LICENSE) pour connaitre les droits
de modification et de distribution du module et de son code source.

La licence s'applique à l'ensemble des codes source du module.

Elle prévaut sur toutes licences qui pourraient être mentionnées dans certains
fichiers.
