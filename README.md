# Guide d'utilisation des outils de migration

## Initialiser un nouveau contexte

Sur un contexte "Anakeen Platform" vierge, importer le module  `migration-tools`. Ce module apporte des outils de migration qui sont décrits ci-dessous.

Sur le contexte Dynacase, importer le module `dynacase-migration-4`. Ce module ajoute des routes qui servent à récupérer des données pour la migration.

## Préparation des bases de données

### Configuration de la base "Anakeen Platform"
Les outils de migration utilisent le système de `Foreign Data Table` de postgresql. Ce système permet de communiquer entre 2 bases postgresql.

Il faut d'abord ajouter un `server` distant à la nouvelle base qui s'appelera `dynacase`.
Il faut que le serveur (platform 4) puisse contacter le serveur distant. Il faut surement modifier les accès (pg_hba.conf) du serveur distant pour autoriser la communication depuis la nouveau serveur.
Le plus simple est de mettre une ligne avec votre adresse

    host    all		postgres	192.168.251.143/32		trust

Ensuite il faut créer la connection (se connecter en `postgres`): 

```sql
    (postgres#) create extension postgres_fdw ;
    (postgres#) alter role "anakeen-platform" superuser ; -- need to create mapping and foreign schema
    (postgres#) CREATE SERVER dynacase FOREIGN DATA WRAPPER postgres_fdw OPTIONS (host '192.168.252.134', dbname 'dynacase');
    (postgres#) ALTER SERVER dynacase OWNER TO "anakeen-platform" ;
 ```

Les tables distantes seront enregistrées dans le schéma `dynacase`.

```sql
    (user) create schema dynacase;
    -- create mapping between 2 sql roles (here "postgres" is foreign role,  
    (user) CREATE USER MAPPING FOR "postgres" SERVER dynacase OPTIONS (user 'postgres');
    (user) CREATE USER MAPPING FOR "anakeen-platform" SERVER dynacase OPTIONS (user 'postgres');
```

### Configure les paramètres d'accès au contexte 

Pour exécuter des routes sur le serveur distant en étant "admin":

*  `DYNACASE_URL`
*  `DYNACASE_PASSWORD`

En plus le paramètre "VENDOR" utilisé pour les stubs. Les fichiers générés seront dans le répertoire "VENDOR"

* `VENDOR` : Nom du vendor (CamelCase suggéré)
* `MODULE` : Nom du module : utilisé comme sous-répertoire de vendor.

## Outils de migration

### Déplacer les id des éléments existants

La première étape consiste à reprendre les id des documents existants (même nom logique) de la base dynacase qui sont présents dans la base anakeen-platform.

```bash
./ank.php --route=Migration::InitTransfert --method=POST 
./ank.php --script=cleanContext --full
./ank.php --script=generateDocumentClass
```

### Transférer une table (définition et contenu)

Pour transférer les données d'une table qui est utilisé par une classe DbObj.

```bash
./ank.php --route=Migration::TableTransfert --method=POST --class="<DBOBJCLASS>"
```

Les tables dont on ajoute des données

```bash
for S in  Permission Group DocHisto VGroup Anakeen\\Core\\Account
do
    ./ank.php --route=Migration::TableTransfert --method=POST --class=$S
done 
```

Les tables dont on réinitialise les données

```bash
for S in  QueryDir VaultDiskDirStorage VaultDiskStorage VaultDiskFsStorage DocVaultIndex DocUTag UserToken DocPerm DocPermExt DocTimer DocRel
do
    ./ank.php --route=Migration::TableTransfert --method=POST --query=clear=all --class=$S
done
```


### Transférer une famille vers une structure

4 étapes :

1.  Transfert de la configuration (attribut, enums, propriétés, stub behavior)  
    `Migration::ConfigStructureTransfert`
2.  Régénération de la classe  
    `generateDocumentClass`
3.  Transfert des données
    `Migration::DataElementTransfert`
4.  Transfert de la configuration ui (render parameters, stub render access)    
    `Migration::UiStructureTransfert`


Les familles systèmes (juste le transfert de données) :


```bash
for S in  BASE DIR PDOC PDIR SEARCH PSEARCH FILE IMAGE MAIL DSEARCH MASK PFAM REPORT CVDOC MSEARCH EXEC SSEARCH MAILTEMPLATE TIMER IGROUP IUSER GROUP ROLE HELPPAGE SENTMESSAGE
do
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S
done
```


Les familles du projet dans l'ordre d'héritage (hors famille de workflow): 

```bash
for S in MYFAM1 MYFAM2 
do
    ./ank.php --route=Migration::ConfigStructureTransfert --method=POST --structure=$S && \
    ./ank.php --script=generateDocumentClass --docid=$S && \
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S \
    ./ank.php --route=Migration::UiStructureTransfert --method=POST --structure=$S
done
```

### Transfert workflow

Presque pareil qu'une structure normale sauf que le transfert de la configuration est différent `Migration::WorkflowTransfert`
La classe behavior du workflow est généré avec le graphe récupéré en xml

Exemple : 

```bash
for S in  CCFD_GEP_W_FINANCEMENT_SIDI_PRET CCFD_GEP_W_FINANCEMENT_SIDI_INVESTISSEMENT CCFD_GEP_W_FINANCEMENT_SIDI_GARANTIE CCFD_GEP_SOUTIEN_COURT_CYCLE CCFD_GEP_PARTENARIAT_SIDI_CYCLE WSTRATEGIEDPL CCFD_GEP_SOUTIEN_LONG_CYCLE CCFD_GEP_RAPP_MISSION_CYCLE
do
    ./ank.php --route=Migration::WorkflowTransfert --method=POST --structure=$S && \
    ./ank.php --script=generateDocumentClass --docid=$S && \
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S && \
    ./ank.php --route=Migration::UiStructureTransfert --method=POST --structure=$S
done
```


### Transfert application

La notion d'application n'existe plus en anakeen-platform.
La transfert d'application consiste à récupérer les acl, et paramètre d'une action en utilisant le nom de l'application comme namespace.

Ensuite chaque action entraine la génération d'une route "`/apps/<APPNAME>/<ACTIONNAME>`". Un stub est généré pour chaque action.


```bash
./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCFD
```


### Transfert routes V1

Les routes V1 doivent être réécrite avec le nouveau routeur.
La commande suivante, crée un bouchon php pour chaque route en conservant ces paramètres de configurations. Les paramètres sont enregistrées dans un fichier xml (`apiv1.xml`) de route.

```bash
./ank.php --route=Migration::RoutesV1Transfert --method=POST
```

### Finalisation de la migration de données

Cette commande restore la securité liès aux smaty fields des utilisateurs et groupes.
Elle finalise aussi les données sur les workflows.

```bash
./ank.php --route=Migration::FinalUpdates --method=POST
```


## Récupération de la configuration d'un vendor

Une fois les données migrés d'une base à l'autre, il est possible de récupérer toutes la configuration avec la commande suivant :

```bash
curl -u admin:anakeen http://<MYCONTEXT_URL>/api/v2/migration/modules/<VENDOR>.zip --output ~/Bureau/MyConfig.zip
```

## Utilitaire : Snapshot

First time :

```bash
    createdb -O "anakeen-platform" -T "anakeen-platform" a4tmp 
```
Other times :

```bash
    dropdb "anakeen-platform" && \
    psql -c 'ALTER DATABASE a4tmp RENAME TO "anakeen-platform"' && \
    createdb -O "anakeen-platform" -T "anakeen-platform" a4tmp
```
