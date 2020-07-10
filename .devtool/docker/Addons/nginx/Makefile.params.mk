PROJECT_NGINX_VERSION = 1.17

DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/nginx/docker-compose.yml

docker-prompt-web: ## Opens a bash shell in Nginx's container
	$(DOCKER_COMPOSE_CMD) exec web /bin/bash

logs-web-error: ## Shows Nginx's error log from Nginx's container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q web) 2>&1 >/dev/null

logs-web-access: ## Shows Nginx's access log from Nginx's container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q web) 2>/dev/null

logs-web-all: ## Shows all Nginx's log from Nginx's container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q web)
