CONTAINER_PHP = php-fpm

DOCKER_COMPOSE_SERVICES += php-fpm
DOCKER_COMPOSE_SERVICES_WAIT_LIST += php-fpm:9000
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/php-fpm/docker-compose.yml

docker-prompt-php-fpm: ## Open a bash shell in PHP-FPM's container
	$(DOCKER_COMPOSE_CMD) exec php-fpm /bin/bash

logs-php-fpm-all: ## Shows PHP-FPM's log
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q php-fpm) 2>&1 >/dev/null | sed -ur 's/\\n/\n/g'
