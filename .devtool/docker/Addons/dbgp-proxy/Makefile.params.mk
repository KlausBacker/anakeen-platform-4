PROJECT_DBGP_PROXY_IDE_PORT   ?= 9001
PROJECT_DBGP_PROXY_DEBUG_PORT ?= 9000

DOCKER_COMPOSE_SERVICES += dbgp-proxy
DOCKER_COMPOSE_SERVICES_WAIT_LIST += dbgp-proxy:$(PROJECT_DBGP_PROXY_DEBUG_PORT) dbgp-proxy:$(PROJECT_DBGP_PROXY_IDE_PORT)
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/dbgp-proxy/docker-compose.yml
DOCKER_COMPOSE_ENV += PROJECT_DBGP_PROXY_DEBUG_PORT=$(PROJECT_DBGP_PROXY_DEBUG_PORT)
DOCKER_COMPOSE_ENV += PROJECT_DBGP_PROXY_IDE_PORT=$(PROJECT_DBGP_PROXY_IDE_PORT)

docker-prompt-dbgp-proxy: ## Opens a bash shell in dbgp-proxy's container
	$(DOCKER_COMPOSE_CMD) exec dbgp-proxy /bin/bash

logs-dbgp-proxy-all: ## Shows all dbgp-proxy's log from dbgp-proxy's container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q dbgp-proxy)
