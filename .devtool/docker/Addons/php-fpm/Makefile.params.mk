CONTAINER_PHP = php-fpm

PROJECT_FPM_PHP_VERSION ?= 7.4-latest

DOCKER_COMPOSE_SERVICES += php-fpm
DOCKER_COMPOSE_SERVICES_WAIT_LIST += php-fpm:9000
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/php-fpm/docker-compose.yml
DOCKER_COMPOSE_ENV += PROJECT_FPM_PHP_VERSION=$(PROJECT_FPM_PHP_VERSION)

docker-prompt-php-fpm: ## Open a bash shell in PHP-FPM's container
	$(DOCKER_COMPOSE_CMD) exec php-fpm /bin/bash

logs-php-fpm-all: ## Shows PHP-FPM's log
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q php-fpm) 2>&1 >/dev/null | sed -ur 's/\\n/\n/g'
