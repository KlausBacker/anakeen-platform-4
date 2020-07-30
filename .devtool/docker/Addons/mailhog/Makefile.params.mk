PROJECT_MAIL_PORT = 8081

SMTP_HOST = mail
SMTP_PORT = 1025
SMTP_FROM = noreply@example.net

DOCKER_COMPOSE_SERVICES += mail
DOCKER_COMPOSE_SERVICES_WAIT_LIST += $(SMTP_HOST):$(SMTP_PORT)
DOCKER_COMPOSE_OVERRIDES += -f $(DOCKER_DIR)/Addons/mailhog/docker-compose.yml
DOCKER_COMPOSE_ENV += PROJECT_MAIL_PORT=$(PROJECT_MAIL_PORT)
DOCKER_COMPOSE_ENV += SMTP_HOST=$(SMTP_HOST)
DOCKER_COMPOSE_ENV += SMTP_PORT=$(SMTP_PORT)

docker-prompt-mail: ## Opens a bash shell in mail container
	$(DOCKER_COMPOSE_CMD) exec mail /bin/sh

logs-mail-all: ## Shows all mailhog's log from mail container
	docker logs -f $$($(DOCKER_COMPOSE_CMD) ps -q mail)
