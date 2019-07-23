# Name used in docker compose to derivate containers names
PROJECT_NAME = monorepo
CONTEXT_NAME = platform

###############################################################################
## Paths
###############################################################################
DEVTOOLS_DIR = ./

DOCKER_DIR = $(DEVTOOLS_DIR)/Docker

VOLUMES_DIR = $(DOCKER_DIR)/Volumes
VOLUMES_PRIVATE_DIR = $(VOLUMES_DIR)/_private

DOCKER_INTERNAL_CONTROL_DIR_PATH = /var/www/html/control
DOCKER_INTERNAL_CONTEXT_PATH = /var/www/html/platform

VOLUMES_PRIVATE = $(VOLUMES_PRIVATE_DIR)/postgres/var/lib/postgresql/data
VOLUMES_PRIVATE += $(VOLUMES_PRIVATE_DIR)/postgres/tmp/share
VOLUMES_PRIVATE += $(VOLUMES_PRIVATE_DIR)/php/var/spool/cron/crontabs
VOLUMES_PRIVATE += $(VOLUMES_PRIVATE_DIR)/php/$(DOCKER_INTERNAL_CONTEXT_PATH)
VOLUMES_PRIVATE += $(VOLUMES_PRIVATE_DIR)/php/$(DOCKER_INTERNAL_CONTROL_DIR_PATH)/conf
VOLUMES_PRIVATE += $(VOLUMES_PRIVATE_DIR)/php/$(DOCKER_INTERNAL_CONTROL_DIR_PATH)/var
VOLUMES_PRIVATE += $(VOLUMES_PRIVATE_DIR)/php/tmp/share
VOLUMES_PRIVATE +=


###############################################################################
## Binaries
###############################################################################
DOCKER_BIN = docker
DOCKER_COMPOSE_BIN = docker-compose
DOCKER_COMPOSE_CMD = $(COMPOSE_ENV) $(DOCKER_COMPOSE_BIN) -p $(PROJECT_NAME) -f "$(DOCKER_DIR)/docker-compose.yml"

###############################################################################
## Various
###############################################################################

COMPOSE_UID = $$(id -u)
COMPOSE_GID = $$(id -g)
COMPOSE_ENV = COMPOSE_UID=$(COMPOSE_UID) COMPOSE_GID=$(COMPOSE_GID)
DOCKER_COMPOSE_UP_OPTIONS =
DOCKER_COMPOSE_DOWN_OPTIONS =
COMPOSE_SERVICES = php postgres mailcatcher
WAIT_FOR_IT_TIMEOUT = 15

################################
## MAILCATCHER
################################
SMTP_HOST=mailcatcher
SMTP_PORT=1025
SMTP_FROM=noreply@example.net
CORE_URLINDEX=http://localhost:8080/

init:
	$(DOCKER_COMPOSE_CMD) exec php unzip -oq /var/www/html/localRepo/control/anakeen-control-latest.zip -d /var/www/html/
	$(DOCKER_COMPOSE_CMD) exec php chown -R "www-data:" "/var/www/html/control/"
	$(_CONTROL_CMD) init --pg-service=platform --password=anakeen
	$(DOCKER_COMPOSE_CMD) exec php /bin/bash -c "/var/www/html/control/anakeen-control  _completion --generate-hook --shell-type=bash > /etc/bash_completion.d/anakeen-control.bash"

register-local-repo:
	$(_CONTROL_CMD) registry add localRepo /var/www/html/localRepo/

install:
	$(_CONTROL_CMD) install --no-interaction