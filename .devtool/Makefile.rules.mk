########################################################################################################################
##
## DOCKER UTILITIES
##
########################################################################################################################

.PHONY: logs-php-error
logs-php-error: ## Shows php error log from php container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q php) 2>&1 >/dev/null | sed -ur 's/\\n/\n/g'

.PHONY: logs-php-access
logs-php-access: ## Shows php access log from php container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q php) 2>/dev/null | sed -ur 's/\\n/\n/g'

.PHONY: logs-postgresql
logs-postgresql: ## Shows error from postgres container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q postgres) | sed -ur 's/\\n/\n/g'

.PHONY: docker-prompt-php
docker-prompt-php: ## open bash prompt in php container
	$(DOCKER_COMPOSE_CMD) exec php docker-php-entrypoint /bin/bash

.PHONY: docker-prompt-control
docker-prompt-control: ## open anakeen-control prompt
	$(_CONTROL_SHELL_CMD)

.PHONY: docker-prompt-postgresql
docker-prompt-postgresql: ## open bash prompt in postgres container
	$(DOCKER_COMPOSE_CMD) exec postgres /bin/bash

.PHONY: docker-prompt-psql
docker-prompt-psql: ## open psql prompt in platform db
	$(DOCKER_COMPOSE_CMD) exec postgres bash -c 'psql -U $$ANK_PG_USER -d $$ANK_PG_BASE'

$(VOLUMES_PHP_CONTROL): | $(CONTROL_ARCHIVE)
	mkdir -p "$@/.."
	unzip -qo "$(CONTROL_ARCHIVE)" -d "$(@D)"
	mkdir -p $(VOLUMES_PHP_CONTROL_CONF)
	touch "$@"

$(BUILD_DIR):
	mkdir -p "$@"

$(VOLUMES_PRIVATE_DIR)/%:
	mkdir -p "$@"

$(VOLUMES_PHP_CONTROL_CONF)/contexts.xml: | _env-start
	@$(PRINT_COLOR) "$(COLOR_INFO)[I]$@ not found, initializing context$(COLOR_RESET)\n"
	$(_CONTROL_CMD) status --format json | jq -e '.status != "Not Initialized"' > /dev/null \
		|| $(_CONTROL_CMD) init --pg-service=platform --password=$(CONTEXT_PASSWORD)
	$(_CONTROL_CMD) registry show --format json | jq -e '.[] | select(.name=="$(LOCAL_REPO_NAME)").name == "$(LOCAL_REPO_NAME)"' > /dev/null \
		|| $(_CONTROL_CMD) registry add $(LOCAL_REPO_NAME) $(DOCKER_INTERNAL_PHP_REPO_PATH)
	#$(_CONTROL_CMD) install --no-interaction --no-ansi
	#$(_CONTROL_CMD) update --no-interaction --no-ansi
	@$(PRINT_COLOR) "$(COLOR_INFO)[I]context initialized$(COLOR_RESET)\n"

.PHONY: _env-start
_env-start: | $(VOLUMES_PRIVATE)
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Start containers$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) up -d $(DOCKER_COMPOSE_UP_OPTIONS) $(DOCKER_COMPOSE_SERVICES)
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Wait for services to start$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) run --rm --no-deps wait-for-it postgres:5432 -t $(WAIT_FOR_IT_TIMEOUT)
	$(DOCKER_COMPOSE_CMD) run --rm --no-deps wait-for-it php:80 -t $(WAIT_FOR_IT_TIMEOUT)

.PHONY: test-mail-set-params
test-mail-set-params: ## configure context to use local mail catcher
	$(_CONTROL_SHELL_CMD) "./ank.php --script=setParameter --param=Core::SMTP_FROM --value='$(SMTP_FROM)'"
	@echo "\n"
	$(_CONTROL_SHELL_CMD) "./ank.php --script=setParameter --param=Core::SMTP_HOST --value='$(SMTP_HOST)'"
	@echo "\n"
	$(_CONTROL_SHELL_CMD) "./ank.php --script=setParameter --param=Core::SMTP_PORT --value='$(SMTP_PORT)'"
	@echo "\n"
	$(_CONTROL_SHELL_CMD) "./ank.php --script=setParameter --param=Core::CORE_URLINDEX --value='$(CORE_URLINDEX)'"
	@echo "\n"

.PHONY: env-list-ports
env-list-ports: ## list exposed ports
	@echo "Exposed ports:"
	@for service in $(DOCKER_COMPOSE_SERVICES); do \
		echo "- $$service:"; \
		$(DOCKER_BIN) port $$($(DOCKER_COMPOSE_CMD) ps -q $$service); \
	done

###############################################################################
## Utils
###############################################################################

_CONTROL_CMD = $(DOCKER_COMPOSE_CMD) exec php $(DOCKER_INTERNAL_PHP_CONTROL_DIR_PATH)/anakeen-control
_CONTROL_SHELL_CMD = $(_CONTROL_CMD) run
_CONTROL_ANK_CMD = $(_CONTROL_SHELL_CMD) ./ank.ph

###############################################################################
##
## MAKEFILE INTERNALS
##
###############################################################################

MAKEFLAGS += --no-builtin-rules
.SUFFIXES:
.PRECIOUS:

.DEFAULT_GOAL := help

.PHONY: help
help: HELP_WIDTH = 25
help: ## Show this help message
	@grep -h -E -e '^[a-zA-Z_%-]+:.*?## .*$$' -e '^##: ##' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-$(HELP_WIDTH)s\033[0m %s\n", $$1, $$2}'
