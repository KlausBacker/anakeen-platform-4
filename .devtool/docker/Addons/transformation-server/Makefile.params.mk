PROJECT_TRANSFORMATION_SERVER_PHP_VERSION ?= 7.3-2

DOCKER_COMPOSE_SERVICES += transformation-server
DOCKER_COMPOSE_SERVICES_WAIT_LIST += transformation-server:51968
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/transformation-server/docker-compose.yml
DOCKER_COMPOSE_ENV += PROJECT_TRANSFORMATION_SERVER_PHP_VERSION=$(PROJECT_TRANSFORMATION_SERVER_PHP_VERSION)

docker-prompt-transformation-server: ## open bash prompt in PHP-FPM container
	$(DOCKER_COMPOSE_CMD) exec transformation-server /bin/bash

logs-transformation-server-all: ## Shows all transformation-server's log
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q transformation-server)
