# PHPStorm + Xdebug + dbgp-proxy

Le conteneur `dbgp-proxy` permet de faire le lien entre le conteneur PHP
(conteneur Apache mod-php ou conteneur PHP-FPM) et le poste du développeur pour
l'utlisation du débogage et du profilage de Xdebug.

## Configuration conteneurs

- Le conteneur qui exécute le code PHP doit être configuré pour avoir
  l'extension PHP Xdebug et que Xdebug soit configuré pour se connecter au
  conteneur `dbgp-proxy` :

  ```ini
  zend_extension="xdebug.so"
  
  xdebug.mode=debug,profile
  xdebug.start_with_request=trigger
  xdebug.client_host="dbgp-proxy"
  xdebug.client_port=9000
  xdebug.remote_handler="dbgp"
  ;xdebug.log="/tmp/xdebug.log"
  xdebug.output_dir="/tmp"
  ```

- Le conteneur `dbgp-proxy` doit être lancé et être en écoute sur le port
  `:9000` pour la réception du Xdebug du conteneur PHP, et sur le port `:9001`
  pour recevoir la demande d'enregistrement de l'IDE PHPStorm.

## Configuration PHPStorm

Configurer le port d'écoute pour le debug Xdebug :

- Menu `File` > `Settings`
- Ouvrir `Languages & Frameworks` > `PHP` > `Debug`
- Dans la section `Xdebug` :
  - Renseigner `Debug port` avec un port d'écoute TCP libre sur votre poste de
    développement : e.g. `9009` (voir liste des ports TCP en écoute avec `ss
    -tnlp`)
  - Cocher `[x] Can accept external connections`
- Ouvrir `Languages & Frameworks` > `PHP` > `Debug` > `DBGp Proxy`
- Renseigner les paramètres :
  - `IDE key` : une chaine de caractère permettant de vous identifier dans le
    ou plusoieurs personnes seraient aménes à debugger votre conteneur PHP
    (e.g.  `john.doe`)
    - `Host` : `localhost`
    - `Port` : `9001` (le port d'écoute IDE du conteneur `dbgp-proxy`)

## Activation débogage dans PHPStorm

Mettre PHPStorm en écoute de connexion de debug PHP :

- Menu `Run` > `Start Listening for PHP Debug Connections`
- PHPStorm ouvre alors en écoute le port configuré précédemment avec `Debug
  port` (e.g. `9009`) :

  ```
  $ ss -tnlp | grep :9009
  LISTEN  0  50  :::9009  :::*  users:(("java",pid=16358,fd=107))
  ```

Enregistrer PHPStorm auprès du contenu `dbgp-proxy` :

- Menu `Tools` > `DBGp Proxy` > `Register IDE`
- PHPStorm va faire une connexion sur le port IDE du conteneur `dbgp-proxy`
  pour s'y enregistrer. Exemple de log du conteneur `dbgp-proxy` lors de cet
  enregistrement :

  ```
  $ .devtools/docker-compose logs -f dbgp-proxy
  [...]
  dbgp-proxy_1             | 12:54:47.399 [info] [server] Start new client connection from 172.29.255.209:58328
  dbgp-proxy_1             | 12:54:47.400 [info] [proxyinit] [john.doe] Added connection for IDE Key 'john.doe': 172.29.255.209:9009
  dbgp-proxy_1             | 12:54:47.401 [info] [server] Closing client connection from 172.29.255.209:58328
  ```

Note :
- Une fois la séance de débogage terminée, vous pouvez désenregistrer l'IDE du
  DBGp proxy (`Tools` > `DBGp Proxy` > `Cancel IDE Registration`) et couper
  l'écoute du port de debug (`Run` > `Stop Listening for PHPDebug
  Connections`).

## Débogage

Le débogage permet de mettre la requête HTTP en arrêt, d'ouvrir le code source
correspondant au fichier PHP traité dans PHPStorm et de faire une exécution
pas-à-pas du programme.

### En mode Web

- Lancer la requête HTTP avec une varialble (GET ou POST)
  `XDEBUG_SESSION_START=<IDE_KEY>` (avec `<IDE_KEY>` étant l'identifiant
  configuré dans le paramètre PHPStorm `IDE key` ci-dessus) :
  
  ```
  $ curl -D - http://localhost:8080/index.php?XDEBUG_SESSION_START=john.doe
  ```

- Le conteneur PHP va alors faire une connexion sur le port client (:9000) du conteneur `dbgp-proxy` qui va alors retransmettre la demande à PHPStorm via la connexion précédemment enregistrée. Exempel de logs sur le contenur `dbgp-proxy` :
    
  ```
  $ .devtools/docker-compose logs -f dbgp-proxy
  [...]
  dbgp-proxy_1             | 13:18:14.106 [info] [server] Start new server connection from 172.29.255.215:46364
  dbgp-proxy_1             | 13:18:14.109 [info] [proxy-client] [john.doe] Found connection for IDE Key 'john.doe': 172.29.255.209:9009
  dbgp-proxy_1             | 13:18:14.109 [info] [proxy-client] [john.doe] Connecting to 172.29.255.209:9009
  dbgp-proxy_1             | 13:18:14.110 [info] [proxy-client] [john.doe] IDE connected
  dbgp-proxy_1             | 13:18:14.110 [info] [proxy-client] [john.doe] Init forwarded, start pipe
  ```

- PHPStorm reçoit alors la demande de debug sur son port `Debug port` (:9009) et ouvre, ou demande, le fichier source correspondant en mode debug.

### En mode CLI

- Exécuter le script PHP avec les variales d'environnement suivantes :
  - `XDEBUG_SESSION_START=<IDE_KEY>` avec `<IDE_KEY>` étant l'identifiant
    configuré dans le paramètre PHPStorm `IDE key` ci-dessus) :
  - `PHP_IDE_CONFIG="serverName=<SERVER_NAME>"` avec `<SERVER_NAME>` étant le
    nom du serveur dans les paramètres `Languages & Frameworks` > `PHP` >
    `Server` > `Name`.

  ```
  $ XDEBUG_SESSION_START=john.doe PHP_IDE_CONFIG="serverName=_" ./ank.php [...]
  ```

## Profilage

Le profilage permet de faire une trace des appels et de la consommation
mémoire au format `cachegrind`.

### En mode Web

- Lancer la requête HTTP avec une variable (GET ou POSt) `XDEBUG_PROFILE` :

  ```
  $ curl -D - http://localhost:8080/index.php?XDEBUG_SESSION_START=john.doe
  ```

- Le ficher `cachegrind.out.*` de profilage est alors produit dans le
  répertoire référencé par `xdebug.output_dir` (e.g. `/tmp`) :

  ```
  $ ls -alt /tmp/cachegrind.out.* | head -1
  -rw-r--r-- 1 www-data www-data 434511 Dec  3 15:20 /tmp/cachegrind.out.2103
  ```

### En mode CLI

- Exécuter le script PHP avec la variable d'environnement `XDEBUG_PROFILE=1` :

  ```
  $ XDEBUG_PROFILE=1 ./ank.php [...]
  ```

- Le ficher `cachegrind.out.*` de profilage est alors produit dans le
  répertoire référencé par `xdebug.output_dir` (e.g. `/tmp`) :

  ```
  $ ls -alt /tmp/cachegrind.out.* | head -1
  -rw-r--r-- 1 www-data www-data 434848 Dec  3 15:25 /tmp/cachegrind.out.2110
  ```

## Trouleshooting

### Connections overview

Aperçu des différentes connexions et ports utilisés :

```
  PHPStorm        PHP Storm Xdebug listener (:9009)
     |               ^
     |               |
     |  .------------'
     | /
  Plugin PHPStorm DBGp proxy ("Register IDE")
     |
     v                                    Poste développeur
  --(localhost:9001)----------------------------------------
     |                                Réseau docker-compose
     |
     '--> dbgp-proxy:9001 (IDE port)
     .--> dbgp-proxy:9000 (Client port)
     |
     | Xdebug breakpoint
     |
     '-- php-fpm
```

