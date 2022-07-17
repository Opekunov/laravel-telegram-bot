SHELL = /bin/bash
DC_RUN_ARGS = --rm --user "$(shell id -u):$(shell id -g)"

shell: ## Start shell into app container
	docker-compose run $(DC_RUN_ARGS) php sh
