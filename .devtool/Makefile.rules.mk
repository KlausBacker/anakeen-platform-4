########################################################################################################################
##
## DOCKER UTILITIES
##
########################################################################################################################

.PHONY: logs-php-error
logs-php-error: ## Shows php error log from php container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q $(CONTAINER_PHP)) 2>&1 >/dev/null | sed -ur 's/\\n/\n/g'

.PHONY: logs-php-access
logs-php-access: ## Shows php access log from php container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q $(CONTAINER_PHP)) 2>/dev/null | sed -ur 's/\\n/\n/g'

.PHONY: logs-postgresql
logs-postgresql: ## Shows error from postgres container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q postgres) | sed -ur 's/\\n/\n/g'

.PHONY: docker-prompt-php
docker-prompt-php: ## open bash prompt in php container
	$(DOCKER_COMPOSE_CMD) exec $(CONTAINER_PHP) docker-php-entrypoint /bin/bash

.PHONY: docker-prompt-control
docker-prompt-control: ## open anakeen-control prompt
	$(_CONTROL_SHELL_CMD)

.PHONY: docker-prompt-postgresql
docker-prompt-postgresql: ## open bash prompt in postgres container
	$(DOCKER_COMPOSE_CMD) exec postgres /bin/bash

.PHONY: docker-prompt-psql
docker-prompt-psql: ## open psql prompt in platform db
	$(DOCKER_COMPOSE_CMD) exec postgres bash -c 'psql -U $$ANK_PG_USER -d $$ANK_PG_BASE'

.PHONY: docker-compose-env
docker-compose-env: ## Generate Bourne shell commands to export variables suitable for use by ".devtools/docker-compose" or any external introspection
	@echo export $(DOCKER_COMPOSE_ENV)
	@echo export DOCKER_COMPOSE_CMD=\"$(DOCKER_COMPOSE_CMD)\"
	@echo export DOCKER_COMPOSE_BASE_FILE=\"$(DOCKER_COMPOSE_BASE_FILE)\"
	@echo export DOCKER_COMPOSE_SERVICES=\"$(DOCKER_COMPOSE_SERVICES)\"
	@echo export DOCKER_COMPOSE_OVERRIDES=\"$(DOCKER_COMPOSE_OVERRIDES)\"
	@echo export PROJECT_NAME=\"$(PROJECT_NAME)\"
	@echo export _CONTROL_CMD=\"$(_CONTROL_CMD)\"

$(VOLUMES_WEBROOT_CONTROL): | $(CONTROL_ARCHIVE)
	mkdir -p "$@/.."
	unzip -qo "$(CONTROL_ARCHIVE)" -d "$(@D)"
	mkdir -p $(VOLUMES_WEBROOT_CONTROL_CONF)
	touch "$@"

$(BUILD_DIR):
	mkdir -p "$@"

$(VOLUMES_PRIVATE_DIR)/%:
	mkdir -p "$@"

$(VOLUMES_WEBROOT_CERTS):
	mkdir -p "$@"

$(VOLUMES_WEBROOT_CONTROL_CONF)/contexts.xml: | _env-start
	@$(PRINT_COLOR) "$(COLOR_INFO)[I]$@ not found, initializing context$(COLOR_RESET)\n"
	$(_CONTROL_CMD) status --format json | jq -e '.status != "Not Initialized"' > /dev/null \
		|| $(_CONTROL_CMD) init --pg-service=platform --password=$(CONTEXT_PASSWORD)
	$(_CONTROL_CMD) registry show --format json | jq -e '.[] | select(.name=="$(LOCAL_REPO_NAME)").name == "$(LOCAL_REPO_NAME)"' > /dev/null \
		|| $(_CONTROL_CMD) registry add $(LOCAL_REPO_NAME) $(DOCKER_INTERNAL_WEBROOT_REPO_PATH)
	#$(_CONTROL_CMD) install --no-interaction --no-ansi
	#$(_CONTROL_CMD) update --no-interaction --no-ansi
	@$(PRINT_COLOR) "$(COLOR_INFO)[I]context initialized$(COLOR_RESET)\n"

.PHONY: _env-start
_env-start: | $(VOLUMES_PRIVATE) $(BUILD_DIR)
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Start containers$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) up -d $(DOCKER_COMPOSE_UP_OPTIONS) $(DOCKER_COMPOSE_SERVICES)
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Wait for services to start$(COLOR_RESET)\n"
	for SERVICE in $(DOCKER_COMPOSE_SERVICES_WAIT_LIST); do \
		echo "[+] Waiting for '$${SERVICE}'..."; \
		$(DOCKER_COMPOSE_CMD) run --rm --no-deps wait-for-it $${SERVICE} -t $(WAIT_FOR_IT_TIMEOUT); \
		echo "[+] Done."; \
	done

.PHONY: _env-stop
_env-stop:
	@$(PRINT_COLOR) "$(COLOR_DEBUG)[D][$@] Stop containers$(COLOR_RESET)\n"
	$(DOCKER_COMPOSE_CMD) down --remove-orphans $(DOCKER_COMPOSE_DOWN_OPTIONS)

########################################################################################################################
##
## SNAPSHOTS
##
########################################################################################################################

.PHONY: snapshot
snapshot: ## take a snapshot of current state
	$(MAKE) snapshots-add-auto-$(shell date "+%Y%m%d-%H%M%S")

.PHONY: _snapshot
_snapshot:
	$(MAKE) _snapshots-add-auto-$(shell date "+%Y%m%d-%H%M%S")

.PHONY: snapshots-list
snapshots-list: ## Display a list of all available snapshots
	@$(PRINT_COLOR) "available snapshots (in $(VOLUMES_PHP_SNAPSHOT_DIR)):\n"
	@for file in $$(ls -t1 $(VOLUMES_PHP_SNAPSHOT_DIR)/*.zip); do \
		echo "  - $$(basename $$file '.zip') ($$(stat -c '%y' $$file); $$(du -sh $$file| cut -f -1))"; \
		echo "    - revert with [make snapshots-revert-$$(basename $$file '.zip')]"; \
		echo "    - delete with [make snapshots-delete-$$(basename $$file '.zip')]"; \
	done

.PHONY: snapshots-add-%
snapshots-add-%: ## Create a new snapshot named %
	@[ -f "$(VOLUMES_PHP_SNAPSHOT_DIR)/$*.zip" ] \
		&& { $(PRINT_COLOR) >&2 "$(COLOR_WARNING)$(VOLUMES_PHP_SNAPSHOT_DIR)/$*.zip already exists$(COLOR_RESET)\n"; exit 1; } \
		|| $(MAKE) _snapshots-add-$*

.PHONY: _snapshots-add-%
_snapshots-add-%: _env-start
	mkdir -p "$(VOLUMES_PHP_SNAPSHOT_DIR)"
	$(_CONTROL_CMD) archive --dry-run "$(DOCKER_INTERNAL_PHP_SNAPSHOTS_PATH)/$*.zip"
	$(_CONTROL_CMD) dojob -v
	@$(PRINT_COLOR) "$(COLOR_NOTICE)New snapshot created: $*.zip\n"
	@$(MAKE) snapshots-list
	@$(PRINT_COLOR) "$(COLOR_RESET)\n"

.PHONY: snapshots-revert-%
snapshots-revert-%: | $(VOLUMES_PHP_CONTEXT) ## revert to snapshot %
	[ -f "$(VOLUMES_PHP_SNAPSHOT_DIR)/$*.zip" ] || \
		{ \
			$(PRINT_COLOR) >&2 "$(COLOR_WARNING)$*.zip does not exists.\n$(COLOR_RESET)$(COLOR_HINT)"; \
			$(MAKE) --no-print-directory snapshots-list >&2; \
			$(PRINT_COLOR) >&2 "$(COLOR_RESET)"; \
			exit 1; \
		}
	@$(PRINT_COLOR) "$(COLOR_DEBUG)Revert snapshot from $*.zip$(COLOR_RESET)\n"
	$(MAKE) _env-stop
	rm -rf "$(VOLUMES_PHP_CONTEXT)" "$(VOLUMES_PHP_VAULTS)" "$(VOLUMES_PHP_CONTROL)"
	unzip -qod "$(VOLUMES_PHP_BASE)" "$(VOLUMES_PHP_SNAPSHOT_DIR)/$*.zip"
	$(MAKE) _env-start
	$(_CONTROL_CMD) restore --pg-service=platform --force-clean --dry-run
	$(_CONTROL_CMD) dojob -v
	$(DOCKER_COMPOSE_CMD) exec php rm "$(DOCKER_INTERNAL_PHP_BASE)/core_db.pg_dump"
	@$(PRINT_COLOR) "$(COLOR_SUCCESS)"
	@$(MAKE) --no-print-directory env-list-ports
	@$(PRINT_COLOR) "$(COLOR_RESET)"

.PHONY: snapshots-delete-%
snapshots-delete-%: ## Delete snapshot %
	rm -i $(VOLUMES_PHP_SNAPSHOT_DIR)/$*.zip

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
	done; \
	if [ -f $(VOLUMES_WEBROOT_CERTS)/rootCA.pem ]; then \
		echo ""; \
		echo "Root CA certificate: $(VOLUMES_WEBROOT_CERTS)/rootCA.pem"; \
		echo ""; \
	fi;

###############################################################################
## Utils
###############################################################################

_CONTROL_CMD = $(DOCKER_COMPOSE_CMD) exec $(CONTAINER_PHP) $(DOCKER_INTERNAL_WEBROOT_CONTROL_DIR_PATH)/anakeen-control
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
