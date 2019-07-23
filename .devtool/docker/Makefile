#MAKEFILE dir
MK_DIR := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

include $(MK_DIR)/Makefile.params.mk
-include $(MK_DIR)/Makefile.local.mk

.PHONY: start-env
start-env: | $(VOLUMES_PRIVATE) ## start containers
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Start containers$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) up -d $(DOCKER_COMPOSE_UP_OPTIONS) $(COMPOSE_SERVICES)
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Wait for services to start$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) run --rm --no-deps wait-for-it postgres:5432 -t $(WAIT_FOR_IT_TIMEOUT)
	$(DOCKER_COMPOSE_CMD) run --rm --no-deps wait-for-it php:80 -t $(WAIT_FOR_IT_TIMEOUT)

.PHONY: stop-env
stop-env: ## stop containers
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Stop containers$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) down --remove-orphans

.PHONY: clean-env
clean-env: ## Remove data
	$(DOCKER_COMPOSE_CMD) down --remove-orphans $(DOCKER_COMPOSE_DOWN_OPTIONS)

	echo $(VOLUMES_PRIVATE_DIR)
	echo $(VOLUMES_PRIVATE)

	@$(PRINT_COLOR) "$(COLOR_WARNING)Delete private volumes$(COLOR_RESET)\n"
	rm -rf $(VOLUMES_PRIVATE)

.PHONY: clean-env-full
clean-env-full: DOCKER_COMPOSE_DOWN_OPTIONS += --rmi local ## Remove data and docker compose images
clean-env-full: clean-env

.PHONY: reboot-env
reboot-env: ## Reinitialize environment
	${MAKE} stop-env
	${MAKE} start-env
	${MAKE} mailcatcher-set-params

.PHONY: reset-env
reset-env: ## Reinitialize environment
	${MAKE} clean-env
	${MAKE} start-env
	${MAKE} mailcatcher-set-params
	${MAKE} stop-env

########################################################################################################################
##
## SNAPSHOTS
##
########################################################################################################################

.PHONY: volumes-private
volumes-private: | $(VOLUMES_PRIVATE)

$(VOLUMES_PRIVATE_DIR)/%: | $(VOLUMES_PRIVATE_DIR)
	mkdir -p "$@"

$(VOLUMES_PRIVATE_DIR):
	mkdir -p "$@"

########################################################################################################################
##
## DOCKER UTILITIES
##
########################################################################################################################

.PHONY: log-php-error
log-php-error: ## Shows php error log from php container
	docker logs -f --since 10m $$($(DOCKER_COMPOSE_CMD) ps -q php) 2>&1 >/dev/null | grep --line-buffered '^\[httpd\]' | sed -ur 's/\\n/\n/g'

.PHONY: log-php-access
log-php-access: ## Shows php access log from php container
	docker logs -f --since 10m $$($(DOCKER_COMPOSE_CMD) ps -q php) 2>/dev/null | grep --line-buffered '^\[httpd\]' | sed -ur 's/\\n/\n/g'

.PHONY: log-php-other
log-php-other: ## Shows output from cron manager
	docker logs -f --since 10m $$($(DOCKER_COMPOSE_CMD) ps -q php) 2>&1 | grep -v --line-buffered '^\[httpd\]' | sed -ur 's/\\n/\n/g'

.PHONY: log-postgresql
log-postgresql: ## Shows error from postgres container
	docker logs -f --since 10m $$($(DOCKER_COMPOSE_CMD) ps -q postgres)

.PHONY: docker-prompt-root
docker-prompt-root: ## open bash prompt in php container
	$(DOCKER_COMPOSE_CMD) exec php docker-php-entrypoint /bin/bash

.PHONY: docker-prompt-platform
docker-prompt-platform: ## open wiff prompt
	$(DOCKER_COMPOSE_CMD) exec php $(DOCKER_INTERNAL_CONTROL_DIR_PATH)/anakeen-control run

.PHONY: docker-prompt-postgresql
docker-prompt-postgresql: ## open bash prompt in postgres container
	$(DOCKER_COMPOSE_CMD) exec postgres /bin/bash

.PHONY: docker-prompt-psql
docker-prompt-psql: ## open psql prompt in platform db
	$(DOCKER_COMPOSE_CMD) exec postgres bash -c 'psql -U $$ANK_PG_USER -d $$ANK_PG_BASE'

.PHONY: install-pimp-my-log
install-pimp-my-log: stop-env
	mkdir -p ${VOLUMES_PRIVATE_DIR}/php/var/www/html/logs/pimp-my-log
	cd ${VOLUMES_PRIVATE_DIR}/php/var/www/html/logs/pimp-my-log && ${COMPOSER_BIN} require "potsky/pimp-my-log"

.PHONY: mailcatcher-set-params
mailcatcher-set-params:
	${DOCKER_COMPOSE_CMD} exec php ${DOCKER_INTERNAL_CONTROL_DIR_PATH}/anakeen-control run \
    		"./ank.php --script=setParameter --param=Core::SMTP_FROM --value='${SMTP_FROM}'"
	@echo "\n"
	${DOCKER_COMPOSE_CMD} exec php ${DOCKER_INTERNAL_CONTROL_DIR_PATH}/anakeen-control run \
		"./ank.php --script=setParameter --param=Core::SMTP_HOST --value='${SMTP_HOST}'"
	@echo "\n"
	${DOCKER_COMPOSE_CMD} exec php ${DOCKER_INTERNAL_CONTROL_DIR_PATH}/anakeen-control run \
		"./ank.php --script=setParameter --param=Core::SMTP_PORT --value='${SMTP_PORT}'"
	@echo "\n"
	${DOCKER_COMPOSE_CMD} exec php ${DOCKER_INTERNAL_CONTROL_DIR_PATH}/anakeen-control run \
    		"./ank.php --script=setParameter --param=Core::CORE_URLINDEX --value='${CORE_URLINDEX}'"
	@echo "\n"

###############################################################################
## Utils
###############################################################################

_CONTROL_CMD = $(DOCKER_COMPOSE_CMD) exec php $(DOCKER_INTERNAL_CONTROL_DIR_PATH)/anakeen-control

###############################################################################
##
## MAKEFILE INTERNALS
##
###############################################################################

.PRECIOUS:
.PHONY:

.PHONY: CHECK_%_BIN
CHECK_%_BIN:
	@command -v '$($*_BIN)' >/dev/null 2>&1 || { $(PRINT_COLOR) >&2 "$(WARNING_COLOR)$*_BIN could not be resolved to a known command (you may want to install $($*_BIN))$(RESET_COLOR)\n"; exit 1; }

PRINT_COLOR = printf
COLOR_SUCCESS = \033[1;32m
COLOR_DEBUG = \033[36m
COLOR_HINT = \033[33m
COLOR_WARNING = \033[31m
COLOR_RESET = \033[0m

.DEFAULT_GOAL := help

.PHONY: help
help: HELP_WIDTH = 25
help: ## Show this help message
	@grep -h -E -e '^[a-zA-Z_%-]+:.*?## .*$$' -e '^##: ##' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-$(HELP_WIDTH)s\033[0m %s\n", $$1, $$2}'
