DOCKER_COMPOSE_SERVICES += transformation-server
DOCKER_COMPOSE_SERVICES_WAIT_LIST += transformation-server:51968
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/transformation-server/docker-compose.yml
