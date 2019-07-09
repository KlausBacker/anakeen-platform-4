# Simple project boilerplate

## tl;dr

* Copy the 

Replace all occurences of `a4` by your project name:
`git grep -lI 'a4' | xargs sed -i -e 's/a4/test/'`

`make start-env`

### Make commands

The provided Makefile is self documented: simply run `make` to get the list of commands.

### Customize makefiles

- `Makefile`: main makefile
- `Makefile.params.mk`: parameters, included at top of `Makefile`. Shared in the repo
- `Makefile.local.mk`: optional customizations per user, included at top of `Makefile`. Excluded from repo.

#### Utils

Example of `Makefile.local.mk` using utilities:

```make
init:
	$(_WIFF_CMD) create context $(CONTEXT_NAME) $(DOCKER_INTERNAL_CONTEXT_PATH)
	$(_WIFF_WITH_CONTEXT) repository enable docker
	$(_WIFF_WITH_CONTEXT) module install --unattended smart-data-engine
```

## Detailed explanations

### Use images in your projects

Images make use of [`ONBUILD`](https://docs.docker.com/engine/reference/builder/#onbuild) instructions.
Thus you **have to** use them as base images (use `build.context` in docker-compose)

### user id

Images are designed to run as current user id, thus volumes can be managed outside of the container.

### PHP image

This image comes with

- apache
- php and php modules
- xdebug
- cli dependencies
- supercronic as cron manager
- PG_SERVICE configuration
- vhost configuration

#### Configuration

- PG_SERVICE

  Default pg_service is named `docker`

- default vhost (`000-anakeen.conf`)
  - `DocumentRoot`: `/var/www/html/platform/public`
  - aliases `/control` and `/anakeen-control`: /var/www/html/control
  - can be extended by following files:
    - `/etc/apache2/sites-enabled/custom-vhost/*.conf`:
      can define new vhosts or change global apache configuration
    - `/etc/apache2/sites-enabled/custom-platform/*.conf`:
      parsed inside directory `/var/www/html/platform`
    - `/etc/apache2/sites-enabled/custom-control/*.conf`:
      parsed inside directory `/var/www/html/control`

#### VOLUMES

- `$ANK_CONTEXT_PATH`
- `$ANK_CONTROL_PATH/conf`
- `/var/spool/cron/crontabs`

#### ONBUILD VARS

Those vars are only used during build time

- `ANK_RUN_UID` (default value: `33`): uid of the user under which apache will run

  It is recommended to set it to your current uid

- `ANK_RUN_GID` (default value: `33`): gid of the group under which apache will run

  It is recommended to set it to your current gid

- `ANK_CONTROL_URL` (default value: `http://eec-integration.corp.anakeen.com/anakeen/repo/4.0/control/anakeen-control-current.tar.gz`): Url of control tar.gz file

  During image build, Anakeen control is downloaded from this url.
  set it to an empty value if you want to prevent control from being downloaded.
  (to use a volume instead for example)

#### ENV VARS

- `ANK_CONTROL_PATH` (default value: `/var/www/html/control`): path to control dir
- `ANK_CONTEXT_PATH` (default value: `/var/www/html/platform`): path to context dir
- `ANK_RUN_UID`: persisted value of the `ANK_RUN_UID` ONBUILD var
  (can be used in your dockerfile if required)
- `ANK_RUN_GID`: persisted value of the `ANK_RUN_GID` ONBUILD var
  (can be used in your dockerfile if required)
- `APACHE_RUN_USER`: extrapolated value of the `ANK_RUN_UID` ONBUILD var
  (ie: prefixed with `#` for apache usage - see <https://httpd.apache.org/docs/current/mod/mod_unixd.html#User>)
- `APACHE_RUN_GROUP`: extrapolated value of the `ANK_RUN_GID` ONBUILD var
  (ie: prefixed with `#` for apache usage - see <https://httpd.apache.org/docs/current/mod/mod_unixd.html#Group>)

### POSTGRES image

This image comes with

- postgresql
- user and empty database initialized

#### Configuration

- files in `/docker-entrypoint-initdb.d`
  - `*.sh`:
    - sourced if not executable
    - executed otherwise
  - `*.sql`
    imported

#### VOLUMES

- /var/lib/postgresql/data

#### ONBUILD VARS

- `ANK_PG_USER` (default value: `anakeen`): postgresql user
- `ANK_PG_PASSWORD` (default value: `anakeen`): postgresql user password
- `ANK_PG_BASE` (default value: `platform`): postgresql user

#### ENV VARS

- `ANK_PG_USER`: persisted value of ONBUILD VAR
- `ANK_PG_PASSWORD`: persisted value of ONBUILD VAR
- `ANK_PG_BASE`: persisted value of ONBUILD VAR
