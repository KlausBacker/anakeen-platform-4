DOCKER_COMPOSE_SERVICES += keycloak
DOCKER_COMPOSE_SERVICES_WAIT_LIST += keycloak:8080
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/keycloak/docker-compose.yml

VOLUMES_KEYCLOAK_EXPORT_IMPORT = $(VOLUMES_PRIVATE_DIR)/keycloak/data/export-import
VOLUMES_PRIVATE += $(VOLUMES_KEYCLOAK_EXPORT_IMPORT)
KEEP_VOLUMES += $(VOLUMES_KEYCLOAK_EXPORT_IMPORT)

docker-prompt-keycloak: ## Open a bash shell in Keycloak's container
	$(DOCKER_COMPOSE_CMD) exec keycloak /bin/bash

logs-keycloak-all: ## Shows Keycloak's log
	$(DOCKER_COMPOSE_CMD) logs -f keycloak

keycloak-export-config: ## Export Keycloak's realms and users
	@echo ""
	@echo "Exporting configuration to: $(VOLUMES_KEYCLOAK_EXPORT_IMPORT)"
	@echo ""
	$(DOCKER_COMPOSE_CMD) stop keycloak
	$(DOCKER_COMPOSE_CMD) run --rm \
		--entrypoint /data/utils/keycloak-export-import-config.sh \
		-e DEBUG=no \
		keycloak \
		export /data/export-import
	$(DOCKER_COMPOSE_CMD) start keycloak

keycloak-import-config: ## Import Keycloak's realms and users
	@echo ""
	@echo "Importing configuration from: $(VOLUMES_KEYCLOAK_EXPORT_IMPORT)"
	@echo ""
	$(DOCKER_COMPOSE_CMD) stop keycloak
	$(DOCKER_COMPOSE_CMD) run --rm \
		--entrypoint /data/utils/keycloak-export-import-config.sh \
		-e DEBUG=no \
		keycloak \
		import /data/export-import
	$(DOCKER_COMPOSE_CMD) start keycloak

keycloak-stop:
	$(DOCKER_COMPOSE_CMD) stop keycloak

keycloak-start:
	$(DOCKER_COMPOSE_CMD) start keycloak